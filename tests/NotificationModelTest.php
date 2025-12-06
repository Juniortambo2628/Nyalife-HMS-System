<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/models/NotificationModel.php';

final class NotificationModelTest extends TestCase {
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

        $this->model = new NotificationModel();
    }

    protected function tearDown(): void {
        if ($this->conn instanceof mysqli) $this->conn->close();
    }

    public function testCreateAndGetByUserId(): void {
        // Create a notification for user_id 12 (assumes exists)
        $data = [
            'user_id' => 12,
            'type' => 'message_received',
            'title' => 'Unit Test Notification',
            'message' => 'This is a test notification',
            'is_read' => 0
        ];

        $id = $this->model->create($data);
        $this->assertIsInt($id);

        $rows = $this->model->getByUserId(12, 10, false);
        $this->assertIsArray($rows);
        $found = false;
        foreach ($rows as $r) { if ($r['notification_id'] == $id) { $found = true; break; } }
        $this->assertTrue($found, 'Created notification should be returned in getByUserId');

        // Cleanup
        $this->conn->query("DELETE FROM notifications WHERE notification_id = {$id}");
    }

    public function testMarkAsReadAndCount(): void {
        $data = [
            'user_id' => 12,
            'type' => 'message_received',
            'title' => 'Read Test',
            'message' => 'Read status test',
            'is_read' => 0
        ];
        $id = $this->model->create($data);
        $this->assertIsInt($id);

        $countBefore = $this->model->getUnreadCount(12);
        $this->assertIsInt($countBefore);

        $this->assertTrue($this->model->markAsRead($id, 12));

        $countAfter = $this->model->getUnreadCount(12);
        $this->assertLessThanOrEqual($countBefore, $countAfter);

        // Cleanup
        $this->conn->query("DELETE FROM notifications WHERE notification_id = {$id}");
    }

    public function testCreateAppointmentNotification(): void {
        $appointmentId = 999999; // dummy ref
        $appointmentData = [
            'patient_user_id' => 12,
            'doctor_user_id' => 12,
            'patient_email' => null,
            'patient_phone' => null
        ];

        $ids = $this->model->createAppointmentNotification($appointmentId, 'appointment_created', 'Test appt', 'Test message', $appointmentData);
        $this->assertIsArray($ids);

        // Cleanup any created notifications referencing our dummy appointment
        if (!empty($ids)) {
            foreach ($ids as $nid) {
                $this->conn->query("DELETE FROM notifications WHERE notification_id = {$nid}");
            }
        }
    }
}


