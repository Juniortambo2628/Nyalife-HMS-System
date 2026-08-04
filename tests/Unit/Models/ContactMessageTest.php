<?php

namespace Tests\Unit\Models;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_message_has_fillable_attributes(): void
    {
        $msg = ContactMessage::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'I would like to book an appointment.',
            'status' => 'pending',
        ]);

        $this->assertSame('Test User', $msg->name);
        $this->assertSame('test@example.com', $msg->email);
        $this->assertSame('pending', $msg->status);
    }

    public function test_contact_message_status_transitions(): void
    {
        $msg = ContactMessage::factory()->create(['status' => 'pending']);
        $this->assertSame('pending', $msg->status);

        $msg->update(['status' => 'read']);
        $msg->refresh();
        $this->assertSame('read', $msg->status);

        $msg->update(['status' => 'replied']);
        $msg->refresh();
        $this->assertSame('replied', $msg->status);
    }

    public function test_contact_message_reply_fields(): void
    {
        $msg = ContactMessage::factory()->create([
            'reply' => null,
            'replied_at' => null,
        ]);

        $msg->update([
            'reply' => 'Thank you for your inquiry.',
            'replied_at' => now(),
        ]);
        $msg->refresh();

        $this->assertSame('Thank you for your inquiry.', $msg->reply);
        $this->assertNotNull($msg->replied_at);
    }

    public function test_contact_message_status_scope(): void
    {
        ContactMessage::factory()->create(['status' => 'read']);
        ContactMessage::factory()->create(['status' => 'pending']);

        $readCount = ContactMessage::where('status', 'read')->count();
        $pendingCount = ContactMessage::where('status', 'pending')->count();

        $this->assertGreaterThanOrEqual(1, $readCount);
        $this->assertGreaterThanOrEqual(1, $pendingCount);
    }
}
