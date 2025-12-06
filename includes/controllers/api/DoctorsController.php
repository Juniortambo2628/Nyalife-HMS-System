<?php

/**
 * Nyalife HMS - Doctors API Controller
 *
 * API controller for doctor-related operations.
 */

require_once __DIR__ . '/../web/WebController.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/DoctorModel.php';

class DoctorsController extends WebController
{
    protected \UserModel $userModel;

    protected \DoctorModel $doctorModel;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->doctorModel = new DoctorModel();
    }

    /**
     * Get all available doctors
     */
    public function index(): void
    {
        try {
            $doctors = $this->userModel->getDoctors();

            $this->jsonResponse([
                'success' => true,
                'doctors' => $doctors
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to load doctors: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctor by ID
     */
    /**
     * Get doctor by ID
     */
    public function show(int $id): void
    {
        try {
            $doctor = $this->userModel->getUserById($id);

            if (!$doctor || $doctor['role_id'] != 2) { // Assuming role_id 2 is for doctors
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Doctor not found'
                ], 404);
                return;
            }

            $this->jsonResponse([
                'success' => true,
                'doctor' => $doctor
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to load doctor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctors by specialization
     */
    /**
     * Get doctors by specialization
     */
    public function bySpecialization(string $specialization): void
    {
        try {
            $doctors = $this->doctorModel->getDoctorsBySpecialization($specialization);

            $this->jsonResponse([
                'success' => true,
                'doctors' => $doctors
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to load doctors: ' . $e->getMessage()
            ], 500);
        }
    }
}
