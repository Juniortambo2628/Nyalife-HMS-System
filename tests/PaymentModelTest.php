<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/models/PaymentModel.php';

final class PaymentModelTest extends TestCase {
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

        $this->model = new PaymentModel();
    }

    protected function tearDown(): void {
        if ($this->conn instanceof mysqli) $this->conn->close();
    }

    public function testPaymentsTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'payments'");
        $this->assertTrue($result->num_rows > 0, 'payments table should exist');
    }

    public function testGetPaymentWithDetails(): void {
        // Get an existing payment
        $result = $this->conn->query("SELECT payment_id FROM payments LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            $paymentId = $row['payment_id'];
            $payment = $this->model->getPaymentWithDetails($paymentId);
            
            if ($payment !== null) {
                $this->assertIsArray($payment);
                $this->assertEquals($paymentId, $payment['payment_id']);
            }
        } else {
            $this->expectNotToPerformAssertions();
        }
    }

    public function testPaymentsTableStructure(): void {
        $result = $this->conn->query("DESCRIBE payments");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $requiredColumns = ['payment_id', 'invoice_id', 'amount', 'payment_method', 'status', 'created_at'];
        foreach ($requiredColumns as $col) {
            $this->assertContains($col, $columns, "payments should have $col column");
        }
    }
}

