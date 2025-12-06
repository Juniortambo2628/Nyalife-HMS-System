<?php
/**
 * Quick verification script to assert new consultation columns accept data.
 * Usage: php scripts/verify_consultation_columns.php
 */

require_once __DIR__ . '/../config/database.php';

$cleanup = in_array('--cleanup', $argv ?? []);

try {
    // Try connectDB first
    try {
        $conn = connectDB();
    } catch (Throwable $e) {
        $conn = null;
    }

    if (!($conn instanceof mysqli)) {
        // Fallback to local root connection
        $fallbackHost = 'localhost';
        $fallbackUser = 'root';
        $fallbackPass = '';
        $fallbackDb = 'nyalifew_hms_prod';
        $conn = new mysqli($fallbackHost, $fallbackUser, $fallbackPass, $fallbackDb);
        if ($conn->connect_error) {
            throw new Exception('Fallback DB connection failed: ' . $conn->connect_error);
        }
    }

    // Insert a test consultation row (minimal required fields) then update new fields
    echo "Creating test consultation...\n";

    $sqlInsert = "INSERT INTO consultations (patient_id, doctor_id, consultation_date, chief_complaint, created_by) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sqlInsert);
    $now = date('Y-m-d H:i:s');
    // Use existing patient_id and doctor_id values available on local DB; fallback to 3 and 12
    $patientId = 3;
    $doctorId = 12;
    $createdBy = 12;
    $chief = 'Verification Test';

    $stmt->bind_param('iissi', $patientId, $doctorId, $now, $chief, $createdBy);
    if (!$stmt->execute()) {
        throw new Exception('Insert failed: ' . $stmt->error);
    }

    $consultationId = $conn->insert_id;
    $stmt->close();

    echo "Inserted consultation_id={$consultationId}\n";

    // Update new columns
    $updateSql = "UPDATE consultations SET diagnosis_confidence = ?, differential_diagnosis = ?, diagnostic_plan = ?, general_examination = ?, systems_examination = ?, clinical_summary = ?, parity = ?, current_pregnancy = ?, past_obstetric = ?, surgical_history = ? WHERE consultation_id = ?";
    $stmt = $conn->prepare($updateSql);
    $diagConf = 'medium';
    $diff = 'Test diff';
    $plan = 'Test plan';
    $genExam = 'General exam text';
    $sysExam = 'Systems exam text';
    $summary = 'Clinical summary text';
    $parity = 2;
    $currPreg = 'No';
    $pastObs = 'G1P1 2019';
    $surg = 'Appendectomy 2018';

    $stmt->bind_param('ssssssisssi', $diagConf, $diff, $plan, $genExam, $sysExam, $summary, $parity, $currPreg, $pastObs, $surg, $consultationId);
    if (!$stmt->execute()) {
        throw new Exception('Update failed: ' . $stmt->error);
    }
    $stmt->close();

    echo "Updated new fields for consultation_id={$consultationId}\n";

    // Read back the row
    $sql = "SELECT diagnosis_confidence, differential_diagnosis, diagnostic_plan, general_examination, systems_examination, clinical_summary, parity, current_pregnancy, past_obstetric, surgical_history FROM consultations WHERE consultation_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $consultationId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    echo "Read back values:\n";
    print_r($row);

    if ($cleanup) {
        $conn->query("DELETE FROM consultations WHERE consultation_id = {$consultationId}");
        echo "Cleaned up test row.\n";
    }

    $conn->close();
    echo "Verification completed successfully.\n";

} catch (Exception $e) {
    echo "Verification failed: " . $e->getMessage() . "\n";
    exit(1);
}


