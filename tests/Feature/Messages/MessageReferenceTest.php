<?php

namespace Tests\Feature\Messages;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\LabTestRequest;
use App\Models\LabTestType;
use App\Models\Medication;
use App\Models\Message;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageReferenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $sender;

    protected User $recipient;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed both the legacy `roles` table (used by $user->role attribute)
        // and the Spatie roles (used for permission gating).
        foreach (['admin', 'doctor', 'nurse', 'receptionist', 'lab_technician', 'pharmacist', 'patient'] as $roleName) {
            Role::firstOrCreate(['role_name' => $roleName]);
        }
        $this->seed(SyncSpatieRolesSeeder::class);

        $this->sender = User::factory()->create([
            'role_id' => Role::where('role_name', 'doctor')->first()->role_id,
            'is_active' => true,
        ]);
        $this->sender->assignRole('doctor');
        $this->sender->givePermissionTo(Permissions::SEND_MESSAGES);

        $this->recipient = User::factory()->create([
            'role_id' => Role::where('role_name', 'nurse')->first()->role_id,
            'is_active' => true,
        ]);
        $this->recipient->assignRole('nurse');
        $this->recipient->givePermissionTo(Permissions::SEND_MESSAGES);
    }

    public function test_message_persists_references_in_metadata(): void
    {
        $patient = Patient::factory()->create();
        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => null,
        ]);

        $payload = [
            'receiver_id' => $this->recipient->user_id,
            'content' => 'Please review the attached consultation.',
            'metadata' => [
                'references' => [
                    [
                        'id' => $consultation->consultation_id,
                        'label' => 'Consultation #'.$consultation->consultation_id,
                        'type' => 'consultation',
                    ],
                ],
            ],
        ];

        $this->actingAs($this->sender)
            ->post(route('messages.store'), $payload)
            ->assertRedirect();

        $message = Message::where('sender_id', $this->sender->user_id)->first();

        $this->assertNotNull($message);
        $this->assertEquals($payload['content'], $message->content);
        $this->assertIsArray($message->metadata);
        $this->assertCount(1, $message->metadata['references']);
        $this->assertEquals('consultation', $message->metadata['references'][0]['type']);
    }

    public function test_message_can_be_sent_without_metadata(): void
    {
        $payload = [
            'receiver_id' => $this->recipient->user_id,
            'content' => 'Plain message, no references.',
        ];

        $this->actingAs($this->sender)
            ->post(route('messages.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'sender_id' => $this->sender->user_id,
            'receiver_id' => $this->recipient->user_id,
            'content' => 'Plain message, no references.',
        ]);
    }

    public function test_validation_requires_a_real_recipient(): void
    {
        $this->actingAs($this->sender)
            ->post(route('messages.store'), [
                'receiver_id' => 999999,
                'content' => 'Hello',
            ])
            ->assertSessionHasErrors('receiver_id');
    }

    public function test_validation_requires_message_content(): void
    {
        $this->actingAs($this->sender)
            ->post(route('messages.store'), [
                'receiver_id' => $this->recipient->user_id,
                'content' => '',
            ])
            ->assertSessionHasErrors('content');
    }

    public function test_get_entities_returns_only_entities_visible_to_role(): void
    {
        // A doctor should see patients, appointments, consultations, lab_requests, medications.
        $patient = Patient::factory()->create();
        $consultation = Consultation::factory()->create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => null,
        ]);
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->patient_id,
            'doctor_id' => null,
        ]);
        $labType = LabTestType::factory()->create(['is_active' => true]);
        $labRequest = LabTestRequest::factory()->create([
            'patient_id' => $patient->patient_id,
            'test_type_id' => $labType->test_type_id,
        ]);
        $medication = Medication::factory()->create();

        $response = $this->actingAs($this->sender)
            ->get(route('messages.entities'))
            ->assertOk()
            ->json();

        $this->assertArrayHasKey('patients', $response);
        $this->assertArrayHasKey('appointments', $response);
        $this->assertArrayHasKey('consultations', $response);
        $this->assertArrayHasKey('lab_requests', $response);
        $this->assertArrayHasKey('medications', $response);

        // Structure of each entry: { id, label, type }.
        $firstPatient = $response['patients'][0];
        $this->assertArrayHasKey('id', $firstPatient);
        $this->assertArrayHasKey('label', $firstPatient);
        $this->assertArrayHasKey('type', $firstPatient);
        $this->assertEquals('patient', $firstPatient['type']);

        $firstConsult = $response['consultations'][0];
        $this->assertEquals('consultation', $firstConsult['type']);
    }

    public function test_get_entities_excludes_records_for_unauthorised_role(): void
    {
        // A pharmacist should NOT see consultations or lab_requests.
        $pharmacist = User::factory()->create([
            'role_id' => Role::where('role_name', 'pharmacist')->first()->role_id,
            'is_active' => true,
        ]);
        $pharmacist->assignRole('pharmacist');
        $pharmacist->givePermissionTo(Permissions::SEND_MESSAGES);

        // Seed at least one of each kind so empty-result sets are meaningful.
        Patient::factory()->create();
        LabTestRequest::factory()->create();
        Consultation::factory()->create(['patient_id' => Patient::first()->patient_id, 'doctor_id' => null]);
        Appointment::factory()->create(['patient_id' => Patient::first()->patient_id, 'doctor_id' => null]);
        Medication::factory()->create();

        $response = $this->actingAs($pharmacist)
            ->get(route('messages.entities'))
            ->assertOk()
            ->json();

        $this->assertArrayHasKey('medications', $response);
        $this->assertArrayHasKey('patients', $response);
        $this->assertArrayNotHasKey('consultations', $response);
        $this->assertArrayNotHasKey('lab_requests', $response);
        $this->assertArrayNotHasKey('appointments', $response);
    }

    public function test_mark_all_read_only_targets_messages_addressed_to_me(): void
    {
        $otherRecipient = User::factory()->create(['is_active' => true]);

        // Unread message from recipient → me (should be marked).
        $unread = Message::create([
            'sender_id' => $this->recipient->user_id,
            'receiver_id' => $this->sender->user_id,
            'content' => 'unread for me',
        ]);
        $unread->refresh();

        // Unread message from some other user → me (should NOT be marked).
        $stranger = Message::create([
            'sender_id' => $otherRecipient->user_id,
            'receiver_id' => $this->sender->user_id,
            'content' => 'also unread but from someone else',
        ]);
        $stranger->refresh();

        $this->actingAs($this->sender)
            ->post(route('messages.mark-all-read', $this->recipient->user_id))
            ->assertRedirect();

        $this->assertNotNull(Message::find($unread->id)->read_at);
        $this->assertNull(Message::find($stranger->id)->read_at);
    }

    public function test_destroy_removes_only_my_message(): void
    {
        $mine = Message::create([
            'sender_id' => $this->sender->user_id,
            'receiver_id' => $this->recipient->user_id,
            'content' => 'mine',
        ]);
        $theirs = Message::create([
            'sender_id' => $this->recipient->user_id,
            'receiver_id' => $this->sender->user_id,
            'content' => 'theirs',
        ]);

        $this->actingAs($this->sender)
            ->delete(route('messages.destroy', $mine->id))
            ->assertRedirect();

        $this->assertNull(Message::find($mine->id));
        $this->assertNotNull(Message::find($theirs->id));
    }

    public function test_destroy_returns_403_for_message_not_in_my_conversation(): void
    {
        $otherRecipient = User::factory()->create(['is_active' => true]);
        $otherSender = User::factory()->create(['is_active' => true]);

        $strangers = Message::create([
            'sender_id' => $otherSender->user_id,
            'receiver_id' => $otherRecipient->user_id,
            'content' => 'not mine',
        ]);

        $this->actingAs($this->sender)
            ->delete(route('messages.destroy', $strangers->id))
            ->assertForbidden();
    }

    public function test_destroy_conversation_only_removes_messages_between_me_and_target(): void
    {
        $other = User::factory()->create(['is_active' => true]);

        // Conversation to delete.
        Message::create([
            'sender_id' => $this->sender->user_id,
            'receiver_id' => $this->recipient->user_id,
            'content' => 'between me and recipient',
        ]);
        Message::create([
            'sender_id' => $this->recipient->user_id,
            'receiver_id' => $this->sender->user_id,
            'content' => 'reply',
        ]);

        // Conversation to keep.
        Message::create([
            'sender_id' => $this->sender->user_id,
            'receiver_id' => $other->user_id,
            'content' => 'between me and other',
        ]);

        $this->actingAs($this->sender)
            ->delete(route('messages.destroy-conversation', $this->recipient->user_id))
            ->assertRedirect();

        $this->assertEquals(0, Message::where('receiver_id', $this->recipient->user_id)
            ->where('sender_id', $this->sender->user_id)
            ->count());
        $this->assertEquals(0, Message::where('sender_id', $this->recipient->user_id)
            ->where('receiver_id', $this->sender->user_id)
            ->count());
        $this->assertEquals(1, Message::where('receiver_id', $other->user_id)
            ->where('sender_id', $this->sender->user_id)
            ->count());
    }
}
