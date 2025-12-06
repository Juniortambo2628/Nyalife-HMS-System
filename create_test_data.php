<?php
/**
 * Create test data for patient ID 3
 */

require_once 'config/database.php';

try {
    // Use local development credentials
    $pdo = new PDO("mysql:host=localhost;dbname=nyalifew_hms_prod", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating test data for patient ID 3...\n";

    // First, let's add patient ID 3 if it doesn't exist
    $checkPatient = $pdo->query("SELECT COUNT(*) as count FROM patients WHERE patient_id = 3")->fetch(PDO::FETCH_ASSOC);
    if ($checkPatient['count'] == 0) {
        echo "Creating patient ID 3...\n";

        // Insert patient
        $pdo->query("INSERT INTO patients (patient_id, user_id, patient_number, created_at, updated_at) VALUES (3, 8, 'NYA-PAT-003', NOW(), NOW())");

        // Insert corresponding user
        $pdo->query("INSERT INTO users (user_id, first_name, last_name, email, password, role, phone, created_at, updated_at) VALUES (8, 'Wanja', 'Kairu', 'wanja@example.com', 'password123', 'patient', '0717215425', NOW(), NOW())");
    }

    // Add some vital signs
    $checkVitals = $pdo->query("SELECT COUNT(*) as count FROM vital_signs WHERE patient_id = 3")->fetch(PDO::FETCH_ASSOC);
    if ($checkVitals['count'] == 0) {
        echo "Adding vital signs for patient 3...\n";
        $pdo->query("INSERT INTO vital_signs (patient_id, blood_pressure, heart_rate, respiratory_rate, temperature, oxygen_saturation, recorded_by, measured_at, created_at) VALUES (3, '120/80', 72, 16, 36.5, 98, 1, NOW(), NOW())");
    } else {
        echo "Updating existing vital signs for patient 3...\n";
        $pdo->query("UPDATE vital_signs SET respiratory_rate = 16, oxygen_saturation = 98 WHERE patient_id = 3");
    }

    // Add a consultation
    $checkConsultations = $pdo->query("SELECT COUNT(*) as count FROM consultations WHERE patient_id = 3")->fetch(PDO::FETCH_ASSOC);
    if ($checkConsultations['count'] == 0) {
        echo "Adding consultation for patient 3...\n";
        $pdo->query("INSERT INTO consultations (patient_id, doctor_id, consultation_date, chief_complaint, consultation_status, created_by, created_at) VALUES (3, 1, NOW(), 'Routine checkup', 'completed', 1, NOW())");

        // Get the consultation ID
        $consultationId = $pdo->lastInsertId();
        echo "Consultation ID: $consultationId\n";
    } else {
        // Get existing consultation ID
        $consultationResult = $pdo->query("SELECT consultation_id FROM consultations WHERE patient_id = 3 ORDER BY consultation_id DESC LIMIT 1");
        $consultationId = $consultationResult->fetch(PDO::FETCH_ASSOC)['consultation_id'];
    }

    // Add a prescription
    $checkPrescriptions = $pdo->query("SELECT COUNT(*) as count FROM prescriptions WHERE patient_id = 3")->fetch(PDO::FETCH_ASSOC);
    if ($checkPrescriptions['count'] == 0) {
        echo "Adding prescription for patient 3...\n";
        $pdo->query("INSERT INTO prescriptions (patient_id, consultation_id, prescribed_by, prescription_date, status, created_at) VALUES (3, $consultationId, 1, NOW(), 'pending', NOW())");
    }

    // Add lab test request and completed test
    echo "Adding lab test data for patient 3...\n";

    // Check if lab test types exist
    $testTypeResult = $pdo->query("SELECT test_type_id FROM lab_test_types LIMIT 1");
    $testType = $testTypeResult->fetch(PDO::FETCH_ASSOC);

    if ($testType) {
        $testTypeId = $testType['test_type_id'];

        // Create lab test request
        $pdo->query("INSERT INTO lab_test_requests (patient_id, requested_by, request_date, status, created_at)
                     VALUES (3, 1, NOW(), 'completed', NOW())");
        $requestId = $pdo->lastInsertId();

        // Create lab test item (completed)
        $pdo->query("INSERT INTO lab_test_items (request_id, test_type_id, status, performed_by, performed_at, created_at)
                     VALUES ($requestId, $testTypeId, 'completed', 1, NOW(), NOW())");
    }

    // Add prescription items to fix the "0 medications" issue
    $prescriptionResult = $pdo->query("SELECT prescription_id FROM prescriptions WHERE patient_id = 3 LIMIT 1");
    $prescription = $prescriptionResult->fetch(PDO::FETCH_ASSOC);

    if ($prescription) {
        $prescriptionId = $prescription['prescription_id'];

        // Check if prescription items exist
        $itemCheck = $pdo->query("SELECT COUNT(*) as count FROM prescription_items WHERE prescription_id = $prescriptionId");
        $itemCount = $itemCheck->fetch(PDO::FETCH_ASSOC)['count'];

        if ($itemCount == 0) {
            // Add a sample medication
            $medicationResult = $pdo->query("SELECT medication_id FROM medications LIMIT 1");
            $medication = $medicationResult->fetch(PDO::FETCH_ASSOC);

            if ($medication) {
                $medicationId = $medication['medication_id'];
                $pdo->query("INSERT INTO prescription_items (prescription_id, medication_id, dosage, frequency, duration, instructions, created_at)
                             VALUES ($prescriptionId, $medicationId, '10mg', 'Once daily', '7 days', 'Take with food', NOW())");
            }
        }
    }

    echo "Test data created successfully!\n";
    echo "Patient ID 3 now has sample data in all tabs.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
