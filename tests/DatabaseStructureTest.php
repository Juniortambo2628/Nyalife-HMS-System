<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';

final class DatabaseStructureTest extends TestCase {
    protected $conn;

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
    }

    protected function tearDown(): void {
        if ($this->conn instanceof mysqli) $this->conn->close();
    }

    public function testPatientsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'patients'");
        $this->assertTrue($result->num_rows > 0, 'Patients table should exist');
    }

    public function testDoctorsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'doctors'");
        $this->assertTrue($result->num_rows > 0, 'Doctors table should exist');
    }

    public function testAppointmentsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'appointments'");
        $this->assertTrue($result->num_rows > 0, 'Appointments table should exist');
    }

    public function testConsultationsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'consultations'");
        $this->assertTrue($result->num_rows > 0, 'Consultations table should exist');
    }

    public function testLabTestTypesTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'lab_test_types'");
        $this->assertTrue($result->num_rows > 0, 'lab_test_types table should exist');
    }

    public function testLabTestRequestsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'lab_test_requests'");
        $this->assertTrue($result->num_rows > 0, 'lab_test_requests table should exist');
    }

    public function testLabTestItemsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'lab_test_items'");
        $this->assertTrue($result->num_rows > 0, 'lab_test_items table should exist');
    }

    public function testLabRequestsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'lab_requests'");
        $this->assertTrue($result->num_rows > 0, 'lab_requests table should exist');
    }

    public function testConsultationsHasRequiredColumns(): void {
        $result = $this->conn->query("DESCRIBE consultations");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $this->assertContains('consultation_id', $columns, 'Consultations should have consultation_id');
        $this->assertContains('patient_id', $columns, 'Consultations should have patient_id');
        $this->assertContains('doctor_id', $columns, 'Consultations should have doctor_id');
        $this->assertContains('consultation_date', $columns, 'Consultations should have consultation_date');
        $this->assertContains('consultation_status', $columns, 'Consultations should have consultation_status');
    }

    public function testAppointmentsHasRequiredColumns(): void {
        $result = $this->conn->query("DESCRIBE appointments");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $this->assertContains('appointment_id', $columns, 'Appointments should have appointment_id');
        $this->assertContains('patient_id', $columns, 'Appointments should have patient_id');
        $this->assertContains('doctor_id', $columns, 'Appointments should have doctor_id');
        $this->assertContains('appointment_date', $columns, 'Appointments should have appointment_date');
        $this->assertContains('appointment_time', $columns, 'Appointments should have appointment_time');
        $this->assertContains('status', $columns, 'Appointments should have status');
    }

    public function testLabTestRequestsHasRequiredColumns(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'lab_test_requests'");
        if ($result->num_rows > 0) {
            $result = $this->conn->query("DESCRIBE lab_test_requests");
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            
            $this->assertContains('request_id', $columns, 'lab_test_requests should have request_id');
            $this->assertContains('patient_id', $columns, 'lab_test_requests should have patient_id');
            $this->assertContains('status', $columns, 'lab_test_requests should have status');
        } else {
            $this->markTestSkipped('lab_test_requests table does not exist');
        }
    }

    public function testLabRequestsHasRequiredColumns(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'lab_requests'");
        if ($result->num_rows > 0) {
            $result = $this->conn->query("DESCRIBE lab_requests");
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            
            $this->assertContains('request_id', $columns, 'lab_requests should have request_id');
            $this->assertContains('patient_id', $columns, 'lab_requests should have patient_id');
            $this->assertContains('status', $columns, 'lab_requests should have status');
            $this->assertContains('priority', $columns, 'lab_requests should have priority');
        } else {
            $this->markTestSkipped('lab_requests table does not exist');
        }
    }
}

