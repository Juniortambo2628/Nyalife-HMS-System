<?php
/**
 * Nyalife HMS - Session Manager
 * 
 * A static class to manage session operations.
 */

class SessionManager {
    private static $started = false;
    
    /**
     * Ensure session is started
     */
    public static function ensureStarted() {
        if (!self::$started && session_status() === PHP_SESSION_NONE) {
            session_start();
            self::$started = true;
        }
    }
    
    /**
     * Get a session variable
     * 
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Session value or default
     */
    public static function get($key, $default = null) {
        self::ensureStarted();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    
    /**
     * Set a session variable
     * 
     * @param string $key Session key
     * @param mixed $value Value to store
     */
    public static function set($key, $value) {
        self::ensureStarted();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Remove a session variable
     * 
     * @param string $key Session key
     */
    public static function remove($key) {
        self::ensureStarted();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Check if a session variable exists
     * 
     * @param string $key Session key
     * @return bool True if exists
     */
    public static function has($key) {
        self::ensureStarted();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Destroy the session
     */
    public static function destroy() {
        self::ensureStarted();
        session_unset();
        session_destroy();
    }
    
    /**
     * Get all session data
     * 
     * @return array Session data
     */
    public static function all() {
        self::ensureStarted();
        return $_SESSION;
    }
}
