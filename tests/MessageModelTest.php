<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/models/MessageModel.php';

final class MessageModelTest extends TestCase {
    protected $conn;
    protected $model;

    protected function setUp(): void {
        try {
            $this->conn = connectDB();
        } catch (Throwable $e) {
            $this->conn = null;
        }

        if (!($this->conn instanceof mysqli)) {
            $this->conn = new mysqli('localhost', 'root', '', 'nyalifew_hms_prod');
            if ($this->conn instanceof mysqli && $this->conn->connect_error) {
                $this->markTestSkipped('Cannot connect to DB: ' . $this->conn->connect_error);
            }
        }

        $this->model = new MessageModel();
    }

    protected function tearDown(): void {
        if ($this->conn instanceof mysqli) $this->conn->close();
    }

    public function testSendAndRetrieveMessage(): void {
        // Ensure sender and recipient exist (using user_id 12)
        $data = [
            'sender_id' => 12,
            'recipient_id' => 12,
            'subject' => 'Unit Test Message',
            'message' => 'This is a test',
            'priority' => 'normal'
        ];

        $msgId = $this->model->sendMessage($data);
        $this->assertIsInt($msgId);

        $msg = $this->model->getMessageWithDetails($msgId, 12);
        $this->assertIsArray($msg);
        $this->assertEquals('Unit Test Message', $msg['subject']);

        // Cleanup
        $this->conn->query("UPDATE messages SET is_deleted = 1 WHERE message_id = {$msgId}");
    }

    public function testMessageStatsAndUnread(): void {
        $userId = 12;
        $stats = $this->model->getMessageStats($userId);
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_inbox', $stats);
        $this->assertArrayHasKey('unread', $stats);
    }
}


