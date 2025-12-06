<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/models/PrescriptionModel.php';

final class PrescriptionModelTest extends TestCase {
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

        $this->model = new PrescriptionModel();
    }

    protected function tearDown(): void {
        if ($this->conn instanceof mysqli) $this->conn->close();
    }

    public function testPrescriptionsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'prescriptions'");
        $this->assertTrue($result->num_rows > 0, 'prescriptions table should exist');
    }

    public function testPrescriptionsTableStructure(): void {
        $result = $this->conn->query("DESCRIBE prescriptions");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $requiredColumns = ['prescription_id', 'patient_id', 'consultation_id', 'prescribed_by', 'prescription_date', 'status', 'created_at'];
        foreach ($requiredColumns as $col) {
            $this->assertContains($col, $columns, "prescriptions should have $col column");
        }
    }
}

