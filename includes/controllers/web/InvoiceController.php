<?php

/**
 * Nyalife HMS - Invoice Controller
 *
 * Controller for handling invoice-related operations.
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/InvoiceModel.php';
require_once __DIR__ . '/../../data/invoice_data.php';
require_once __DIR__ . '/../../core/SessionManager.php';

class InvoiceController extends WebController
{
    private readonly \InvoiceModel $invoiceModel;

    public function __construct()
    {
        parent::__construct();
        $this->invoiceModel = new InvoiceModel();
    }

    /**
     * Display invoices list
     */
    public function index(): void
    {
        // Check if user has permission to view invoices
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $status = $_GET['status'] ?? null;
        $patientId = $_GET['patient_id'] ?? null;
        $doctorId = $_GET['doctor_id'] ?? null;

        $invoices = [];

        if ($patientId) {
            $invoices = getInvoicesByPatient($patientId, $status);
        } elseif ($doctorId) {
            $invoices = getInvoicesByDoctor($doctorId, $status);
        } else {
            // Get all invoices with filters
            $invoices = $this->getAllInvoices($status);
        }

        $statistics = getInvoiceStatistics();

        $this->renderView('invoices/index', [
            'invoices' => $invoices,
            'statistics' => $statistics,
            'filters' => [
                'status' => $status,
                'patient_id' => $patientId,
                'doctor_id' => $doctorId
            ],
            'pageTitle' => 'Invoices'
        ]);
    }

    /**
     * Display invoice details
     */
    public function show($id): void
    {
        // Check if user has permission to view invoices
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $invoice = getInvoiceWithItems($id);

        if (!$invoice) {
            $this->setFlashMessage('error', 'Invoice not found.');
            $this->redirect('invoices');
            return;
        }

        $this->renderView('invoices/show', [
            'invoice' => $invoice,
            'pageTitle' => 'Invoice Details'
        ]);
    }

    /**
     * Display create invoice form
     */
    public function create(): void
    {
        // Check if user has permission to create invoices
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $patientId = $_GET['patient_id'] ?? null;
        $doctorId = $_GET['doctor_id'] ?? null;

        // Get patients and doctors for form
        $patients = $this->getPatients();
        $doctors = $this->getDoctors();
        $services = $this->getServices();

        $this->renderView('invoices/create', [
            'patients' => $patients,
            'doctors' => $doctors,
            'services' => $services,
            'selectedPatient' => $patientId,
            'selectedDoctor' => $doctorId,
            'pageTitle' => 'Create Invoice'
        ]);
    }

    /**
     * Store new invoice
     */
    public function store(): void
    {
        // Check if user has permission to create invoices
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        // Validate CSRF token - using simple validation for now
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== SessionManager::get('csrf_token')) {
            $this->setFlashMessage('error', 'Invalid request.');
            $this->redirect('invoices/create');
            return;
        }

        $data = [
            'patient_id' => $_POST['patient_id'] ?? null,
            'doctor_id' => $_POST['doctor_id'] ?? null,
            'due_date' => $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
            'items' => $_POST['items'] ?? []
        ];

        // Validate required fields
        if (empty($data['patient_id'])) {
            $this->setFlashMessage('error', 'Patient is required.');
            $this->redirect('invoices/create');
            return;
        }

        if (empty($data['items'])) {
            $this->setFlashMessage('error', 'At least one item is required.');
            $this->redirect('invoices/create');
            return;
        }

        // Calculate totals
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? 0;
            $subtotal += $quantity * $unitPrice;
        }

        $taxRate = 0.16; // 16% VAT
        $taxAmount = $subtotal * $taxRate;
        $totalAmount = $subtotal + $taxAmount;

        $data['subtotal'] = $subtotal;
        $data['tax_amount'] = $taxAmount;
        $data['total_amount'] = $totalAmount;

        $invoiceId = createInvoice($data);

        if ($invoiceId) {
            $this->setFlashMessage('success', 'Invoice created successfully.');
            $this->redirect("invoices/show/{$invoiceId}");
        } else {
            $this->setFlashMessage('error', 'Failed to create invoice.');
            $this->redirect('invoices/create');
        }
    }

    /**
     * Display edit invoice form
     */
    public function edit($id): void
    {
        // Check if user has permission to edit invoices
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $invoice = getInvoiceWithItems($id);

        if (!$invoice) {
            $this->setFlashMessage('error', 'Invoice not found.');
            $this->redirect('invoices');
            return;
        }

        // Get patients and doctors for form
        $patients = $this->getPatients();
        $doctors = $this->getDoctors();
        $services = $this->getServices();

        $this->renderView('invoices/edit', [
            'invoice' => $invoice,
            'patients' => $patients,
            'doctors' => $doctors,
            'services' => $services,
            'pageTitle' => 'Edit Invoice'
        ]);
    }

    /**
     * Update invoice
     */
    public function update($id): void
    {
        // Check if user has permission to edit invoices
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        // Validate CSRF token - using simple validation for now
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== SessionManager::get('csrf_token')) {
            $this->setFlashMessage('error', 'Invalid request.');
            $this->redirect("invoices/edit/{$id}");
            return;
        }

        $data = [
            'patient_id' => $_POST['patient_id'] ?? null,
            'doctor_id' => $_POST['doctor_id'] ?? null,
            'due_date' => $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
            'status' => $_POST['status'] ?? 'pending'
        ];

        // Validate required fields
        if (empty($data['patient_id'])) {
            $this->setFlashMessage('error', 'Patient is required.');
            $this->redirect("invoices/edit/{$id}");
            return;
        }

        $success = $this->invoiceModel->updateStatus($id, $data['status']);

        if ($success) {
            $this->setFlashMessage('success', 'Invoice updated successfully.');
            $this->redirect("invoices/show/{$id}");
        } else {
            $this->setFlashMessage('error', 'Failed to update invoice.');
            $this->redirect("invoices/edit/{$id}");
        }
    }

    /**
     * Print invoice
     */
    public function print($id): void
    {
        // Check if user has permission to view invoices
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $invoice = getInvoiceWithItems($id);

        if (!$invoice) {
            $this->setFlashMessage('error', 'Invoice not found.');
            $this->redirect('invoices');
            return;
        }

        $this->renderView('invoices/print', [
            'invoice' => $invoice,
            'pageTitle' => 'Print Invoice'
        ]);
    }

    /**
     * Get all invoices with filters
     */
    private function getAllInvoices(?string $status = null): array
    {
        $conn = connectDB();

        if (!$conn) {
            error_log("Database connection failed in getAllInvoices");
            return [];
        }

        $query = "SELECT i.*, 
                  p.patient_number,
                  CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
                  COALESCE(CONCAT(du.first_name, ' ', du.last_name), 'N/A') as doctor_name
                  FROM invoices i
                  JOIN patients p ON i.patient_id = p.patient_id
                  JOIN users pu ON p.user_id = pu.user_id
                  LEFT JOIN doctors d ON i.doctor_id = d.doctor_id
                  LEFT JOIN staff s ON d.staff_id = s.staff_id
                  LEFT JOIN users du ON s.user_id = du.user_id";

        $params = [];

        if ($status !== null && $status !== '' && $status !== '0') {
            $query .= " WHERE i.status = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY i.created_at DESC";

        $stmt = $conn->prepare($query);

        if ($params !== []) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $invoices = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();

        return $invoices;
    }

    /**
     * Get patients for form
     */
    private function getPatients(): array
    {
        $conn = connectDB();

        if (!$conn) {
            error_log("Database connection failed in getPatients");
            return [];
        }

        $query = "SELECT p.patient_id, p.patient_number,
                  CONCAT(u.first_name, ' ', u.last_name) as patient_name
                  FROM patients p
                  JOIN users u ON p.user_id = u.user_id
                  ORDER BY u.last_name, u.first_name ASC";

        $result = $conn->query($query);
        $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $conn->close();

        return $data;
    }

    /**
     * Get doctors for form
     */
    private function getDoctors(): array
    {
        $conn = connectDB();

        if (!$conn) {
            error_log("Database connection failed in getDoctors");
            return [];
        }

        $query = "SELECT d.doctor_id,
                  CONCAT(u.first_name, ' ', u.last_name) as doctor_name,
                  COALESCE(s.specialization_name, 'General') as specialization
                  FROM doctors d
                  JOIN users u ON d.user_id = u.user_id
                  LEFT JOIN specializations s ON d.specialization_id = s.specialization_id
                  WHERE d.is_active = 1
                  ORDER BY u.last_name, u.first_name ASC";

        $result = $conn->query($query);
        $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $conn->close();

        return $data;
    }

    /**
     * Get services for form
     */
    private function getServices(): array
    {
        $conn = connectDB();

        if (!$conn) {
            error_log("Database connection failed in getServices");
            return [];
        }

        $query = "SELECT service_id, service_name, price, description
                  FROM services
                  WHERE is_active = 1
                  ORDER BY service_name ASC";

        $result = $conn->query($query);
        $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $conn->close();

        return $data;
    }
}
