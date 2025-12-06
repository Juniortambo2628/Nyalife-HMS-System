<?php

/**
 * Nyalife HMS - Payment Controller
 *
 * Controller for managing payments.
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/PaymentModel.php';
require_once __DIR__ . '/../../models/InvoiceModel.php';
require_once __DIR__ . '/../../data/invoice_data.php';
require_once __DIR__ . '/../../core/SessionManager.php';

class PaymentController extends WebController
{
    private readonly \PaymentModel $paymentModel;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->paymentModel = new PaymentModel();
        $this->pageTitle = 'Payments - Nyalife HMS';
    }

    /**
     * Display payments list
     */
    public function index(): void
    {
        // Check if user has permission to view payments
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $filters = [
            'status' => $_GET['status'] ?? null,
            'method' => $_GET['method'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'invoice_id' => $_GET['invoice_id'] ?? null
        ];

        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 20;

        $payments = $this->paymentModel->getPaymentsFiltered($filters, $page, $perPage);
        $totalPayments = $this->paymentModel->countPaymentsFiltered($filters);
        $totalPages = ceil($totalPayments / $perPage);

        $statistics = $this->paymentModel->getPaymentStatistics('month');

        $this->renderView('payments/index', [
            'payments' => $payments,
            'filters' => $filters,
            'statistics' => $statistics,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalItems' => $totalPayments
            ],
            'pageTitle' => 'Payments'
        ]);
    }

    /**
     * Display payment details
     */
    public function show(int $id): void
    {
        // Check if user has permission to view payments
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $payment = $this->paymentModel->getPaymentWithDetails($id);

        if (!$payment) {
            $this->setFlashMessage('error', 'Payment not found.');
            $this->redirect('payments');
            return;
        }

        $this->renderView('payments/show', [
            'payment' => $payment,
            'pageTitle' => 'Payment Details'
        ]);
    }

    /**
     * Display create payment form
     */
    public function create(): void
    {
        // Check if user has permission to create payments
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $invoiceId = $_GET['invoice_id'] ?? null;
        $invoice = null;

        if ($invoiceId) {
            $invoice = getInvoiceWithItems($invoiceId);
        }

        $invoices = getPendingInvoices();
        $paymentMethods = [
            'cash' => 'Cash',
            'card' => 'Credit/Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'mobile_money' => 'Mobile Money',
            'insurance' => 'Insurance',
            'other' => 'Other'
        ];

        $this->renderView('payments/create', [
            'invoice' => $invoice,
            'invoices' => $invoices,
            'paymentMethods' => $paymentMethods,
            'pageTitle' => 'Create Payment'
        ]);
    }

    /**
     * Store new payment
     */
    public function store(): void
    {
        // Check if user has permission to create payments
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== SessionManager::get('csrf_token')) {
            $this->setFlashMessage('error', 'Invalid request.');
            $this->redirect('payments/create');
            return;
        }

        $data = [
            'invoice_id' => $_POST['invoice_id'] ?? null,
            'payment_amount' => $_POST['payment_amount'] ?? 0,
            'payment_method' => $_POST['payment_method'] ?? 'cash',
            'payment_date' => $_POST['payment_date'] ?? date('Y-m-d'),
            'reference_number' => $_POST['reference_number'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'status' => $_POST['status'] ?? 'completed',
            'received_by' => SessionManager::get('user_id')
        ];

        // Validate required fields
        if (empty($data['invoice_id'])) {
            $this->setFlashMessage('error', 'Invoice is required.');
            $this->redirect('payments/create');
            return;
        }

        if (empty($data['payment_amount']) || $data['payment_amount'] <= 0) {
            $this->setFlashMessage('error', 'Payment amount must be greater than zero.');
            $this->redirect('payments/create');
            return;
        }

        $paymentId = $this->paymentModel->createPayment($data);

        if ($paymentId) {
            $this->setFlashMessage('success', 'Payment created successfully.');
            $this->redirect("payments/show/{$paymentId}");
        } else {
            $this->setFlashMessage('error', 'Failed to create payment.');
            $this->redirect('payments/create');
        }
    }

    /**
     * Display edit payment form
     */
    public function edit(int $id): void
    {
        // Check if user has permission to edit payments
        if (!$this->auth->hasRole('admin')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $payment = $this->paymentModel->getPaymentWithDetails($id);

        if (!$payment) {
            $this->setFlashMessage('error', 'Payment not found.');
            $this->redirect('payments');
            return;
        }

        $paymentMethods = [
            'cash' => 'Cash',
            'card' => 'Credit/Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'mobile_money' => 'Mobile Money',
            'insurance' => 'Insurance',
            'other' => 'Other'
        ];

        $this->renderView('payments/edit', [
            'payment' => $payment,
            'paymentMethods' => $paymentMethods,
            'pageTitle' => 'Edit Payment'
        ]);
    }

    /**
     * Update payment
     */
    public function update(int $id): void
    {
        // Check if user has permission to edit payments
        if (!$this->auth->hasRole('admin')) {
            $this->redirect('error/unauthorized');
            return;
        }

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== SessionManager::get('csrf_token')) {
            $this->setFlashMessage('error', 'Invalid request.');
            $this->redirect("payments/edit/{$id}");
            return;
        }

        $data = [
            'payment_amount' => $_POST['payment_amount'] ?? 0,
            'payment_method' => $_POST['payment_method'] ?? 'cash',
            'payment_date' => $_POST['payment_date'] ?? date('Y-m-d'),
            'reference_number' => $_POST['reference_number'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'status' => $_POST['status'] ?? 'completed'
        ];

        // Validate required fields
        if (empty($data['payment_amount']) || $data['payment_amount'] <= 0) {
            $this->setFlashMessage('error', 'Payment amount must be greater than zero.');
            $this->redirect("payments/edit/{$id}");
            return;
        }

        $success = $this->paymentModel->updatePayment($id, $data);

        if ($success) {
            $this->setFlashMessage('success', 'Payment updated successfully.');
            $this->redirect("payments/show/{$id}");
        } else {
            $this->setFlashMessage('error', 'Failed to update payment.');
            $this->redirect("payments/edit/{$id}");
        }
    }

    /**
     * Delete payment
     */
    public function delete(int $id): void
    {
        // Check if user has permission to delete payments
        if (!$this->auth->hasRole('admin')) {
            $this->redirect('error/unauthorized');
            return;
        }

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== SessionManager::get('csrf_token')) {
            $this->setFlashMessage('error', 'Invalid request.');
            $this->redirect('payments');
            return;
        }

        $success = $this->paymentModel->deletePayment($id);

        if ($success) {
            $this->setFlashMessage('success', 'Payment deleted successfully.');
        } else {
            $this->setFlashMessage('error', 'Failed to delete payment.');
        }

        $this->redirect('payments');
    }

    /**
     * Print payment receipt
     */
    public function print(int $id): void
    {
        // Check if user has permission to view payments
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $payment = $this->paymentModel->getPaymentWithDetails($id);

        if (!$payment) {
            $this->setFlashMessage('error', 'Payment not found.');
            $this->redirect('payments');
            return;
        }

        $this->renderView('payments/print', [
            'payment' => $payment,
            'pageTitle' => 'Payment Receipt'
        ]);
    }

    /**
     * Get payments for AJAX requests
     */
    public function getPayments(): void
    {
        // Check if user has permission to view payments
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }

        $filters = [
            'status' => $_GET['status'] ?? null,
            'method' => $_GET['method'] ?? null,
            'invoice_id' => $_GET['invoice_id'] ?? null
        ];

        $payments = $this->paymentModel->getPaymentsFiltered($filters, 1, 50);

        $this->jsonResponse([
            'success' => true,
            'payments' => $payments
        ]);
    }

    /**
     * Get payment statistics
     */
    public function statistics(): void
    {
        // Check if user has permission to view statistics
        if (!$this->auth->hasRole('admin')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $period = $_GET['period'] ?? 'month';
        $statistics = $this->paymentModel->getPaymentStatistics($period);

        $this->renderView('payments/statistics', [
            'statistics' => $statistics,
            'period' => $period,
            'pageTitle' => 'Payment Statistics'
        ]);
    }
}
