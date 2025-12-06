<?php
/**
 * Nyalife HMS - Utilities Class
 * 
 * Centralized utility functions to avoid duplication across the application.
 */

class Utilities {
    /**
     * Get base URL of the application
     * 
     * @return string Base URL including application directory
     */
    public static function getBaseUrl() {
        return getBaseUrl(); // Use the global function for now, can be migrated here later
    }
    
    /**
     * Sanitize input data
     * 
     * @param mixed $data Data to sanitize
     * @param object $conn Optional database connection for SQL escaping
     * @return mixed Sanitized data
     */
    public static function sanitize($data, $conn = null) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitize($value, $conn);
            }
        } else {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            
            // Apply SQL escaping if database connection is provided
            if ($conn !== null && method_exists($conn, 'real_escape_string')) {
                $data = $conn->real_escape_string($data);
            }
        }
        
        return $data;
    }

    /**
     * Format date for display
     * 
     * @param string $date Date string
     * @param string $format Output format (default: Y-m-d)
     * @return string Formatted date
     */
    public static function formatDate($date, $format = 'Y-m-d') {
        if (empty($date)) return '';
        
        $datetime = new DateTime($date);
        return $datetime->format($format);
    }
    
    /**
     * Generate a secure random token
     * 
     * @param int $length Length of token
     * @return string Random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Check if a string is a valid JSON
     * 
     * @param string $string String to check
     * @return bool True if valid JSON
     */
    public static function isValidJson($string) {
        if (!is_string($string)) return false;
        
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
    
    /**
     * Get URL for a specific route
     * 
     * @param string $route Route name
     * @param array $params Route parameters
     * @return string Full URL
     */
    public static function getRouteUrl($route, $params = []) {
        $baseUrl = self::getBaseUrl();
        $url = $baseUrl . '/' . $route;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
}
