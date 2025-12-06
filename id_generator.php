<?php
/**
 * Nyalife HMS - ID Generation Utilities
 * 
 * This file contains functions for generating various IDs used in the system.
 */

require_once __DIR__ . '/config/database.php';

/**
 * Generate a unique ID with a given prefix
 * 
 * @param string $prefix Prefix for the ID
 * @param string $tableName Database table to check for existing IDs
 * @param string $columnName Column name to check for existing IDs
 * @param int $prefixLength Length of the prefix (to determine substring position)
 * @param int $padding Number of digits to pad the ID with
 * @return string Generated ID
 */
function generateUniqueId($prefix, $tableName, $columnName, $prefixLength, $padding = 3) {
    $sql = "SELECT MAX(CAST(SUBSTRING($columnName, ?) AS UNSIGNED)) as max_num FROM $tableName WHERE $columnName LIKE ?";
    $result = selectSingle($sql, [$prefixLength + 1, $prefix . '%']);
    
    $nextNum = ($result && $result['max_num']) ? $result['max_num'] + 1 : 1;
    return $prefix . sprintf('%0' . $padding . 'd', $nextNum);
}

/**
 * Generate a patient number
 * 
 * @return string Generated patient number
 */
function generatePatientNumber() {
    return generateUniqueId('NYA-PAT-', 'patients', 'patient_number', 8);
}

/**
 * Generate an employee ID based on role
 * 
 * @param string $role Employee role
 * @return string Generated employee ID
 */
function generateEmployeeId($role) {
    $prefix = '';
    
    switch ($role) {
        case 'doctor':
            $prefix = 'NYA-DOC-';
            break;
        case 'nurse':
            $prefix = 'NYA-NUR-';
            break;
        case 'lab_technician':
            $prefix = 'NYA-LAB-';
            break;
        case 'pharmacist':
            $prefix = 'NYA-PHM-';
            break;
        case 'admin':
            $prefix = 'NYA-ADM-';
            break;
        default:
            $prefix = 'NYA-EMP-';
    }
    
    return generateUniqueId($prefix, 'staff', 'employee_id', 8);
}

/**
 * Generate an appointment number
 * 
 * @return string Generated appointment number
 */
function generateAppointmentNumber() {
    return generateUniqueId('NYA-APT-', 'appointments', 'appointment_number', 8);
}

/**
 * Generate a consultation ID
 * 
 * @return string Generated consultation ID
 */
function generateConsultationId() {
    return generateUniqueId('NYA-CON-', 'consultations', 'consultation_id', 8);
}

/**
 * Generate a prescription ID
 * 
 * @return string Generated prescription ID
 */
function generatePrescriptionId() {
    return generateUniqueId('NYA-PRE-', 'prescriptions', 'prescription_id', 8);
}

/**
 * Generate a lab request ID
 * 
 * @return string Generated lab request ID
 */
function generateLabRequestId() {
    return generateUniqueId('NYA-LAB-', 'lab_requests', 'request_id', 8);
}

/**
 * Generate a payment reference
 * 
 * @return string Generated payment reference
 */
function generatePaymentReference() {
    return generateUniqueId('NYA-PAY-', 'payments', 'payment_reference', 8);
}

/**
 * Generate a transaction ID
 * 
 * @return string Generated transaction ID
 */
function generateTransactionId() {
    return 'TXN-' . time() . '-' . mt_rand(1000, 9999);
}

/**
 * Generate a random password
 * 
 * @param int $length Password length
 * @return string Generated password
 */
function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    
    return $password;
} 