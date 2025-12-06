<?php

/**
 * Nyalife HMS - Payment API Controller
 *
 * API controller for managing payments.
 */

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../models/PaymentModel.php';
require_once __DIR__ . '/../../models/InvoiceModel.php';

class PaymentController extends ApiController
{
    private readonly \PaymentModel $paymentModel;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->paymentModel = new PaymentModel();
    }

    /**
     * Get all payments with filters
     */
    public function index(): void
    {
        try {
            $this->requireAuth();

            $filters = [
                'status' => $_GET['status'] ?? null,
                'method' => $_GET['method'] ?? null,
                'invoice_id' => $_GET['invoice_id'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null
            ];

            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = intval($_GET['per_page'] ?? 20);

            $payments = $this->paymentModel->getPaymentsFiltered($filters, $page, $perPage);
            $totalPayments = $this->paymentModel->countPaymentsFiltered($filters);

            $this->sendResponse([
                'success' => true,
                'data' => $payments,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $totalPayments,
                    'total_pages' => ceil($totalPayments / $perPage)
                ]
            ]);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get payment by ID
     */
    /**
     * Get payment by ID
     */
    public function show(int $id): void
    {
        try {
            $this->requireAuth();

            $payment = $this->paymentModel->getPaymentWithDetails($id);

            if (!$payment) {
                $this->sendErrorResponse('Payment not found', 404);
                return;
            }

            $this->sendResponse([
                'success' => true,
                'data' => $payment
            ]);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Create new payment
     */
    public function store(): void
    {
        try {
            $this->requireAuth();
            $this->validateRequest(['POST']);

            $data = [
                'invoice_id' => $_POST['invoice_id'] ?? null,
                'payment_amount' => $_POST['payment_amount'] ?? 0,
                'payment_method' => $_POST['payment_method'] ?? 'cash',
                'payment_date' => $_POST['payment_date'] ?? date('Y-m-d'),
                'reference_number' => $_POST['reference_number'] ?? '',
                'notes' => $_POST['notes'] ?? '',
                'status' => $_POST['status'] ?? 'completed',
                'received_by' => $this->getCurrentUserId()
            ];

            // Validate required fields
            if (empty($data['invoice_id'])) {
                $this->sendErrorResponse('Invoice ID is required', 400);
                return;
            }

            if (empty($data['payment_amount']) || $data['payment_amount'] <= 0) {
                $this->sendErrorResponse('Payment amount must be greater than zero', 400);
                return;
            }

            $paymentId = $this->paymentModel->createPayment($data);

            if ($paymentId) {
                $payment = $this->paymentModel->getPaymentWithDetails($paymentId);
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Payment created successfully',
                    'data' => $payment
                ], 201);
            } else {
                $this->sendErrorResponse('Failed to create payment', 500);
            }
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update payment
     */
    /**
     * Update payment
     */
    public function update(int $id): void
    {
        try {
            $this->requireAuth();
            $this->validateRequest(['PUT', 'POST']);

            $payment = $this->paymentModel->find($id);

            if (!$payment) {
                $this->sendErrorResponse('Payment not found', 404);
                return;
            }

            $data = [
                'payment_amount' => $_POST['payment_amount'] ?? $payment['payment_amount'],
                'payment_method' => $_POST['payment_method'] ?? $payment['payment_method'],
                'payment_date' => $_POST['payment_date'] ?? $payment['payment_date'],
                'reference_number' => $_POST['reference_number'] ?? $payment['reference_number'],
                'notes' => $_POST['notes'] ?? $payment['notes'],
                'status' => $_POST['status'] ?? $payment['status']
            ];

            // Validate payment amount
            if (isset($_POST['payment_amount']) && (empty($_POST['payment_amount']) || $_POST['payment_amount'] <= 0)) {
                $this->sendErrorResponse('Payment amount must be greater than zero', 400);
                return;
            }

            $success = $this->paymentModel->updatePayment($id, $data);

            if ($success) {
                $updatedPayment = $this->paymentModel->getPaymentWithDetails($id);
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Payment updated successfully',
                    'data' => $updatedPayment
                ]);
            } else {
                $this->sendErrorResponse('Failed to update payment', 500);
            }
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Delete payment
     */
    /**
     * Delete payment
     */
    public function delete(int $id): void
    {
        try {
            $this->requireAuth();
            $this->validateRequest(['DELETE', 'POST']);

            $payment = $this->paymentModel->find($id);

            if (!$payment) {
                $this->sendErrorResponse('Payment not found', 404);
                return;
            }

            $success = $this->paymentModel->deletePayment($id);

            if ($success) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Payment deleted successfully'
                ]);
            } else {
                $this->sendErrorResponse('Failed to delete payment', 500);
            }
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get payments by invoice
     */
    /**
     * Get payments by invoice
     */
    public function getByInvoice(int $invoiceId): void
    {
        try {
            $this->requireAuth();

            $payments = $this->paymentModel->getPaymentsByInvoice($invoiceId);

            $this->sendResponse([
                'success' => true,
                'data' => $payments
            ]);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get payments by patient
     */
    /**
     * Get payments by patient
     */
    public function getByPatient(int $patientId): void
    {
        try {
            $this->requireAuth();

            $status = $_GET['status'] ?? null;
            $payments = $this->paymentModel->getPaymentsByPatient($patientId, $status);

            $this->sendResponse([
                'success' => true,
                'data' => $payments
            ]);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get payment statistics
     */
    public function statistics(): void
    {
        try {
            $this->requireAuth();

            $period = $_GET['period'] ?? 'month';
            $statistics = $this->paymentModel->getPaymentStatistics($period);

            $this->sendResponse([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get payments by method
     */
    /**
     * Get payments by method
     */
    public function getByMethod(string $method): void
    {
        try {
            $this->requireAuth();

            $period = $_GET['period'] ?? 'month';
            $payments = $this->paymentModel->getPaymentsByMethod($method, $period);

            $this->sendResponse([
                'success' => true,
                'data' => $payments
            ]);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update payment status
     */
    /**
     * Update payment status
     */
    public function updateStatus(int $id): void
    {
        try {
            $this->requireAuth();
            $this->validateRequest(['PUT', 'POST']);

            $status = $_POST['status'] ?? null;

            if (!$status) {
                $this->sendErrorResponse('Status is required', 400);
                return;
            }

            $success = $this->paymentModel->updatePaymentStatus($id, $status);

            if ($success) {
                $payment = $this->paymentModel->getPaymentWithDetails($id);
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Payment status updated successfully',
                    'data' => $payment
                ]);
            } else {
                $this->sendErrorResponse('Failed to update payment status', 500);
            }
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }
}
