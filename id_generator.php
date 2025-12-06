<?php
/**
 * Nyalife HMS - ID Generator Functions
 * 
 * This file contains functions for generating unique IDs and numbers.
 */

/**
 * Generate a unique patient number
 * 
 * @return string Unique patient number
 */
if (!function_exists('generatePatientNumber')) {
    function generatePatientNumber() {
        $prefix = 'P';
        $year = date('Y');
        $month = date('m');
        
        // Get the last patient number for this month
        $sql = "SELECT patient_number FROM patients 
                WHERE patient_number LIKE ? 
                ORDER BY patient_number DESC 
                LIMIT 1";
        
        $pattern = $prefix . $year . $month . '%';
        $result = selectSingle($sql, [$pattern]);
        
        if ($result) {
            // Extract the sequence number and increment
            $lastNumber = $result['patient_number'];
            $sequence = intval(substr($lastNumber, -4)) + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

/**
 * Generate a unique appointment number
 * 
 * @return string Unique appointment number
 */
if (!function_exists('generateAppointmentNumber')) {
    function generateAppointmentNumber() {
        $prefix = 'APT';
        $year = date('Y');
        $month = date('m');
        
        // Get the last appointment number for this month
        $sql = "SELECT appointment_number FROM appointments 
                WHERE appointment_number LIKE ? 
                ORDER BY appointment_number DESC 
                LIMIT 1";
        
        $pattern = $prefix . $year . $month . '%';
        $result = selectSingle($sql, [$pattern]);
        
        if ($result) {
            // Extract the sequence number and increment
            $lastNumber = $result['appointment_number'];
            $sequence = intval(substr($lastNumber, -4)) + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

/**
 * Generate a unique invoice number
 * 
 * @return string Unique invoice number
 */
if (!function_exists('generateInvoiceNumber')) {
    function generateInvoiceNumber() {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        
        // Get the last invoice number for this month
        $sql = "SELECT invoice_number FROM invoices 
                WHERE invoice_number LIKE ? 
                ORDER BY invoice_number DESC 
                LIMIT 1";
        
        $pattern = $prefix . $year . $month . '%';
        $result = selectSingle($sql, [$pattern]);
        
        if ($result) {
            // Extract the sequence number and increment
            $lastNumber = $result['invoice_number'];
            $sequence = intval(substr($lastNumber, -4)) + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

/**
 * Generate a unique prescription number
 * 
 * @return string Unique prescription number
 */
if (!function_exists('generatePrescriptionNumber')) {
    function generatePrescriptionNumber() {
        $prefix = 'PRESC';
        $year = date('Y');
        $month = date('m');
        
        // Get the last prescription number for this month
        $sql = "SELECT prescription_number FROM prescriptions 
                WHERE prescription_number LIKE ? 
                ORDER BY prescription_number DESC 
                LIMIT 1";
        
        $pattern = $prefix . $year . $month . '%';
        $result = selectSingle($sql, [$pattern]);
        
        if ($result) {
            // Extract the sequence number and increment
            $lastNumber = $result['prescription_number'];
            $sequence = intval(substr($lastNumber, -4)) + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

/**
 * Generate a unique lab request number
 * 
 * @return string Unique lab request number
 */
if (!function_exists('generateLabRequestNumber')) {
    function generateLabRequestNumber() {
        $prefix = 'LAB';
        $year = date('Y');
        $month = date('m');
        
        // Get the last lab request number for this month
        $sql = "SELECT request_number FROM lab_test_requests 
                WHERE request_number LIKE ? 
                ORDER BY request_number DESC 
                LIMIT 1";
        
        $pattern = $prefix . $year . $month . '%';
        $result = selectSingle($sql, [$pattern]);
        
        if ($result) {
            // Extract the sequence number and increment
            $lastNumber = $result['request_number'];
            $sequence = intval(substr($lastNumber, -4)) + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

/**
 * Generate a unique consultation number
 * 
 * @return string Unique consultation number
 */
if (!function_exists('generateConsultationNumber')) {
    function generateConsultationNumber() {
        $prefix = 'CONS';
        $year = date('Y');
        $month = date('m');
        
        // Get the last consultation number for this month
        $sql = "SELECT consultation_number FROM consultations 
                WHERE consultation_number LIKE ? 
                ORDER BY consultation_number DESC 
                LIMIT 1";
        
        $pattern = $prefix . $year . $month . '%';
        $result = selectSingle($sql, [$pattern]);
        
        if ($result) {
            // Extract the sequence number and increment
            $lastNumber = $result['consultation_number'];
            $sequence = intval(substr($lastNumber, -4)) + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

/**
 * Generate a unique payment number
 * 
 * @return string Unique payment number
 */
if (!function_exists('generatePaymentNumber')) {
    function generatePaymentNumber() {
        $prefix = 'PAY';
        $year = date('Y');
        $month = date('m');
        
        // Get the last payment number for this month
        $sql = "SELECT payment_number FROM payments 
                WHERE payment_number LIKE ? 
                ORDER BY payment_number DESC 
                LIMIT 1";
        
        $pattern = $prefix . $year . $month . '%';
        $result = selectSingle($sql, [$pattern]);
        
        if ($result) {
            // Extract the sequence number and increment
            $lastNumber = $result['payment_number'];
            $sequence = intval(substr($lastNumber, -4)) + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

/**
 * Generate a unique follow-up number
 * 
 * @return string Unique follow-up number
 */
if (!function_exists('generateFollowUpNumber')) {
    function generateFollowUpNumber() {
        $prefix = 'FU';
        $year = date('Y');
        $month = date('m');
        
        // Get the last follow-up number for this month
        $sql = "SELECT follow_up_number FROM follow_ups 
                WHERE follow_up_number LIKE ? 
                ORDER BY follow_up_number DESC 
                LIMIT 1";
        
        $pattern = $prefix . $year . $month . '%';
        $result = selectSingle($sql, [$pattern]);
        
        if ($result) {
            // Extract the sequence number and increment
            $lastNumber = $result['follow_up_number'];
            $sequence = intval(substr($lastNumber, -4)) + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

/**
 * Generate a random token
 * 
 * @param int $length Token length
 * @return string Random token
 */
if (!function_exists('generateToken')) {
    function generateToken($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
}

/**
 * Generate a unique username
 * 
 * @param string $firstName First name
 * @param string $lastName Last name
 * @return string Unique username
 */
if (!function_exists('generateUsername')) {
    function generateUsername($firstName, $lastName) {
        $base = strtolower($firstName . '.' . $lastName);
        $base = preg_replace('/[^a-z0-9]/', '', $base);
        
        $username = $base;
        $counter = 1;
        
        // Check if username exists
        while (usernameExists($username)) {
            $username = $base . $counter;
            $counter++;
        }
        
        return $username;
    }
}

/**
 * Check if username exists
 * 
 * @param string $username Username to check
 * @return bool True if exists, false otherwise
 */
if (!function_exists('usernameExists')) {
    function usernameExists($username) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ?";
        $result = selectSingle($sql, [$username]);
        return $result && $result['count'] > 0;
    }
} 