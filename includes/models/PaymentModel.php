<?php

/**
 * Nyalife HMS - Payment Model
 *
 * Model for handling payment data.
 */

require_once __DIR__ . '/BaseModel.php';

class PaymentModel extends BaseModel
{
    protected $table = 'payments';
    protected $primaryKey = 'payment_id';

    /**
     * Get payment with invoice and patient details
     *
     * @param int $paymentId Payment ID
     * @return array|null Payment data with details or null if not found
     */
    public function getPaymentWithDetails($paymentId)
    {
        try {
            $sql = "SELECT p.*, 
                    i.invoice_number, i.total_amount as invoice_total,
                    CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                    pu.email as patient_email, pu.phone as patient_phone
                    FROM {$this->table} p
                    JOIN invoices i ON p.invoice_id = i.invoice_id
                    JOIN patients pt ON i.patient_id = pt.patient_id
                    JOIN users pu ON pt.user_id = pu.user_id
                    WHERE p.payment_id = ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $paymentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $payment = $result->fetch_assoc();
            $stmt->close();

            return $payment;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return null;
        }
    }

    /**
     * Get payments by patient
     *
     * @param int $patientId Patient ID
     * @param string $status Optional status filter
     * @return array Array of payments
     */
    public function getPaymentsByPatient($patientId, $status = null)
    {
        try {
            $sql = "SELECT p.*, i.invoice_number, i.total_amount as invoice_total
                    FROM {$this->table} p
                    JOIN invoices i ON p.invoice_id = i.invoice_id
                    WHERE i.patient_id = ?";

            $params = [$patientId];

            if ($status) {
                $sql .= " AND p.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY p.payment_date DESC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $payments = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $payments;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get payments by invoice
     *
     * @param int $invoiceId Invoice ID
     * @return array Array of payments for the invoice
     */
    public function getPaymentsByInvoice($invoiceId)
    {
        try {
            $sql = "SELECT p.*, 
                    CONCAT(u.first_name, ' ', u.last_name) as received_by_name
                    FROM {$this->table} p
                    LEFT JOIN users u ON p.received_by = u.user_id
                    WHERE p.invoice_id = ?
                    ORDER BY p.payment_date DESC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $invoiceId);
            $stmt->execute();
            $result = $stmt->get_result();
            $payments = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $payments;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Create a new payment
     *
     * @param array $data Payment data
     * @return int|false New payment ID or false on failure
     */
    public function createPayment($data)
    {
        try {
            $this->beginTransaction();

            $sql = "INSERT INTO {$this->table} (
                        invoice_id, payment_amount, payment_method, 
                        payment_date, reference_number, notes, 
                        status, received_by, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $invoiceId = $data['invoice_id'] ?? null;
            $paymentAmount = $data['payment_amount'] ?? 0;
            $paymentMethod = $data['payment_method'] ?? 'cash';
            $paymentDate = $data['payment_date'] ?? date('Y-m-d');
            $referenceNumber = $data['reference_number'] ?? '';
            $notes = $data['notes'] ?? '';
            $status = $data['status'] ?? 'completed';
            $receivedBy = $data['received_by'] ?? null;
            $createdAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param(
                'idssssis',
                $invoiceId,
                $paymentAmount,
                $paymentMethod,
                $paymentDate,
                $referenceNumber,
                $notes,
                $status,
                $receivedBy,
                $createdAt
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to create payment");
            }

            $paymentId = $stmt->insert_id;
            $stmt->close();

            // Update invoice status if payment is completed
            if ($status === 'completed') {
                $this->updateInvoicePaymentStatus($invoiceId);
            }

            $this->commitTransaction();
            return $paymentId;
        } catch (Exception $e) {
            $this->rollbackTransaction();
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Update payment status
     *
     * @param int $paymentId Payment ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updatePaymentStatus($paymentId, $status)
    {
        try {
            $sql = "UPDATE {$this->table} SET status = ?, updated_at = ? WHERE {$this->primaryKey} = ?";

            $updatedAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('ssi', $status, $updatedAt, $paymentId);

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Update invoice payment status based on payments
     *
     * @param int $invoiceId Invoice ID
     * @return bool Success status
     */
    private function updateInvoicePaymentStatus($invoiceId): bool
    {
        try {
            // Get total paid amount for the invoice
            $sql = "SELECT SUM(amount) as total_paid 
                    FROM {$this->table} 
                    WHERE invoice_id = ? AND payment_status = 'completed'";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $invoiceId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            $totalPaid = $row['total_paid'] ?? 0;

            // Get invoice total
            $sql = "SELECT total_amount FROM invoices WHERE invoice_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $invoiceId);
            $stmt->execute();
            $result = $stmt->get_result();
            $invoice = $result->fetch_assoc();
            $stmt->close();

            if ($invoice) {
                $invoiceTotal = $invoice['total_amount'];
                $newStatus = ($totalPaid >= $invoiceTotal) ? 'paid' : 'partial';

                // Update invoice status
                $sql = "UPDATE invoices SET status = ?, updated_at = ? WHERE invoice_id = ?";
                $updatedAt = date('Y-m-d H:i:s');

                $stmt = $this->db->prepare($sql);
                $stmt->bind_param('ssi', $newStatus, $updatedAt, $invoiceId);
                $stmt->execute();
                $stmt->close();
            }

            return true;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get payment statistics
     *
     * @param string $period Period (today, week, month, year)
     * @return array Payment statistics
     */
    public function getPaymentStatistics($period = 'month')
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
                        COUNT(*) as total_payments,
                        SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed_payments,
                        SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
                        SUM(CASE WHEN payment_status = 'failed' THEN 1 ELSE 0 END) as failed_payments,
                        SUM(amount) as total_amount,
                        SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as completed_amount,
                        AVG(amount) as avg_payment
                    FROM {$this->table}
                    WHERE payment_date BETWEEN ? AND ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ss', $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_assoc();
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [
                'total_payments' => 0,
                'completed_payments' => 0,
                'pending_payments' => 0,
                'failed_payments' => 0,
                'total_amount' => 0,
                'completed_amount' => 0,
                'avg_payment' => 0
            ];
        }
    }

    /**
     * Get payments by method
     *
     * @param string $method Payment method
     * @param string $period Period filter
     * @return array Array of payments
     */
    public function getPaymentsByMethod($method, $period = 'month')
    {
        try {
            $startDate = date('Y-m-d', strtotime('-1 month'));
            $endDate = date('Y-m-d');

            if ($period === 'today') {
                $startDate = date('Y-m-d');
            } elseif ($period === 'week') {
                $startDate = date('Y-m-d', strtotime('-1 week'));
            } elseif ($period === 'year') {
                $startDate = date('Y-m-d', strtotime('-1 year'));
            }

            $sql = "SELECT p.*, i.invoice_number,
                    CONCAT(pu.first_name, ' ', pu.last_name) as patient_name
                    FROM {$this->table} p
                    JOIN invoices i ON p.invoice_id = i.invoice_id
                    JOIN patients pt ON i.patient_id = pt.patient_id
                    JOIN users pu ON pt.user_id = pu.user_id
                    WHERE p.payment_method = ? AND p.payment_date BETWEEN ? AND ?
                    ORDER BY p.payment_date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('sss', $method, $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result();
            $payments = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $payments;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get payments with filters
     *
     * @param array $filters Filters to apply
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Array of payments
     */
    public function getPaymentsFiltered(array $filters = [], $page = 1, $perPage = 20)
    {
        try {
            $offset = ($page - 1) * $perPage;

            $sql = "SELECT p.*, i.invoice_number, i.total_amount as invoice_total,
                    CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                    CONCAT(u.first_name, ' ', u.last_name) as received_by_name
                    FROM {$this->table} p
                    JOIN invoices i ON p.invoice_id = i.invoice_id
                    JOIN patients pt ON i.patient_id = pt.patient_id
                    JOIN users pu ON pt.user_id = pu.user_id
                    LEFT JOIN users u ON p.received_by = u.user_id
                    WHERE 1=1";

            $params = [];
            $types = '';

            if (!empty($filters['status'])) {
                $sql .= " AND p.payment_status = ?";
                $params[] = $filters['status'];
                $types .= 's';
            }

            if (!empty($filters['method'])) {
                $sql .= " AND p.payment_method = ?";
                $params[] = $filters['method'];
                $types .= 's';
            }

            if (!empty($filters['invoice_id'])) {
                $sql .= " AND p.invoice_id = ?";
                $params[] = $filters['invoice_id'];
                $types .= 'i';
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND p.payment_date >= ?";
                $params[] = $filters['date_from'];
                $types .= 's';
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND p.payment_date <= ?";
                $params[] = $filters['date_to'];
                $types .= 's';
            }

            $sql .= " ORDER BY p.payment_date DESC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            $types .= 'ii';

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            // $params always has at least $perPage and $offset, so always bind
            $stmt->bind_param($types, ...$params);

            $stmt->execute();
            $result = $stmt->get_result();
            $payments = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $payments;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Count payments with filters
     *
     * @param array $filters Filters to apply
     * @return int Count of payments
     */
    public function countPaymentsFiltered(array $filters = [])
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} p
                    JOIN invoices i ON p.invoice_id = i.invoice_id
                    WHERE 1=1";

            $params = [];
            $types = '';

            if (!empty($filters['status'])) {
                $sql .= " AND p.payment_status = ?";
                $params[] = $filters['status'];
                $types .= 's';
            }

            if (!empty($filters['method'])) {
                $sql .= " AND p.payment_method = ?";
                $params[] = $filters['method'];
                $types .= 's';
            }

            if (!empty($filters['invoice_id'])) {
                $sql .= " AND p.invoice_id = ?";
                $params[] = $filters['invoice_id'];
                $types .= 'i';
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND p.payment_date >= ?";
                $params[] = $filters['date_from'];
                $types .= 's';
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND p.payment_date <= ?";
                $params[] = $filters['date_to'];
                $types .= 's';
            }

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            if ($params !== []) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row['count'] ?? 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return 0;
        }
    }

    /**
     * Update payment
     *
     * @param int $paymentId Payment ID
     * @param array $data Payment data
     * @return bool Success status
     */
    public function updatePayment($paymentId, $data)
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                    payment_amount = ?, payment_method = ?, payment_date = ?,
                    reference_number = ?, notes = ?, status = ?, updated_at = ?
                    WHERE {$this->primaryKey} = ?";

            $paymentAmount = $data['payment_amount'] ?? 0;
            $paymentMethod = $data['payment_method'] ?? 'cash';
            $paymentDate = $data['payment_date'] ?? date('Y-m-d');
            $referenceNumber = $data['reference_number'] ?? '';
            $notes = $data['notes'] ?? '';
            $status = $data['status'] ?? 'completed';
            $updatedAt = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param(
                'dsssssi',
                $paymentAmount,
                $paymentMethod,
                $paymentDate,
                $referenceNumber,
                $notes,
                $status,
                $updatedAt,
                $paymentId
            );

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Delete payment
     *
     * @param int $paymentId Payment ID
     * @return bool Success status
     */
    public function deletePayment($paymentId)
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $paymentId);

            return $stmt->execute() && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }
}
