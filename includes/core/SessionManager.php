<?php

/**
 * Nyalife HMS - Session Manager
 *
 * A static class to manage session operations.
 */

class SessionManager
{
    private static bool $started = false;

    /**
     * Ensure session is started
     */
    public static function ensureStarted(): void
    {
        // Check if session was already started in index.php
        if (isset($GLOBALS['session_started']) && $GLOBALS['session_started'] === true) {
            self::$started = true;
            return;
        }

        if (!self::$started && session_status() === PHP_SESSION_NONE) {
            // Check if headers have been sent
            if (headers_sent($file, $line)) {
                error_log("Warning: Headers already sent in $file:$line. Session may not work properly.");
            } else {
                // Start the session
                session_start();
                self::$started = true;
            }
        }
    }

    /**
     * Get a session variable
     *
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Session value or default
     */
    public static function get($key, mixed $default = null)
    {
        self::ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session variable
     *
     * @param string $key Session key
     * @param mixed $value Value to store
     */
    public static function set(string $key, mixed $value): void
    {
        self::ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Remove a session variable
     *
     * @param string $key Session key
     */
    public static function remove(string $key): void
    {
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
    public static function has(string $key): bool
    {
        self::ensureStarted();
        return isset($_SESSION[$key]);
    }

    /**
     * Destroy the session
     */
    public static function destroy(): void
    {
        self::ensureStarted();
        session_unset();
        session_destroy();
    }

    /**
     * Get all session data
     *
     * @return array Session data
     */
    public static function all(): array
    {
        self::ensureStarted();
        return $_SESSION;
    }

    /**
     * Check if user is logged in
     *
     * @return bool True if user is logged in
     */
    public static function isLoggedIn(): bool
    {
        self::ensureStarted();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}
