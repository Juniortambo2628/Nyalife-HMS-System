<?php

/**
 * PharmacyController
 *
 * Handles all pharmacy-related operations including medicine management,
 * inventory tracking, and order processing.
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/MedicationModel.php';
require_once __DIR__ . '/../../data/pharmacy_data.php';

class PharmacyController extends WebController
{
    private readonly \MedicationModel $medicationModel;

    /**
     * Initialize controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Pharmacy Management';
        $this->medicationModel = new MedicationModel();

        // Only allow authorized users to access pharmacy features
        if (!$this->auth->isLoggedIn()) {
            $this->redirectToRoute('login');
            return;
        }

        // Check permissions based on role - only admin and pharmacist can access pharmacy
        $allowedRoles = ['admin', 'pharmacist'];
        if (!$this->auth->hasAnyRole($allowedRoles)) {
            $this->showError('You do not have permission to access this resource', 403);
            exit;
        }
    }

    /**
     * Display medicines list
     */
    public function medicines(): void
    {
        try {
            $search = $_GET['search'] ?? '';
            $category = $_GET['category'] ?? '';
            $status = $_GET['status'] ?? 'active';
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 20;

            // Get medications with filters
            $medications = $this->medicationModel->getAllMedications($search, $category, false, $page, $perPage);

            // Get total count for pagination
            $totalMedications = $this->medicationModel->getTotalMedications($search, $category, $status);
            $totalPages = ceil($totalMedications / $perPage);

            // Get categories for filter dropdown
            $categories = $this->medicationModel->getMedicationCategories();

            // Get statistics
            $statistics = [
                'total_medications' => $this->medicationModel->getTotalMedications('', '', 'all'),
                'active_medications' => $this->medicationModel->getTotalMedications('', '', 'active'),
                'low_stock' => $this->medicationModel->getLowStockCount(),
                'out_of_stock' => $this->medicationModel->getOutOfStockCount()
            ];

            $this->renderView('pharmacy/medicines/index', [
                'medications' => $medications,
                'categories' => $categories,
                'statistics' => $statistics,
                'filters' => [
                    'search' => $search,
                    'category' => $category,
                    'status' => $status
                ],
                'pagination' => [
                    'current' => $page,
                    'total' => $totalPages,
                    'perPage' => $perPage,
                    'totalItems' => $totalMedications
                ]
            ]);
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error loading medications: ' . $e->getMessage());
            $this->redirect('pharmacy/medicines');
        }
    }

    /**
     * Display create medicine form
     */
    public function createMedicine(): void
    {
        try {
            $categories = $this->medicationModel->getMedicationCategories();
            $forms = $this->medicationModel->getMedicationForms();
            $units = $this->medicationModel->getMedicationUnits();

            $this->renderView('pharmacy/medicines/create', [
                'categories' => $categories,
                'forms' => $forms,
                'units' => $units
            ]);
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error loading form: ' . $e->getMessage());
            $this->redirect('pharmacy/medicines');
        }
    }

    /**
     * Store new medicine
     */
    public function storeMedicine(): void
    {
        try {
            // Validate required fields
            $requiredFields = ['medication_name', 'form', 'strength', 'unit'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $this->setFlashMessage('error', ucfirst(str_replace('_', ' ', $field)) . ' is required.');
                    $this->redirect('pharmacy/medicines/create');
                    return;
                }
            }

            $medicationData = [
                'medication_name' => $_POST['medication_name'],
                'generic_name' => $_POST['generic_name'] ?? '',
                'medication_type' => $_POST['category'] ?? '',
                'form' => $_POST['form'],
                'strength' => $_POST['strength'],
                'unit' => $_POST['unit'],
                'manufacturer' => $_POST['manufacturer'] ?? '',
                'price' => floatval($_POST['price'] ?? 0),
                'description' => $_POST['description'] ?? '',
                'side_effects' => $_POST['side_effects'] ?? '',
                'contraindications' => $_POST['contraindications'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $medicationId = $this->medicationModel->createMedication($medicationData);

            if ($medicationId) {
                $this->setFlashMessage('success', 'Medication created successfully.');
                $this->redirect("pharmacy/medicines/show/$medicationId");
            } else {
                $this->setFlashMessage('error', 'Failed to create medication.');
                $this->redirect('pharmacy/medicines/create');
            }
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error creating medication: ' . $e->getMessage());
            $this->redirect('pharmacy/medicines/create');
        }
    }

    /**
     * Display medicine details
     */
    public function showMedicine($id): void
    {
        try {
            $medication = $this->medicationModel->getMedicationById($id);

            if (!$medication) {
                $this->setFlashMessage('error', 'Medication not found.');
                $this->redirect('pharmacy/medicines');
                return;
            }

            // Get stock information
            $stock = $this->medicationModel->getMedicationStock($id);

            $this->renderView('pharmacy/medicines/show', [
                'medication' => $medication,
                'stock' => $stock
            ]);
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error loading medication: ' . $e->getMessage());
            $this->redirect('pharmacy/medicines');
        }
    }

    /**
     * Display edit medicine form
     */
    public function editMedicine($id): void
    {
        try {
            $medication = $this->medicationModel->getMedicationById($id);

            if (!$medication) {
                $this->setFlashMessage('error', 'Medication not found.');
                $this->redirect('pharmacy/medicines');
                return;
            }

            $categories = $this->medicationModel->getMedicationCategories();
            $forms = $this->medicationModel->getMedicationForms();
            $units = $this->medicationModel->getMedicationUnits();

            $this->renderView('pharmacy/medicines/edit', [
                'medication' => $medication,
                'categories' => $categories,
                'forms' => $forms,
                'units' => $units
            ]);
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error loading medication: ' . $e->getMessage());
            $this->redirect('pharmacy/medicines');
        }
    }

    /**
     * Update medicine
     */
    public function updateMedicine($id): void
    {
        try {
            $medication = $this->medicationModel->getMedicationById($id);

            if (!$medication) {
                $this->setFlashMessage('error', 'Medication not found.');
                $this->redirect('pharmacy/medicines');
                return;
            }

            // Validate required fields
            $requiredFields = ['medication_name', 'form', 'strength', 'unit'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $this->setFlashMessage('error', ucfirst(str_replace('_', ' ', $field)) . ' is required.');
                    $this->redirect("pharmacy/medicines/edit/$id");
                    return;
                }
            }

            $medicationData = [
                'medication_name' => $_POST['medication_name'],
                'generic_name' => $_POST['generic_name'] ?? '',
                'medication_type' => $_POST['category'] ?? '',
                'form' => $_POST['form'],
                'strength' => $_POST['strength'],
                'unit' => $_POST['unit'],
                'manufacturer' => $_POST['manufacturer'] ?? '',
                'price' => floatval($_POST['price'] ?? 0),
                'description' => $_POST['description'] ?? '',
                'side_effects' => $_POST['side_effects'] ?? '',
                'contraindications' => $_POST['contraindications'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $success = $this->medicationModel->updateMedication($id, $medicationData);

            if ($success) {
                $this->setFlashMessage('success', 'Medication updated successfully.');
                $this->redirect("pharmacy/medicines/show/$id");
            } else {
                $this->setFlashMessage('error', 'Failed to update medication.');
                $this->redirect("pharmacy/medicines/edit/$id");
            }
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error updating medication: ' . $e->getMessage());
            $this->redirect("pharmacy/medicines/edit/$id");
        }
    }

    /**
     * Toggle medication status
     */
    public function toggleMedicineStatus($id): void
    {
        try {
            $medication = $this->medicationModel->getMedicationById($id);

            if (!$medication) {
                $this->setFlashMessage('error', 'Medication not found.');
                $this->redirect('pharmacy/medicines');
                return;
            }

            $newStatus = $medication['is_active'] ? 0 : 1;
            $success = $this->medicationModel->updateMedicationStatus($id, $newStatus);

            if ($success) {
                $statusText = $newStatus !== 0 ? 'activated' : 'deactivated';
                $this->setFlashMessage('success', "Medication $statusText successfully.");
            } else {
                $this->setFlashMessage('error', 'Failed to update medication status.');
            }

            $this->redirect('pharmacy/medicines');
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Error updating medication status: ' . $e->getMessage());
            $this->redirect('pharmacy/medicines');
        }
    }

    /**
     * Display inventory management
     */
    public function inventory(): void
    {
        $this->renderUnderDevelopment('Inventory Management');
    }

    /**
     * Display orders management
     */
    public function orders(): void
    {
        $this->renderUnderDevelopment('Pharmacy Orders');
    }

    /**
     * Render a "module under development" page
     *
     * @param string $moduleName The name of the module
     */
    private function renderUnderDevelopment(string $moduleName): void
    {
        $this->renderView('error', [
            'pageTitle' => $moduleName,
            'errorMessage' => $moduleName . ' module is currently under development.',
            'errorCode' => 'UNDER_DEVELOPMENT',
            'showBackLink' => true,
            'backLink' => $this->getBaseUrl(),
            'backLinkText' => 'Return to Dashboard'
        ]);
    }
}
