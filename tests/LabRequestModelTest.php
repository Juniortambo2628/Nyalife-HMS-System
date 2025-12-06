<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/models/LabRequestModel.php';

final class LabRequestModelTest extends TestCase {
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

        $this->model = new LabRequestModel();
    }

    protected function tearDown(): void {
        if ($this->conn instanceof mysqli) $this->conn->close();
    }

    public function testLabRequestsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'lab_requests'");
        $this->assertTrue($result->num_rows > 0, 'lab_requests table should exist');
    }

    public function testLabRequestsTableStructure(): void {
        $result = $this->conn->query("DESCRIBE lab_requests");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $this->assertContains('request_id', $columns, 'lab_requests should have request_id');
        $this->assertContains('patient_id', $columns, 'lab_requests should have patient_id');
        $this->assertContains('requested_by', $columns, 'lab_requests should have requested_by');
        $this->assertContains('status', $columns, 'lab_requests should have status');
        $this->assertContains('priority', $columns, 'lab_requests should have priority');
    }

    public function testLabTestRequestsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'lab_test_requests'");
        $this->assertTrue($result->num_rows > 0, 'lab_test_requests table should exist');
    }

    public function testLabTestItemsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'lab_test_items'");
        $this->assertTrue($result->num_rows > 0, 'lab_test_items table should exist');
    }

    public function testLabTestTypesTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'lab_test_types'");
        $this->assertTrue($result->num_rows > 0, 'lab_test_types table should exist');
    }

    public function testGetPendingRequests(): void {
        $requests = $this->model->getPendingRequests();
        $this->assertIsArray($requests, 'getPendingRequests should return an array');
    }

    public function testGetRequestStatistics(): void {
        $stats = $this->model->getRequestStatistics('month');
        $this->assertIsArray($stats, 'getRequestStatistics should return an array');
        $this->assertArrayHasKey('total_requests', $stats);
        $this->assertArrayHasKey('pending_requests', $stats);
        $this->assertArrayHasKey('completed_requests', $stats);
    }

    public function testGetStatusClass(): void {
        $this->assertEquals('badge bg-warning', $this->model->getStatusClass('pending'));
        $this->assertEquals('badge bg-info', $this->model->getStatusClass('processing'));
        $this->assertEquals('badge bg-success', $this->model->getStatusClass('completed'));
        $this->assertEquals('badge bg-danger', $this->model->getStatusClass('cancelled'));
    }

    public function testGetPriorityClass(): void {
        $this->assertEquals('badge bg-danger', $this->model->getPriorityClass('stat'));
        $this->assertEquals('badge bg-warning', $this->model->getPriorityClass('urgent'));
        $this->assertEquals('badge bg-primary', $this->model->getPriorityClass('routine'));
    }
}


