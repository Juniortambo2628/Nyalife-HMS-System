<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/models/AppointmentModel.php';

final class AppointmentModelTest extends TestCase {
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

        $this->model = new AppointmentModel();
    }

    protected function tearDown(): void {
        if ($this->conn instanceof mysqli) $this->conn->close();
    }

    public function testCreateAppointmentAndCounts(): void {
        $data = [
            'patient_id' => 3,
            'doctor_id' => 12,
            'appointment_date' => date('Y-m-d', strtotime('+1 day')),
            'appointment_time' => '09:00:00',
            'status' => 'scheduled',
            'created_by' => 12
        ];

        $id = $this->model->createAppointment($data);
        $this->assertIsInt($id);

        $count = $this->model->getDoctorAppointmentCount(12);
        $this->assertIsInt($count);

        $appointments = $this->model->getDoctorAppointmentsByStatus(12, 'scheduled');
        $this->assertIsArray($appointments);

        // Cleanup
        $this->conn->query("DELETE FROM appointments WHERE appointment_id = {$id}");
    }

    public function testIsTimeSlotAvailable(): void {
        $date = date('Y-m-d', strtotime('+2 days'));
        $start = '10:00:00';
        $end = '10:30:00';

        // Insert an appointment that occupies the slot
        $stmt = $this->conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, end_time, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, 'scheduled', ?, NOW())");
        $patientId = 3; $doctorId = 12; $stmt->bind_param('iisssi', $patientId, $doctorId, $date, $start, $end, $doctorId);
        $this->assertTrue($stmt->execute(), 'Insert appointment failed: ' . $stmt->error);
        $apptId = $this->conn->insert_id;
        $stmt->close();

        $available = $this->model->isTimeSlotAvailable(12, $date, $start, $end);
        $this->assertFalse($available, 'Slot should be unavailable when appointment exists');

        // Check an adjacent free slot
        $free = $this->model->isTimeSlotAvailable(12, $date, '11:00:00', '11:30:00');
        $this->assertTrue($free);

        // Cleanup
        $this->conn->query("DELETE FROM appointments WHERE appointment_id = {$apptId}");
    }

    public function testUpdateStatus(): void {
        $data = [
            'patient_id' => 3,
            'doctor_id' => 12,
            'appointment_date' => date('Y-m-d', strtotime('+3 days')),
            'appointment_time' => '14:00:00',
            'status' => 'scheduled',
            'created_by' => 12
        ];
        $id = $this->model->createAppointment($data);
        $this->assertIsInt($id);

        $this->assertTrue($this->model->updateStatus($id, 'completed', 12));

        // verify
        $row = $this->conn->query("SELECT status FROM appointments WHERE appointment_id = {$id}")->fetch_assoc();
        $this->assertEquals('completed', $row['status']);

        // Cleanup
        $this->conn->query("DELETE FROM appointments WHERE appointment_id = {$id}");
    }
}


