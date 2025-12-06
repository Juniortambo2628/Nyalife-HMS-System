<?php
/**
 * Nyalife HMS - Invoice Data Functions
 *
 * Contains functions for retrieving and manipulating invoice data.
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Get invoice with items and patient details
 *
 * @param int $invoiceId Invoice ID
 * @return array|null Invoice data with items or null if not found
 */
function getInvoiceWithItems($invoiceId)
{
    global $conn;

    $query = "SELECT i.*, 
              p.patient_number,
              CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
              pu.email as patient_email, pu.phone as patient_phone,
              CONCAT(du.first_name, ' ', du.last_name) as doctor_name
              FROM invoices i
              JOIN patients p ON i.patient_id = p.patient_id
              JOIN users pu ON p.user_id = pu.user_id
              LEFT JOIN users du ON i.doctor_id = du.user_id
              WHERE i.invoice_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $invoiceId);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoice = $result->fetch_assoc();
    $stmt->close();

    if ($invoice) {
        // Get invoice items
        $invoice['items'] = getInvoiceItems($invoiceId);
    }

    return $invoice;
}

/**
 * Get invoice items
 *
 * @param int $invoiceId Invoice ID
 * @return array Array of invoice items
 */
function getInvoiceItems($invoiceId)
{
    global $conn;

    $query = "SELECT ii.*, s.service_name, s.description as service_description
              FROM invoice_items ii
              LEFT JOIN services s ON ii.service_id = s.service_id
              WHERE ii.invoice_id = ?
              ORDER BY ii.item_id ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $invoiceId);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $items;
}

/**
 * Get invoices by patient
 *
 * @param int $patientId Patient ID
 * @param string $status Optional status filter
 * @return array Array of invoices
 */
function getInvoicesByPatient($patientId, $status = null)
{
    global $conn;

    $query = "SELECT i.*, 
              CONCAT(du.first_name, ' ', du.last_name) as doctor_name
              FROM invoices i
              LEFT JOIN users du ON i.doctor_id = du.user_id
              WHERE i.patient_id = ?";

    $params = [$patientId];

    if ($status) {
        $query .= " AND i.status = ?";
        $params[] = $status;
    }

    $query .= " ORDER BY i.created_at DESC";

    $stmt = $conn->prepare($query);
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);

    $stmt->execute();
    $result = $stmt->get_result();
    $invoices = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    return $invoices;
}

/**
 * Get invoices by doctor
 *
 * @param int $doctorId Doctor ID
 * @param string $status Optional status filter
 * @return array Array of invoices
 */
function getInvoicesByDoctor($doctorId, $status = null)
{
    global $conn;

    $query = "SELECT i.*, 
              p.patient_number,
              CONCAT(pu.first_name, ' ', pu.last_name) as patient_name
              FROM invoices i
              JOIN patients p ON i.patient_id = p.patient_id
              JOIN users pu ON p.user_id = pu.user_id
              WHERE i.doctor_id = ?";

    $params = [$doctorId];

    if ($status) {
        $query .= " AND i.status = ?";
        $params[] = $status;
    }

    $query .= " ORDER BY i.created_at DESC";

    $stmt = $conn->prepare($query);
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);

    $stmt->execute();
    $result = $stmt->get_result();
    $invoices = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    return $invoices;
}

/**
 * Create a new invoice
 *
 * @param array $data Invoice data
 * @return int|false New invoice ID or false on failure
 */
function createInvoice($data)
{
    global $conn;

    try {
        $conn->begin_transaction();

        $query = "INSERT INTO invoices (
                    patient_id, doctor_id, invoice_number, 
                    subtotal, tax_amount, total_amount, 
                    status, due_date, created_at
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $patientId = $data['patient_id'] ?? null;
        $doctorId = $data['doctor_id'] ?? null;
        $invoiceNumber = generateInvoiceNumber();
        $subtotal = $data['subtotal'] ?? 0;
        $taxAmount = $data['tax_amount'] ?? 0;
        $totalAmount = $data['total_amount'] ?? 0;
        $status = $data['status'] ?? 'pending';
        $dueDate = $data['due_date'] ?? date('Y-m-d', strtotime('+30 days'));
        $createdAt = date('Y-m-d H:i:s');

        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "iisdddsis",
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
                addInvoiceItem($invoiceId, $item);
            }
        }

        $conn->commit();
        return $invoiceId;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error creating invoice: " . $e->getMessage());
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
function addInvoiceItem($invoiceId, $itemData)
{
    global $conn;

    $query = "INSERT INTO invoice_items (
                invoice_id, service_id, description, 
                quantity, unit_price, total_price
              ) VALUES (?, ?, ?, ?, ?, ?)";

    $serviceId = $itemData['service_id'] ?? null;
    $description = $itemData['description'] ?? '';
    $quantity = $itemData['quantity'] ?? 1;
    $unitPrice = $itemData['unit_price'] ?? 0;
    $totalPrice = $quantity * $unitPrice;

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "iisddd",
        $invoiceId,
        $serviceId,
        $description,
        $quantity,
        $unitPrice,
        $totalPrice
    );

    return $stmt->execute();
}

