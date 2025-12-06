<?php

/**
 * Nyalife HMS - Lab Test Web Controller
 *
 * Handles all lab test type and parameter related web requests
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/LabTestModel.php';
require_once __DIR__ . '/../../models/PatientModel.php';
require_once __DIR__ . '/../../models/LabAttachmentModel.php';
require_once __DIR__ . '/../../helpers/AuditLogger.php';

class LabTestController extends WebController
{
    private readonly \LabTestModel $labTestModel;

    private readonly \AuditLogger $auditLogger;

    /** @var bool */
    protected $requiresLogin = true;

    /** @var array */
    protected $allowedRoles = ['admin', 'lab_technician'];

    public function __construct()
    {
        parent::__construct();
        $this->labTestModel = new LabTestModel();
        $this->pageTitle = 'Lab Tests';

        // Initialize AuditLogger with database connection from the model
        $this->auditLogger = new AuditLogger($this->labTestModel->getDbConnection());
    }

    /**
     * List all lab test types
     */
    public function index(): void
    {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 20;
            $search = $_GET['search'] ?? '';

            $tests = $this->labTestModel->getAllTestTypes($search, $page, $perPage);
            $total = $this->labTestModel->countTestTypes($search);

            $this->renderView('lab/tests/index', [
                'tests' => $tests,
                'search' => $search,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'url' => "/lab/tests?search=" . urlencode((string) $search) . "&page="
                ]
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading lab tests', $e);
        }
    }

    /**
     * Show create lab test type form
     */
    public function create(): void
    {
        try {
            $this->renderView('lab/tests/form', [
                'test' => null,
                'parameters' => [],
                'allParameters' => $this->labTestModel->getAllParameters()
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading lab test form', $e);
        }
    }

    /**
     * Store a new lab test type
     */
    public function store(): void
    {
        try {
            // Validate input
            $required = ['test_name', 'description', 'category', 'price'];
            $missing = [];
            $data = [];

            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    $missing[] = $field;
                } else {
                    $data[$field] = $_POST[$field];
                }
            }

            if ($missing !== []) {
                $this->redirectWithError('Missing required fields: ' . implode(', ', $missing), '/lab/tests/create');
                return;
            }

            // Format data
            $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
            $data['created_by'] = $this->userId;
            $data['created_at'] = date('Y-m-d H:i:s');

            // Handle file upload if present
            if (isset($_FILES['instructions_file']) && $_FILES['instructions_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../../uploads/lab_tests/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = uniqid() . '_' . basename((string) $_FILES['instructions_file']['name']);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['instructions_file']['tmp_name'], $targetPath)) {
                    $data['instructions_file'] = $fileName;
                }
            }

            // Start transaction
            $this->db->begin_transaction();

            try {
                // Insert test type
                $testId = $this->labTestModel->createTestType($data);

                if (!$testId) {
                    throw new Exception('Failed to create lab test type');
                }

                // Handle parameters
                $parameterIds = isset($_POST['parameter_ids']) ? (array)$_POST['parameter_ids'] : [];
                $parameterNames = isset($_POST['parameter_names']) ? (array)$_POST['parameter_names'] : [];
                $parameterUnits = isset($_POST['parameter_units']) ? (array)$_POST['parameter_units'] : [];
                $parameterRanges = isset($_POST['parameter_ranges']) ? (array)$_POST['parameter_ranges'] : [];
                $parameterSequences = isset($_POST['parameter_sequences']) ? (array)$_POST['parameter_sequences'] : [];

                foreach ($parameterIds as $index => $paramId) {
                    if (empty($parameterNames[$index])) {
                        continue;
                    }

                    $paramData = [
                        'test_id' => $testId,
                        'parameter_name' => $parameterNames[$index],
                        'unit' => $parameterUnits[$index] ?? '',
                        'reference_range' => $parameterRanges[$index] ?? '',
                        'sequence' => (int)($parameterSequences[$index] ?? $index + 1),
                        'is_active' => 1,
                        'created_by' => $this->userId,
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    $paramId = $this->labTestModel->createParameter($paramData);
                    if (!$paramId) {
                        throw new Exception('Failed to create parameter: ' . $paramData['parameter_name']);
                    }
                }

                // Commit transaction
                $this->db->commit();

                // Log the action
                $this->logAction('lab_test_created', [
                    'test_id' => $testId,
                    'test_name' => $data['test_name']
                ]);

                $this->redirectWithSuccess('Lab test type created successfully', '/lab/tests');
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
        } catch (Exception $e) {
            $this->handleError('Error creating lab test type', $e);

            // Preserve form input
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/lab/tests/create');
        }
    }

    /**
     * Show edit lab test type form
     */
    public function edit($testId): void
    {
        try {
            $test = $this->labTestModel->getTestTypeById($testId);

            if (!$test) {
                $this->redirectWithError('Lab test type not found', '/lab/tests');
                return;
            }

            $parameters = $this->labTestModel->getParametersByTestId($testId);
            $allParameters = $this->labTestModel->getAllParameters();

            $this->renderView('lab/tests/form', [
                'test' => $test,
                'parameters' => $parameters,
                'allParameters' => $allParameters,
                'edit' => true
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading lab test form', $e);
        }
    }

    /**
     * View a lab test type
     */
    public function view($testId): void
    {
        try {
            $test = $this->labTestModel->getTestTypeById($testId);

            if (!$test) {
                $this->redirectWithError('Lab test type not found', '/lab/tests');
                return;
            }

            $parameters = $this->labTestModel->getParametersByTestId($testId);

            $this->renderView('lab/tests/view', [
                'test' => $test,
                'parameters' => $parameters
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading lab test type', $e);
        }
    }

    /**
     * Update a lab test type
     */
    public function update($testId): void
    {
        try {
            $test = $this->labTestModel->getTestTypeById($testId);

            if (!$test) {
                $this->redirectWithError('Lab test type not found', '/lab/tests');
                return;
            }

            // Validate input
            $required = ['test_name', 'description', 'category', 'price'];
            $missing = [];
            $data = [];

            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    $missing[] = $field;
                } else {
                    $data[$field] = $_POST[$field];
                }
            }

            if ($missing !== []) {
                $this->redirectWithError('Missing required fields: ' . implode(', ', $missing), "/lab/tests/edit/$testId");
                return;
            }

            // Format data
            $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
            $data['updated_by'] = $this->userId;
            $data['updated_at'] = date('Y-m-d H:i:s');

            // Define upload directory
            $uploadDir = __DIR__ . '/../../../uploads/lab_tests/';

            // Handle file upload if present
            if (isset($_FILES['instructions_file']) && $_FILES['instructions_file']['error'] === UPLOAD_ERR_OK) {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Delete old file if exists
                if (!empty($test['instructions_file'])) {
                    $oldFile = $uploadDir . $test['instructions_file'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $fileName = uniqid() . '_' . basename((string) $_FILES['instructions_file']['name']);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['instructions_file']['tmp_name'], $targetPath)) {
                    $data['instructions_file'] = $fileName;
                }
            } elseif (isset($_POST['remove_instructions_file']) && $_POST['remove_instructions_file'] == '1') {
                // Remove existing file if requested
                if (!empty($test['instructions_file'])) {
                    $oldFile = $uploadDir . $test['instructions_file'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                    $data['instructions_file'] = null;
                }
            }

            // Start transaction
            $this->db->begin_transaction();

            try {
                // Update test type
                $success = $this->labTestModel->updateTestType($testId, $data);

                if (!$success) {
                    throw new Exception('Failed to update lab test type');
                }

                // Handle parameters
                $existingParamIds = [];
                $parameterIds = isset($_POST['parameter_ids']) ? (array)$_POST['parameter_ids'] : [];
                $parameterNames = isset($_POST['parameter_names']) ? (array)$_POST['parameter_names'] : [];
                $parameterUnits = isset($_POST['parameter_units']) ? (array)$_POST['parameter_units'] : [];
                $parameterRanges = isset($_POST['parameter_ranges']) ? (array)$_POST['parameter_ranges'] : [];
                $parameterSequences = isset($_POST['parameter_sequences']) ? (array)$_POST['parameter_sequences'] : [];

                // Get existing parameters to track deletions
                $existingParams = $this->labTestModel->getParametersByTestId($testId);
                $existingParamIds = array_column($existingParams, 'parameter_id');
                $submittedParamIds = [];

                // Update or create parameters
                foreach ($parameterIds as $index => $paramId) {
                    if (empty($parameterNames[$index])) {
                        continue;
                    }

                    $paramData = [
                        'parameter_name' => $parameterNames[$index],
                        'unit' => $parameterUnits[$index] ?? '',
                        'reference_range' => $parameterRanges[$index] ?? '',
                        'sequence' => (int)($parameterSequences[$index] ?? $index + 1),
                        'is_active' => 1,
                        'updated_by' => $this->userId,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if (!empty($paramId) && in_array($paramId, $existingParamIds)) {
                        // Update existing parameter
                        $success = $this->labTestModel->updateParameter($paramId, $paramData);
                        if (!$success) {
                            throw new Exception('Failed to update parameter: ' . $paramData['parameter_name']);
                        }
                        $submittedParamIds[] = $paramId;
                    } else {
                        // Create new parameter
                        $paramData['test_id'] = $testId;
                        $paramData['created_by'] = $this->userId;
                        $paramData['created_at'] = date('Y-m-d H:i:s');

                        $newParamId = $this->labTestModel->createParameter($paramData);
                        if (!$newParamId) {
                            throw new Exception('Failed to create parameter: ' . $paramData['parameter_name']);
                        }
                        $submittedParamIds[] = $newParamId;
                    }
                }

                // Delete parameters that were removed
                $paramsToDelete = array_diff($existingParamIds, $submittedParamIds);
                foreach ($paramsToDelete as $paramId) {
                    $this->labTestModel->deleteParameter($paramId);
                }

                // Commit transaction
                $this->db->commit();

                // Log the action
                $this->logAction('lab_test_updated', [
                    'test_id' => $testId,
                    'test_name' => $data['test_name']
                ]);

                $this->redirectWithSuccess('Lab test type updated successfully', '/lab/tests');
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
        } catch (Exception $e) {
            $this->handleError('Error updating lab test type', $e);
            $this->redirect("/lab/tests/edit/$testId");
        }
    }

    /**
     * Delete a lab test type
     */
    public function delete($testId): void
    {
        try {
            $test = $this->labTestModel->getTestTypeById($testId);

            if (!$test) {
                $this->jsonResponse(['success' => false, 'message' => 'Lab test type not found'], 404);
                return;
            }

            // Check if test type is in use
            $inUse = $this->labTestModel->isTestTypeInUse($testId);
            if ($inUse) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Cannot delete this test type as it is already in use. You can deactivate it instead.'
                ], 400);
                return;
            }

            // Start transaction
            $this->db->begin_transaction();

            try {
                // Delete parameters first
                $this->labTestModel->deleteParametersByTestId($testId);

                // Delete test type
                $success = $this->labTestModel->deleteTestType($testId);

                if (!$success) {
                    throw new Exception('Failed to delete lab test type');
                }

                // Delete instructions file if exists
                if (!empty($test['instructions_file'])) {
                    $filePath = __DIR__ . '/../../../uploads/lab_tests/' . $test['instructions_file'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                // Commit transaction
                $this->db->commit();

                // Log the action
                $this->logAction('lab_test_deleted', [
                    'test_id' => $testId,
                    'test_name' => $test['test_name']
                ]);

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Lab test type deleted successfully',
                    'redirect' => '/lab/tests'
                ]);
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error deleting lab test type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle test type active status
     */
    public function toggleStatus($testId): void
    {
        try {
            $test = $this->labTestModel->getTestTypeById($testId);

            if (!$test) {
                $this->jsonResponse(['success' => false, 'message' => 'Lab test type not found'], 404);
                return;
            }

            $newStatus = $test['is_active'] ? 0 : 1;
            $success = $this->labTestModel->updateTestType($testId, [
                'is_active' => $newStatus,
                'updated_by' => $this->userId,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($success) {
                $action = $newStatus !== 0 ? 'activated' : 'deactivated';

                // Log the action
                $this->logAction('lab_test_status_changed', [
                    'test_id' => $testId,
                    'test_name' => $test['test_name'],
                    'status' => $newStatus !== 0 ? 'active' : 'inactive'
                ]);

                $this->jsonResponse([
                    'success' => true,
                    'message' => "Lab test type $action successfully",
                    'is_active' => $newStatus
                ]);
            } else {
                throw new Exception('Failed to update test type status');
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error updating test type status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export test types to CSV
     */
    public function export(): void
    {
        try {
            $tests = $this->labTestModel->getAllTestTypes('', 1, 1000); // Get all tests

            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="lab_test_types_' . date('Y-m-d') . '.csv"');

            $output = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($output, [
                'Test Name',
                'Category',
                'Description',
                'Price',
                'Turnaround Time',
                'Status',
                'Created At',
                'Updated At'
            ]);

            // Add data rows
            foreach ($tests as $test) {
                fputcsv($output, [
                    $test['test_name'],
                    $test['category'],
                    $test['description'],
                    $test['price'],
                    $test['turnaround_time'],
                    $test['is_active'] ? 'Active' : 'Inactive',
                    $test['created_at'],
                    $test['updated_at']
                ]);
            }

            fclose($output);
            exit;
        } catch (Exception $e) {
            $this->handleError('Error exporting lab test types', $e);
            $this->redirect('/lab/tests');
        }
    }

    /**
     * Show lab test sample registration form
     */
    public function registerSample(): void
    {
        try {
            // Get available test types for selection
            $testTypes = $this->labTestModel->getActiveTestTypes();
            // Get patients for selection
            $patientModel = new PatientModel();
            $patients = $patientModel->getAllPatients();

            $this->renderView('lab/tests/register-sample', [
                'testTypes' => $testTypes,
                'patients' => $patients
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading sample registration form', $e);
        }
    }

    /**
     * Store a registered lab sample
     */
    public function storeRegisteredSample(): void
    {
        try {
            // Debug: Log the POST data
            error_log("POST data received: " . print_r($_POST, true));

            // Validate input
            $required = ['patient_id', 'test_type_id', 'sample_type', 'collected_date'];
            $missing = [];
            $data = [];

            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    $missing[] = $field;
                } else {
                    $data[$field] = $_POST[$field];
                }
            }

            if ($missing !== []) {
                error_log("Missing required fields: " . implode(', ', $missing));
                $this->redirectWithError('Missing required fields: ' . implode(', ', $missing), '/lab-tests/register-sample');
                return;
            }

            // Add additional data
            $data['status'] = 'registered';
            $data['sample_id'] = $this->generateSampleId();
            $data['collected_by'] = $this->userId;
            $data['collected_at'] = date('Y-m-d H:i:s');
            $data['notes'] = $_POST['notes'] ?? '';
            $data['urgent'] = isset($_POST['urgent']) ? 1 : 0;

            error_log("Data to be inserted: " . print_r($data, true));

            // Insert sample
            $sampleId = $this->labTestModel->registerSample($data);

            if (!$sampleId) {
                error_log("Failed to register sample - registerSample returned false");
                throw new Exception('Failed to register sample');
            }

            error_log("Sample registered successfully with ID: " . $sampleId);

            // Log the action
            $this->logAction('lab_sample_registered', [
                'sample_id' => $data['sample_id'],
                'patient_id' => $data['patient_id'],
                'test_type_id' => $data['test_type_id']
            ]);

            $this->redirectWithSuccess('Sample registered successfully', '/lab-tests/manage');
        } catch (Exception $e) {
            error_log("Error in storeRegisteredSample: " . $e->getMessage());
            $this->handleError('Error registering sample', $e);

            // Preserve form input
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/lab-tests/register-sample');
        }
    }

    /**
     * Get pending tests for AJAX requests
     */
    public function getPendingTestsAjax(): void
    {
        try {
            $pendingTests = $this->labTestModel->getPendingTests();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $pendingTests
            ]);
        } catch (Exception) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error loading pending tests'
            ]);
        }
    }

    /**
     * Show pending lab tests
     */
    public function pending(): void
    {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 20;
            $search = $_GET['search'] ?? '';

            $samples = $this->labTestModel->getSamplesByStatus('pending', $search, $page, $perPage);
            $total = $this->labTestModel->countSamplesByStatus('pending', $search);

            $this->renderView('lab/tests/manage', [
                'samples' => $samples,
                'status' => 'pending',
                'search' => $search,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'url' => "/lab-tests/pending?search=" . urlencode((string) $search) . "&page="
                ]
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading pending samples', $e);
        }
    }

    /**
     * View completed lab tests
     */
    public function completed(): void
    {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 20;
            $search = $_GET['search'] ?? '';

            $completedTests = $this->labTestModel->getCompletedTests($search, $page, $perPage);
            $total = $this->labTestModel->countCompletedTests($search);

            $this->renderView('lab/tests/completed', [
                'tests' => $completedTests,
                'search' => $search,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'url' => "/lab-tests/completed?search=" . urlencode((string) $search) . "&page="
                ]
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading completed tests', $e);
        }
    }

    /**
     * Manage lab tests and samples
     */
    public function manage(): void
    {
        try {
            $status = $_GET['status'] ?? 'registered';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 20;
            $search = $_GET['search'] ?? '';

            $samples = $this->labTestModel->getSamplesByStatus($status, $search, $page, $perPage);
            $total = $this->labTestModel->countSamplesByStatus($status, $search);

            $this->renderView('lab/tests/manage', [
                'samples' => $samples,
                'status' => $status,
                'search' => $search,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'url' => "/lab-tests/manage?status=" . urlencode((string) $status) . "&search=" . urlencode((string) $search) . "&page="
                ]
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading samples', $e);
        }
    }

    /**
     * Show the form for updating test results
     */
    public function showUpdateResult($id): void
    {
        try {
            // Check if $id is a numeric ID or sample_id
            if (is_numeric($id)) {
                // Get sample by numeric ID
                $sql = "SELECT s.*, 
                               t.test_name, t.description as test_description,
                               CONCAT(p_user.first_name, ' ', p_user.last_name) as patient_name,
                               p.patient_number
                        FROM lab_samples s
                        JOIN lab_test_types t ON s.test_type_id = t.test_type_id
                        JOIN patients p ON s.patient_id = p.patient_id
                        JOIN users p_user ON p.user_id = p_user.user_id
                        WHERE s.id = ?";

                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    $this->redirectWithError('Sample not found', '/lab-tests/manage');
                    return;
                }

                $sample = $result->fetch_assoc();
            } else {
                // Get sample by sample_id
                $sample = $this->labTestModel->getSampleById((string)$id);
            }

            if (!$sample) {
                $this->redirectWithError('Sample not found', '/lab-tests/manage');
                return;
            }

            // Check if allowed to update
            if ($sample['status'] === 'completed') {
                $this->redirectWithError('Cannot update results for a completed test', '/lab-tests/manage');
                return;
            }

            // Get test type and parameters
            $testType = $this->labTestModel->getTestTypeById($sample['test_type_id']);
            $parameters = $this->labTestModel->getParametersByTestId($sample['test_type_id']);

            // Get existing results
            $existingResults = $this->labTestModel->getSampleResults($id);
            $resultsMap = [];
            foreach ($existingResults as $result) {
                $resultsMap[$result['parameter_id']] = $result['result_value'];
            }

            // Get existing attachments
            $attachmentModel = new LabAttachmentModel();
            $attachments = $attachmentModel->getAttachmentsBySampleId(is_numeric($id) ? $id : $sample['id']);

            $this->renderView('lab/tests/update-result', [
                'sample' => $sample,
                'testType' => $testType,
                'parameters' => $parameters,
                'existingResults' => $resultsMap,
                'attachments' => $attachments
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading update result form', $e);
        }
    }

    /**
     * Update a lab test result
     */
    public function updateResult($id): void
    {
        try {
            // Get sample
            $sample = $this->labTestModel->getSampleById($id);

            if (!$sample) {
                $this->redirectWithError('Sample not found', '/lab-tests/manage');
                return;
            }

            // Check if allowed to update
            if ($sample['status'] === 'completed') {
                $this->redirectWithError('Cannot update results for a completed test', '/lab-tests/manage');
                return;
            }

            // Process results - check if results array exists
            $results = $_POST['results'] ?? [];
            $status = $_POST['status'] ?? 'in_progress';
            $notes = $_POST['notes'] ?? '';

            // Update sample status
            $this->labTestModel->updateSampleStatus($id, $status, $notes);

            // Handle file uploads
            if (!empty($_FILES['attachments']) && $_FILES['attachments']['error'][0] !== UPLOAD_ERR_NO_FILE) {
                $this->handleFileUploads($sample['id'], $sample['sample_id']);
            }

            // Store results if provided (for tests with parameters)
            if (!empty($results) && is_array($results)) {
                foreach ($results as $parameterId => $value) {
                    // Only save non-empty results (including zero values)
                    // Check if value is meaningful: not empty, is zero string, or is numeric zero
                    $hasValue = !empty($value);
                    if (!$hasValue) {
                        // Check for zero values
                        if ($value === '0') {
                            $hasValue = true;
                        } elseif (is_numeric($value) && (float)$value == 0.0) {
                            $hasValue = true;
                        }
                    }
                    if ($hasValue) {
                        $resultData = [
                            'sample_id' => $id,
                            'parameter_id' => $parameterId,
                            'result_value' => $value,
                            'recorded_by' => $this->userId,
                            'recorded_at' => date('Y-m-d H:i:s')
                        ];

                        $this->labTestModel->saveTestResult($resultData);
                    }
                }
            }

            // If marked as completed, set completion date
            if ($status === 'completed') {
                $this->labTestModel->completeSample($id, $this->userId);

                // Log the action
                $this->logAction('lab_test_completed', [
                    'sample_id' => $sample['sample_id'],
                    'patient_id' => $sample['patient_id'],
                    'test_type_id' => $sample['test_type_id']
                ]);
            } else {
                // Log the action
                $this->logAction('lab_test_updated', [
                    'sample_id' => $sample['sample_id'],
                    'patient_id' => $sample['patient_id'],
                    'test_type_id' => $sample['test_type_id'],
                    'status' => $status
                ]);
            }

            // Check if this is an AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                // Return JSON response
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Test results updated successfully',
                    'redirect' => '/lab-tests/manage'
                ]);
                exit;
            }

            $this->redirectWithSuccess('Test results updated successfully', '/lab-tests/manage');
        } catch (Exception $e) {
            // Check if this is an AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                // Return JSON error
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error updating test results: ' . $e->getMessage()
                ]);
                exit;
            }

            $this->handleError('Error updating test results', $e);
            $this->redirect('/lab-tests/manage');
        }
    }

    /**
     * View a lab sample
     */
    public function viewSample($id): void
    {
        try {
            $sample = $this->labTestModel->getSampleById($id);

            if (!$sample) {
                $this->redirectWithError('Sample not found', '/lab-tests/manage');
                return;
            }

            // Get patient information with user data
            $patientModel = new PatientModel();
            $patient = $patientModel->getWithUserData($sample['patient_id']);

            // Get test type information
            $testType = $this->labTestModel->getTestTypeById($sample['test_type_id']);

            $this->renderView('lab/samples/view', [
                'sample' => $sample,
                'patient' => $patient,
                'testType' => $testType
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading sample details', $e);
        }
    }

    /**
     * View sample results
     */
    public function sampleResults($id): void
    {
        try {
            $sample = $this->labTestModel->getSampleById($id);

            if (!$sample) {
                $this->redirectWithError('Sample not found', '/lab-tests/manage');
                return;
            }

            // Get test results
            $results = $this->labTestModel->getSampleResults($id);

            // Get test type and parameters
            $testType = $this->labTestModel->getTestTypeById($sample['test_type_id']);
            $parameters = $this->labTestModel->getParametersByTestId($sample['test_type_id']);

            $this->renderView('lab/samples/results', [
                'sample' => $sample,
                'results' => $results,
                'testType' => $testType,
                'parameters' => $parameters
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading sample results', $e);
        }
    }

    /**
     * Generate a unique sample ID
     */
    private function generateSampleId(): string
    {
        $prefix = 'LTS-';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 4));
        return $prefix . $date . '-' . $random;
    }

    /**
     * Log an action
     */
    private function logAction(string $action, array $details): void
    {
        // $this->auditLogger is always initialized in constructor, check userId
        if ($this->userId) {
            $this->auditLogger->log([
                'user_id' => $this->userId,
                'action' => $action,
                'entity_type' => 'lab_test',
                'entity_id' => $details['sample_id'] ?? null,
                'details' => $details
            ]);
        }
    }

    /**
     * Handle file uploads for lab test results
     */
    private function handleFileUploads($sampleDbId, string $sampleId): void
    {
        $attachmentModel = new LabAttachmentModel();
        $uploadDir = __DIR__ . '/../../../uploads/lab/';

        // Create upload directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Process each uploaded file
        $files = $_FILES['attachments'];
        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = $files['name'][$i];
                $fileSize = $files['size'][$i];
                $fileTmp = $files['tmp_name'][$i];

                // Get file extension and type
                $fileExt = strtolower(pathinfo((string) $fileName, PATHINFO_EXTENSION));
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];

                if (!in_array($fileExt, $allowedTypes)) {
                    continue; // Skip invalid file types
                }

                // Generate unique file name
                $uniqueFileName = $sampleId . '_' . time() . '_' . uniqid() . '.' . $fileExt;
                $filePath = $uploadDir . $uniqueFileName;

                // Move uploaded file
                if (move_uploaded_file($fileTmp, $filePath)) {
                    // Determine file type category
                    $fileTypeCategory = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'document';

                    // Save attachment to database
                    $attachmentData = [
                        'sample_id' => $sampleDbId,
                        'file_name' => $fileName,
                        'file_path' => '/uploads/lab/' . $uniqueFileName,
                        'file_type' => $fileTypeCategory,
                        'file_size' => $fileSize,
                        'uploaded_by' => $this->userId,
                        'description' => 'Lab test result attachment'
                    ];

                    $attachmentModel->createAttachment($attachmentData);
                }
            }
        }
    }
}
