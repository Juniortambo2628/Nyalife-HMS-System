<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/models/InvoiceModel.php';

final class InvoiceModelTest extends TestCase {
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

        $this->model = new InvoiceModel();
    }

    protected function tearDown(): void {
        if ($this->conn instanceof mysqli) $this->conn->close();
    }

    public function testInvoicesTableExists(): void {
        $result = $this->conn->query("SHOW TABLES LIKE 'invoices'");
        $this->assertTrue($result->num_rows > 0, 'invoices table should exist');
    }

    public function testGetInvoiceWithItems(): void {
        // Get an existing invoice
        $result = $this->conn->query("SELECT invoice_id FROM invoices LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            $invoiceId = $row['invoice_id'];
            $invoice = $this->model->getInvoiceWithItems($invoiceId);
            
            if ($invoice !== null) {
                $this->assertIsArray($invoice);
                $this->assertEquals($invoiceId, $invoice['invoice_id']);
                $this->assertIsArray($invoice['items']);
            }
        } else {
            $this->expectNotToPerformAssertions();
        }
    }

    public function testInvoicesTableStructure(): void {
        $result = $this->conn->query("DESCRIBE invoices");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $requiredColumns = ['invoice_id', 'invoice_number', 'patient_id', 'total_amount', 'status', 'created_at'];
        foreach ($requiredColumns as $col) {
            $this->assertContains($col, $columns, "invoices should have $col column");
        }
    }
}

