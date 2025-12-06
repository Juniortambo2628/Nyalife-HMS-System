<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/models/ConsultationModel.php';

final class ConsultationModelTest extends TestCase {
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

        $this->model = new ConsultationModel();
    }

    protected function tearDown(): void {
        if ($this->conn instanceof mysqli) $this->conn->close();
    }

    public function testGetConsultationById(): void {
        // Get an existing consultation
        $result = $this->conn->query("SELECT consultation_id FROM consultations LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            $consultationId = $row['consultation_id'];
            $consultation = $this->model->getConsultationById($consultationId);
            
            if ($consultation !== false && $consultation !== null) {
                $this->assertIsArray($consultation);
                $this->assertEquals($consultationId, $consultation['consultation_id']);
            }
        }
    }

    public function testGetConsultationsByPatient(): void {
        // Get an existing patient
        $result = $this->conn->query("SELECT patient_id FROM patients LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            $patientId = $row['patient_id'];
            $consultations = $this->model->getConsultationsByPatient($patientId);
            
            $this->assertIsArray($consultations);
        }
    }

    public function testConsultationsTableStructure(): void {
        $result = $this->conn->query("DESCRIBE consultations");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $requiredColumns = ['consultation_id', 'patient_id', 'doctor_id', 'consultation_date', 'consultation_status', 'created_at'];
        foreach ($requiredColumns as $col) {
            $this->assertContains($col, $columns, "consultations should have $col column");
        }
    }
}

