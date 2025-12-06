<?php

/**
 * Nyalife HMS - Department Controller
 *
 * Controller for handling department-related operations.
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/DepartmentModel.php';
require_once __DIR__ . '/../../data/department_data.php';
require_once __DIR__ . '/../../core/SessionManager.php';

class DepartmentController extends WebController
{
    private readonly \DepartmentModel $departmentModel;

    public function __construct()
    {
        parent::__construct();
        $this->departmentModel = new DepartmentModel();
    }

    /**
     * Display departments list
     */
    public function index(): void
    {
        // Check if user has permission to view departments
        if (!$this->auth->hasRole('admin')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $departments = getAllDepartmentsWithStaffCount();
        $statistics = getDepartmentStatistics();

        $this->renderView('departments/index', [
            'departments' => $departments,
            'statistics' => $statistics,
            'pageTitle' => 'Departments'
        ]);
    }

    /**
     * Display department details
     */
    public function show($id): void
    {
        // Check if user has permission to view departments
        if (!$this->auth->hasRole('admin')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $department = $this->departmentModel->find($id);

        if (!$department) {
            $this->setFlashMessage('error', 'Department not found.');
            $this->redirect('departments');
            return;
        }

        $staff = $this->departmentModel->getStaffInDepartment($id);

        $this->renderView('departments/show', [
            'department' => $department,
            'staff' => $staff,
            'pageTitle' => 'Department Details'
        ]);
    }

    /**
     * Display create department form
     */
    public function create(): void
    {
        // Check if user has permission to create departments
        if (!$this->auth->hasRole('admin')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $this->renderView('departments/create', [
            'pageTitle' => 'Create Department'
        ]);
    }

    /**
     * Store new department
     */
    public function store(): void
    {
        // Check if user has permission to create departments
        if (!$this->auth->hasRole('admin')) {
            $this->redirect('error/unauthorized');
            return;
        }

        // Validate CSRF token - using simple validation for now
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== SessionManager::get('csrf_token')) {
            $this->setFlashMessage('error', 'Invalid request.');
            $this->redirect('departments/create');
            return;
        }

        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Validate required fields
        if (empty($data['name'])) {
            $this->setFlashMessage('error', 'Department name is required.');
            $this->redirect('departments/create');
            return;
        }

        // Check if department name already exists
        if (departmentNameExists($data['name'])) {
            $this->setFlashMessage('error', 'Department name already exists.');
            $this->redirect('departments/create');
            return;
        }

        $departmentId = createDepartment($data);

        if ($departmentId) {
            $this->setFlashMessage('success', 'Department created successfully.');
            $this->redirect('departments');
        } else {
            $this->setFlashMessage('error', 'Failed to create department.');
            $this->redirect('departments/create');
        }
    }

    /**
     * Display edit department form
     */
    public function edit($id): void
    {
        // Check if user has permission to edit departments
        if (!$this->auth->hasRole('admin')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $department = $this->departmentModel->find($id);

        if (!$department) {
            $this->setFlashMessage('error', 'Department not found.');
            $this->redirect('departments');
            return;
        }

        // Get staff members for the department head dropdown
        $staff = getAllStaff();

        $this->renderView('departments/edit', [
            'department' => $department,
            'staff' => $staff,
            'pageTitle' => 'Edit Department'
        ]);
    }

    /**
     * Update department
     */
    public function update($id): void
    {
        // Check if user has permission to edit departments
        if (!$this->auth->hasRole('admin')) {
            $this->redirect('error/unauthorized');
            return;
        }

        // Validate CSRF token - using simple validation for now
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== SessionManager::get('csrf_token')) {
            $this->setFlashMessage('error', 'Invalid request.');
            $this->redirect("departments/edit/{$id}");
            return;
        }

        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Validate required fields
        if (empty($data['name'])) {
            $this->setFlashMessage('error', 'Department name is required.');
            $this->redirect("departments/edit/{$id}");
            return;
        }

        // Check if department name already exists (excluding current department)
        if (departmentNameExists($data['name'], $id)) {
            $this->setFlashMessage('error', 'Department name already exists.');
            $this->redirect("departments/edit/{$id}");
            return;
        }

        $success = updateDepartment($id, $data);

        if ($success) {
            $this->setFlashMessage('success', 'Department updated successfully.');
            $this->redirect('departments');
        } else {
            $this->setFlashMessage('error', 'Failed to update department.');
            $this->redirect("departments/edit/{$id}");
        }
    }

    /**
     * Delete department
     */
    public function delete($id): void
    {
        // Check if user has permission to delete departments
        if (!$this->auth->hasRole('admin')) {
            $this->redirect('error/unauthorized');
            return;
        }

        // Validate CSRF token - using simple validation for now
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== SessionManager::get('csrf_token')) {
            $this->setFlashMessage('error', 'Invalid request.');
            $this->redirect('departments');
            return;
        }

        // Check if department has staff
        if ($this->departmentModel->hasStaff($id)) {
            $this->setFlashMessage('error', 'Cannot delete department with assigned staff.');
            $this->redirect('departments');
            return;
        }

        $success = deleteDepartment($id);

        if ($success) {
            $this->setFlashMessage('success', 'Department deleted successfully.');
        } else {
            $this->setFlashMessage('error', 'Failed to delete department.');
        }

        $this->redirect('departments');
    }

    /**
     * Search departments
     */
    public function search(): void
    {
        // Check if user has permission to view departments
        if (!$this->auth->hasRole('admin')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $searchTerm = $_GET['q'] ?? '';

        if (empty($searchTerm)) {
            $this->redirect('departments');
            return;
        }

        $departments = searchDepartments($searchTerm);

        $this->renderView('departments/search', [
            'departments' => $departments,
            'searchTerm' => $searchTerm,
            'pageTitle' => 'Search Departments'
        ]);
    }

    /**
     * Get departments for AJAX requests
     */
    public function getDepartments(): void
    {
        // Check if user has permission to view departments
        if (!$this->auth->hasRole('admin')) {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }

        $departments = getActiveDepartments();

        $this->jsonResponse([
            'success' => true,
            'departments' => $departments
        ]);
    }
}