/**
 * Update invoice status
 *
 * @param int $invoiceId Invoice ID
 * @param string $status New status
 * @return bool Success status
 */
function updateInvoiceStatus($invoiceId, $status): bool
{
    global $conn;

    $query = "UPDATE invoices SET status = ?, updated_at = ? WHERE invoice_id = ?";

    $updatedAt = date('Y-m-d H:i:s');

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $status, $updatedAt, $invoiceId);

    return $stmt->execute() && $stmt->affected_rows > 0;
}

/**
 * Generate unique invoice number
 *
 * @return string Invoice number
 */
if (!function_exists('generateInvoiceNumber')) {
    function generateInvoiceNumber(): string
    {
        global $conn;

        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');

        // Get the last invoice number for this month
        $query = "SELECT invoice_number FROM invoices 
              WHERE invoice_number LIKE ? 
              ORDER BY invoice_id DESC LIMIT 1";

        $pattern = "{$prefix}{$year}{$month}%";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row) {
            // Extract the sequence number and increment
            $lastNumber = $row['invoice_number'];
            $sequence = (int)substr((string) $lastNumber, -4) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

/**
 * Get invoice statistics
 *
 * @param string $period Period (today, week, month, year)
 * @return array Invoice statistics
 */
function getInvoiceStatistics($period = 'month')
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in getInvoiceStatistics");
        return [
            'total_invoices' => 0,
            'pending_invoices' => 0,
            'paid_invoices' => 0,
            'overdue_invoices' => 0,
            'total_amount' => 0,
            'paid_amount' => 0
        ];
    }

    $startDate = '';
    $endDate = date('Y-m-d');

    $startDate = match ($period) {
        'today' => date('Y-m-d'),
        'week' => date('Y-m-d', strtotime('-1 week')),
        'month' => date('Y-m-d', strtotime('-1 month')),
        'year' => date('Y-m-d', strtotime('-1 year')),
        default => date('Y-m-d', strtotime('-1 month')),
    };

    $query = "SELECT 
                COUNT(*) as total_invoices,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_invoices,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_invoices,
                SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_invoices,
                SUM(total_amount) as total_amount,
                SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as paid_amount
              FROM invoices
              WHERE created_at BETWEEN ? AND ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    $stats = $result->fetch_assoc();
    $conn->close();
    return $stats;
}

/**
 * Get overdue invoices
 *
 * @return array Array of overdue invoices
 */
function getOverdueInvoices()
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in getOverdueInvoices");
        return [];
    }

    $query = "SELECT i.*, 
              p.patient_number,
              CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
              pu.email as patient_email, pu.phone as patient_phone,
              CONCAT(du.first_name, ' ', du.last_name) as doctor_name
              FROM invoices i
              JOIN patients p ON i.patient_id = p.patient_id
              JOIN users pu ON p.user_id = pu.user_id
              LEFT JOIN users du ON i.doctor_id = du.user_id
              WHERE i.due_date < CURDATE() 
              AND i.status IN ('pending', 'partially_paid')
              ORDER BY i.due_date ASC";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoices = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    return $invoices;
}

/**
 * Get pending invoices (unpaid or partially paid)
 *
 * @return array Array of pending invoices
 */
function getPendingInvoices()
{
    $conn = connectDB();

    if (!$conn) {
        error_log("Database connection failed in getPendingInvoices");
        return [];
    }

    $query = "SELECT i.*, 
              p.patient_number,
              CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
              pu.email as patient_email, pu.phone as patient_phone,
              CONCAT(du.first_name, ' ', du.last_name) as doctor_name
              FROM invoices i
              JOIN patients p ON i.patient_id = p.patient_id
              JOIN users pu ON p.user_id = pu.user_id
              LEFT JOIN users du ON i.doctor_id = du.user_id
              WHERE i.status IN ('pending', 'partially_paid')
              ORDER BY i.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoices = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();

    return $invoices;
}
