<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/models/FollowUpModel.php';

final class FollowUpModelTest extends TestCase {
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

        $this->model = new FollowUpModel();
    }

    protected function tearDown(): void {
        if ($this->conn instanceof mysqli) $this->conn->close();
    }

    public function testFollowUpsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'follow_ups'");
        $this->assertTrue($result->num_rows > 0, 'follow_ups table should exist');
    }

    public function testFollowUpsTableStructure(): void {
        $result = $this->conn->query("DESCRIBE follow_ups");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $requiredColumns = ['follow_up_id', 'patient_id', 'consultation_id', 'follow_up_date', 'status', 'created_at'];
        foreach ($requiredColumns as $col) {
            $this->assertContains($col, $columns, "follow_ups should have $col column");
        }
    }
}

