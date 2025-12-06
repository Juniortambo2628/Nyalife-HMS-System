<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';

/**
 * Comprehensive System Diagnostics Test
 * Tests all major components and identifies issues
 */
final class SystemDiagnosticsTest extends TestCase {
    protected $conn;
    protected $issues = [];

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

    public function testLabRequestModuleExists(): void {
        // Check for LabRequestModel
        $modelFile = __DIR__ . '/../includes/models/LabRequestModel.php';
        $this->assertFileExists($modelFile, 'LabRequestModel.php should exist');
        
        // Check for LabTestModel
        $testModelFile = __DIR__ . '/../includes/models/LabTestModel.php';
        $this->assertFileExists($testModelFile, 'LabTestModel.php should exist');
        
        // Check for LabRequestController
        $controllerFile = __DIR__ . '/../includes/controllers/web/LabRequestController.php';
        $this->assertFileExists($controllerFile, 'LabRequestController.php should exist');
        
        // Check for lab views
        $viewDir = __DIR__ . '/../includes/views/lab';
        $this->assertDirectoryExists($viewDir, 'Lab views directory should exist');
    }

    public function testAppointmentModuleExists(): void {
        // Check for AppointmentModel
        $modelFile = __DIR__ . '/../includes/models/AppointmentModel.php';
        $this->assertFileExists($modelFile, 'AppointmentModel.php should exist');
        
        // Check for AppointmentController
        $controllerFile = __DIR__ . '/../includes/controllers/web/AppointmentController.php';
        $this->assertFileExists($controllerFile, 'AppointmentController.php should exist');
        
        // Check for appointment views
        $this->assertFileExists(__DIR__ . '/../includes/views/appointments/index.php', 'Appointments index view should exist');
        $this->assertFileExists(__DIR__ . '/../includes/views/appointments/calendar.php', 'Appointments calendar view should exist');
        $this->assertFileExists(__DIR__ . '/../includes/views/appointments/create.php', 'Appointments create view should exist');
    }

    public function testLabTablesHaveData(): void {
        // Check if lab_test_types has data
        $result = $this->conn->query("SELECT COUNT(*) as count FROM lab_test_types");
        if ($result && $row = $result->fetch_assoc()) {
            $this->assertGreaterThan(0, $row['count'], 'lab_test_types should have data');
        }
    }

    public function testLabRequestsCanBeCreated(): void {
        // Verify lab_requests table structure
        $result = $this->conn->query("DESCRIBE lab_requests");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $requiredColumns = ['request_id', 'patient_id', 'requested_by', 'status', 'priority'];
        foreach ($requiredColumns as $col) {
            $this->assertContains($col, $columns, "lab_requests should have $col column");
        }
    }

    public function testConsultationTableStructure(): void {
        $result = $this->conn->query("DESCRIBE consultations");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $requiredColumns = ['consultation_id', 'patient_id', 'doctor_id', 'consultation_date', 'consultation_status'];
        foreach ($requiredColumns as $col) {
            $this->assertContains($col, $columns, "consultations should have $col column");
        }
    }

    public function testAppointmentTableStructure(): void {
        $result = $this->conn->query("DESCRIBE appointments");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $requiredColumns = ['appointment_id', 'patient_id', 'doctor_id', 'appointment_date', 'appointment_time', 'status'];
        foreach ($requiredColumns as $col) {
            $this->assertContains($col, $columns, "appointments should have $col column");
        }
    }

    public function testPatientsTableStructure(): void {
        $result = $this->conn->query("DESCRIBE patients");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $this->assertContains('patient_id', $columns, 'patients should have patient_id');
        $this->assertContains('user_id', $columns, 'patients should have user_id');
    }

    public function testDoctorsTableStructure(): void {
        $result = $this->conn->query("DESCRIBE doctors");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $this->assertContains('doctor_id', $columns, 'doctors should have doctor_id');
        $this->assertContains('staff_id', $columns, 'doctors should have staff_id');
    }

    public function testStaffTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'staff'");
        $this->assertTrue($result->num_rows > 0, 'staff table should exist');
    }

    public function testUsersTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'users'");
        $this->assertTrue($result->num_rows > 0, 'users table should exist');
    }

    public function testDepartmentsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'departments'");
        $this->assertTrue($result->num_rows > 0, 'departments table should exist');
    }

    public function testMessagesTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'messages'");
        $this->assertTrue($result->num_rows > 0, 'messages table should exist');
    }

    public function testNotificationsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'notifications'");
        $this->assertTrue($result->num_rows > 0, 'notifications table should exist');
    }

    public function testVitalSignsCanBeSaved(): void {
        // Check if consultations table has vital_signs column
        $result = $this->conn->query("DESCRIBE consultations");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $this->assertContains('vital_signs', $columns, 'consultations should have vital_signs column for storing vitals');
    }

    public function testLabTestItemsCanBeSaved(): void {
        // Check if lab_test_items has proper structure
        $result = $this->conn->query("DESCRIBE lab_test_items");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $requiredColumns = ['test_item_id', 'request_id', 'test_type_id', 'status'];
        foreach ($requiredColumns as $col) {
            $this->assertContains($col, $columns, "lab_test_items should have $col column");
        }
    }

    public function testCalendarViewFileIsReadable(): void {
        $calendarFile = __DIR__ . '/../includes/views/appointments/calendar.php';
        $this->assertFileExists($calendarFile, 'Calendar view file should exist');
        $this->assertTrue(is_readable($calendarFile), 'Calendar view file should be readable');
        
        // Check file is not corrupted (contains php opening tag)
        $content = file_get_contents($calendarFile);
        
        // The file might have UTF-8 BOM or other encoding issues
        // Remove BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        // Also try removing any other BOM-like characters
        $content = ltrim($content, "\xEF\xBB\xBF\x00");
        
        // Check for PHP opening tag - handle various encodings
        $hasPhpTag = false;
        
        // Method 1: Direct string search
        if (strpos($content, '<?php') !== false) {
            $hasPhpTag = true;
        }
        
        // Method 2: Regex search
        if (!$hasPhpTag && preg_match('/<\\?php/i', $content)) {
            $hasPhpTag = true;
        }
        
        // Method 3: Check if file contains any PHP code patterns
        if (!$hasPhpTag && (strpos($content, '<?=') !== false || preg_match('/<\\?/', $content))) {
            $hasPhpTag = true;
        }
        
        $this->assertTrue($hasPhpTag, 'Calendar view should contain PHP code. File size: ' . filesize($calendarFile));
        
        // Check for FullCalendar
        $hasFullCalendar = strpos($content, 'FullCalendar') !== false || 
                          preg_match('/fullcalendar/i', $content);
        
        $this->assertTrue($hasFullCalendar, 'Calendar view should use FullCalendar');
    }
}


