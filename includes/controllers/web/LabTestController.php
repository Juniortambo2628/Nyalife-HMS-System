<?php
/**
 * Nyalife HMS - Lab Test Web Controller
 * 
 * Handles all lab test type and parameter related web requests
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/LabTestModel.php';

class LabTestController extends WebController {
    private $labTestModel;
    
    public function __construct() {
        parent::__construct();
        $this->labTestModel = new LabTestModel();
        
        // Check if user is logged in and has appropriate permissions
        $this->requireLogin();
        
        // Only admins and lab technicians can access these functions
        if ($this->userRole !== 'admin' && $this->userRole !== 'lab_technician') {
            $this->redirectWithError('You do not have permission to access this section', '/dashboard');
            exit;
        }
    }
    
    /**
     * List all lab test types
     */
    public function index() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 20;
            $search = $_GET['search'] ?? '';
            
            $tests = $this->labTestModel->getAllTestTypes($search, $page, $perPage);
            $total = $this->labTestModel->countTestTypes($search);
            
            $this->render('lab/tests/index', [
                'tests' => $tests,
                'search' => $search,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'url' => "/lab/tests?search=" . urlencode($search) . "&page="
                ]
            ]);
            
        } catch (Exception $e) {
            $this->handleError('Error loading lab tests', $e);
        }
    }
    
    /**
     * Show create lab test type form
     */
    public function create() {
        try {
            $this->render('lab/tests/form', [
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
    public function store() {
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
            
            if (!empty($missing)) {
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
                
                $fileName = uniqid() . '_' . basename($_FILES['instructions_file']['name']);
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
                    if (empty($parameterNames[$index])) continue;
                    
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
    public function edit($testId) {
        try {
            $test = $this->labTestModel->getTestTypeById($testId);
            
            if (!$test) {
                $this->redirectWithError('Lab test type not found', '/lab/tests');
                return;
            }
            
            $parameters = $this->labTestModel->getParametersByTestId($testId);
            $allParameters = $this->labTestModel->getAllParameters();
            
            $this->render('lab/tests/form', [
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
     * Update a lab test type
     */
    public function update($testId) {
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
            
            if (!empty($missing)) {
                $this->redirectWithError('Missing required fields: ' . implode(', ', $missing), "/lab/tests/edit/$testId");
                return;
            }
            
            // Format data
            $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
            $data['updated_by'] = $this->userId;
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Handle file upload if present
            if (isset($_FILES['instructions_file']) && $_FILES['instructions_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../../uploads/lab_tests/';
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
                
                $fileName = uniqid() . '_' . basename($_FILES['instructions_file']['name']);
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
                    if (empty($parameterNames[$index])) continue;
                    
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
                if (!empty($paramsToDelete)) {
                    foreach ($paramsToDelete as $paramId) {
                        $this->labTestModel->deleteParameter($paramId);
                    }
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
    public function delete($testId) {
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
    public function toggleStatus($testId) {
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
                $action = $newStatus ? 'activated' : 'deactivated';
                
                // Log the action
                $this->logAction('lab_test_status_changed', [
                    'test_id' => $testId,
                    'test_name' => $test['test_name'],
                    'status' => $newStatus ? 'active' : 'inactive'
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
    public function export() {
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
}
