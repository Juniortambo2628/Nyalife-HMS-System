<?php

namespace Tests\Unit\Models;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_has_fillable_attributes(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $message = Message::create([
            'sender_id' => $sender->user_id,
            'receiver_id' => $receiver->user_id,
            'content' => 'Hello, this is a test message.',
        ]);

        $this->assertSame('Hello, this is a test message.', $message->content);
        $this->assertSame($sender->user_id, $message->sender_id);
        $this->assertSame($receiver->user_id, $message->receiver_id);
    }

    public function test_message_belongs_to_sender(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $message = Message::create([
            'sender_id' => $sender->user_id,
            'receiver_id' => $receiver->user_id,
            'content' => 'Test',
        ]);

        $this->assertNotNull($message->sender);
        $this->assertSame($sender->user_id, $message->sender->user_id);
    }

    public function test_message_belongs_to_receiver(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $message = Message::create([
            'sender_id' => $sender->user_id,
            'receiver_id' => $receiver->user_id,
            'content' => 'Test',
        ]);

        $this->assertNotNull($message->receiver);
        $this->assertSame($receiver->user_id, $message->receiver->user_id);
    }

    public function test_message_read_at_tracking(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $message = Message::create([
            'sender_id' => $sender->user_id,
            'receiver_id' => $receiver->user_id,
            'content' => 'Unread message',
        ]);

        $this->assertNull($message->read_at);

        $message->update(['read_at' => now()]);
        $message->refresh();
        $this->assertNotNull($message->read_at);
    }

    public function test_message_metadata_json_cast(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $message = Message::create([
            'sender_id' => $sender->user_id,
            'receiver_id' => $receiver->user_id,
            'content' => 'Message with reference',
            'metadata' => [
                'references' => [
                    ['id' => 1, 'type' => 'consultation', 'label' => 'CON-1'],
                ],
            ],
        ]);

        $message->refresh();
        $this->assertIsArray($message->metadata);
        $this->assertCount(1, $message->metadata['references']);
        $this->assertSame('consultation', $message->metadata['references'][0]['type']);
    }

    public function test_message_is_read_scope(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $readMsg = Message::create([
            'sender_id' => $sender->user_id,
            'receiver_id' => $receiver->user_id,
            'content' => 'Read message '.uniqid(),
            'read_at' => now(),
        ]);
        $unreadMsg = Message::create([
            'sender_id' => $sender->user_id,
            'receiver_id' => $receiver->user_id,
            'content' => 'Unread message '.uniqid(),
        ]);

        $this->assertNotNull($readMsg->read_at);
        $this->assertNull($unreadMsg->read_at);
    }
}
