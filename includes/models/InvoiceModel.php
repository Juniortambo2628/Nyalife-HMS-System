<?php

/**
 * Nyalife HMS - Invoice Model
 *
 * Model for handling invoice data.
 */

require_once __DIR__ . '/BaseModel.php';

class InvoiceModel extends BaseModel
{
    protected $table = 'invoices';
    protected $primaryKey = 'invoice_id';

    /**
     * Get invoice with items and patient details
     *
     * @param int $invoiceId Invoice ID
     * @return array|null Invoice data with items or null if not found
     */
    public function getInvoiceWithItems($invoiceId)
    {
        try {
            $sql = "SELECT i.*, 
                    p.patient_number,
                    CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                    pu.email as patient_email, pu.phone as patient_phone,
                    CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                    COALESCE(s.specialization_name, 'General') as doctor_specialization
                    FROM {$this->table} i
                    JOIN patients p ON i.patient_id = p.patient_id
                    JOIN users pu ON p.user_id = pu.user_id
                    LEFT JOIN doctors d ON i.doctor_id = d.doctor_id
                    LEFT JOIN users du ON d.user_id = du.user_id
                    LEFT JOIN specializations s ON d.specialization_id = s.specialization_id
                    WHERE i.invoice_id = ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $invoiceId);
            $stmt->execute();
            $result = $stmt->get_result();
            $invoice = $result->fetch_assoc();
            $stmt->close();

            if ($invoice) {
                // Get invoice items
                $invoice['items'] = $this->getInvoiceItems($invoiceId);
            }

            return $invoice;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Get invoice items
     *
     * @param int $invoiceId Invoice ID
     * @return array Array of invoice items
     */
    public function getInvoiceItems($invoiceId)
    {
        try {
            $sql = "SELECT ii.*, s.service_name, s.description as service_description
                    FROM invoice_items ii
                    LEFT JOIN services s ON ii.service_id = s.service_id
                    WHERE ii.invoice_id = ?
                    ORDER BY ii.item_id ASC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $invoiceId);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $items;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get invoices by patient
     *
     * @param int $patientId Patient ID
     * @param string $status Optional status filter
     * @return array Array of invoices
     */
    public function getInvoicesByPatient($patientId, $status = null)
    {
        try {
            $sql = "SELECT i.*, 
                    CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
                    COALESCE(s.specialization_name, 'General') as doctor_specialization
                    FROM {$this->table} i
                    LEFT JOIN doctors d ON i.doctor_id = d.doctor_id
                    LEFT JOIN users du ON d.user_id = du.user_id
                    LEFT JOIN specializations s ON d.specialization_id = s.specialization_id
                    WHERE i.patient_id = ?";

            $params = [$patientId];

            if ($status) {
                $sql .= " AND i.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY i.created_at DESC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $invoices = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $invoices;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get invoices by doctor
     *
     * @param int $doctorId Doctor ID
     * @param string $status Optional status filter
     * @return array Array of invoices
     */
    public function getInvoicesByDoctor($doctorId, $status = null)
    {
        try {
            $sql = "SELECT i.*, 
                    p.patient_number,
                    CONCAT(pu.first_name, ' ', pu.last_name) as patient_name
                    FROM {$this->table} i
                    JOIN patients p ON i.patient_id = p.patient_id
                    JOIN users pu ON p.user_id = pu.user_id
                    WHERE i.doctor_id = ?";

            $params = [$doctorId];

            if ($status) {
                $sql .= " AND i.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY i.created_at DESC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $invoices = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $invoices;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Create a new invoice
     *
     * @param array $data Invoice data
     * @return int|false New invoice ID or false on failure
     */
    public function createInvoice($data)
    {
        try {
            $this->beginTransaction();

            $sql = "INSERT INTO {$this->table} (
                        patient_id, doctor_id, invoice_number, 
                        subtotal, tax_amount, total_amount, 
                        status, due_date, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $patientId = $data['patient_id'] ?? null;
            $doctorId = $data['doctor_id'] ?? null;
            $invoiceNumber = $this->generateInvoiceNumber();
            $subtotal = $data['subtotal'] ?? 0;
            $taxAmount = $data['tax_amount'] ?? 0;
            $totalAmount = $data['total_amount'] ?? 0;
            $status = $data['status'] ?? 'pending';
            $dueDate = $data['due_date'] ?? date('Y-m-d', strtotime('+30 days'));
            $createdAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param(
                'iisdddsis',
                $patientId,
                $doctorId,
                $invoiceNumber,
                $subtotal,
                $taxAmount,
                $totalAmount,
                $status,
                $dueDate,
                $createdAt
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to create invoice");
            }

            $invoiceId = $stmt->insert_id;
            $stmt->close();

            // Add invoice items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $this->addInvoiceItem($invoiceId, $item);
                }
            }

            $this->commitTransaction();
            return $invoiceId;
        } catch (Exception $e) {
            $this->rollbackTransaction();
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Add item to invoice
     *
     * @param int $invoiceId Invoice ID
     * @param array $itemData Item data
     * @return bool Success status
     */
    public function addInvoiceItem($invoiceId, $itemData)
    {
        try {
            $sql = "INSERT INTO invoice_items (
                        invoice_id, service_id, description, 
                        quantity, unit_price, total_price
                    ) VALUES (?, ?, ?, ?, ?, ?)";

            $serviceId = $itemData['service_id'] ?? null;
            $description = $itemData['description'] ?? '';
            $quantity = $itemData['quantity'] ?? 1;
            $unitPrice = $itemData['unit_price'] ?? 0;
            $totalPrice = $quantity * $unitPrice;

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param(
                'iisddd',
                $invoiceId,
                $serviceId,
                $description,
                $quantity,
                $unitPrice,
                $totalPrice
            );

            return $stmt->execute();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Update invoice status
     *
     * @param int $invoiceId Invoice ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateStatus($invoiceId, $status)
    {
        try {
            $sql = "UPDATE {$this->table} SET status = ?, updated_at = ? WHERE {$this->primaryKey} = ?";

            $updatedAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('ssi', $status, $updatedAt, $invoiceId);

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Generate unique invoice number
     *
     * @return string Invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');

        // Get the last invoice number for this month
        $sql = "SELECT invoice_number FROM {$this->table} 
                WHERE invoice_number LIKE ? 
                ORDER BY invoice_id DESC LIMIT 1";

        $pattern = "{$prefix}{$year}{$month}%";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row) {
            // Extract the sequence number and increment
            $lastNumber = $row['invoice_number'];
            $sequence = (int)substr($lastNumber, -4) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix . $year . $month . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get invoice statistics
     *
     * @param string $period Period (today, week, month, year)
     * @return array Invoice statistics
     */
    public function getInvoiceStatistics($period = 'month')
    {
        try {
            $startDate = '';
            $endDate = date('Y-m-d');

            $startDate = match ($period) {
                'today' => date('Y-m-d'),
                'week' => date('Y-m-d', strtotime('-1 week')),
                'month' => date('Y-m-d', strtotime('-1 month')),
                'year' => date('Y-m-d', strtotime('-1 year')),
                default => date('Y-m-d', strtotime('-1 month')),
            };

            $sql = "SELECT 
                        COUNT(*) as total_invoices,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_invoices,
                        SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_invoices,
                        SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_invoices,
                        SUM(total_amount) as total_amount,
                        SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as paid_amount
                    FROM {$this->table}
                    WHERE created_at BETWEEN ? AND ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ss', $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_assoc();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [
                'total_invoices' => 0,
                'pending_invoices' => 0,
                'paid_invoices' => 0,
                'overdue_invoices' => 0,
                'total_amount' => 0,
                'paid_amount' => 0
            ];
        }
    }
}
