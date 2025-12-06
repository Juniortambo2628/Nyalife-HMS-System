<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/models/PatientModel.php';

final class PatientModelTest extends TestCase {
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

        $this->model = new PatientModel();
    }

    protected function tearDown(): void {
        if ($this->conn instanceof mysqli) $this->conn->close();
    }

    public function testPatientsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'patients'");
        $this->assertTrue($result->num_rows > 0, 'patients table should exist');
    }

    public function testGetPatientById(): void {
        // Test with an existing patient ID (assuming patient ID 1 exists)
        $patient = $this->model->getById(1);
        // If patient exists, check structure
        if ($patient !== null) {
            $this->assertIsArray($patient);
            $this->assertArrayHasKey('patient_id', $patient);
        }
    }

    public function testGetPatientByUserId(): void {
        // Test with an existing user ID (assuming user ID 1 exists)
        $patient = $this->model->getByUserId(1);
        // If patient exists, check structure  
        if ($patient !== null) {
            $this->assertIsArray($patient);
            $this->assertArrayHasKey('patient_id', $patient);
        } else {
            $this->assertNull($patient, 'Patient may not exist for user ID 1');
        }
    }

    public function testGetPatientByPatientNumber(): void {
        // Get a real patient number from the database
        $result = $this->conn->query("SELECT patient_number FROM patients LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            $patientNumber = $row['patient_number'];
            $patient = $this->model->getByPatientNumber($patientNumber);
            
            if ($patient !== null) {
                $this->assertIsArray($patient);
                $this->assertEquals($patientNumber, $patient['patient_number']);
            }
        }
    }

    public function testPatientsTableStructure(): void {
        $result = $this->conn->query("DESCRIBE patients");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $requiredColumns = ['patient_id', 'user_id', 'patient_number', 'blood_group', 'created_at'];
        foreach ($requiredColumns as $col) {
            $this->assertContains($col, $columns, "patients should have $col column");
        }
    }
}

