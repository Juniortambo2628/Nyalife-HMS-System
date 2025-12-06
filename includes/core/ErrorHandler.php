<?php
/**
 * Nyalife HMS - Error Handler
 * 
 * A centralized system for handling and logging errors.
 */

class ErrorHandler {
    const LOG_LEVEL_INFO = 'INFO';
    const LOG_LEVEL_WARNING = 'WARNING';
    const LOG_LEVEL_ERROR = 'ERROR';
    
    /**
     * Log a message to the error log
     * 
     * @param string $message Message to log
     * @param string $level Log level
     * @param string $context Context information
     */
    public static function log($message, $level = self::LOG_LEVEL_INFO, $context = '') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] ";
        
        if (!empty($context)) {
            $logMessage .= "[$context] ";
        }
        
        $logMessage .= $message . PHP_EOL;
        
        // Ensure logs directory exists
        $logDir = __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Log to file
        $logFile = $logDir . '/app_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Also log to PHP error log
        error_log($logMessage);
    }
    
    /**
     * Log a database error
     * 
     * @param Exception $e Exception to log
     * @param string $context Context information
     */
    public static function logDatabaseError($e, $context = '') {
        self::log($e->getMessage(), self::LOG_LEVEL_ERROR, "Database error in $context");
    }
    
    /**
     * Log a system error
     * 
     * @param Exception $e Exception to log
     * @param string $context Context information
     */
    public static function logSystemError($e, $context = '') {
        self::log($e->getMessage(), self::LOG_LEVEL_ERROR, "System error in $context");
    }
    
    /**
     * Log an API error
     * 
     * @param string $message Error message
     * @param string $endpoint API endpoint
     * @param array $params Request parameters
     */
    public static function logApiError($message, $endpoint = '', $params = []) {
        $context = "API error";
        if (!empty($endpoint)) {
            $context .= " in $endpoint";
        }
        
        $logMessage = $message;
        if (!empty($params)) {
            $logMessage .= " - Params: " . json_encode($params);
        }
        
        self::log($logMessage, self::LOG_LEVEL_ERROR, $context);
    }
}
