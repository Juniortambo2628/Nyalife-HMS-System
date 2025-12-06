<?php

/**
 * Nyalife HMS - Utilities Class
 *
 * Centralized utility functions to avoid duplication across the application.
 */

class Utilities
{
    /**
     * Opening hours guardrails
     */
    public static function getOpeningWindowsForDate(DateTime $date): array
    {
        // 1) Holidays default window 08:00-13:00 (assume weekends/Holidays handled same on Saturdays)
        $dow = (int)$date->format('N'); // 1=Mon .. 7=Sun
        $windows = [];
        if ($dow <= 5) { // Weekdays (1-5), $dow is always >= 1 from format('N')
            $windows[] = ['08:00', '13:00'];
            $windows[] = ['14:00', '17:00'];
        } elseif ($dow === 6) { // Saturday
            $windows[] = ['08:00', '13:00'];
        } else { // Sunday – closed by default
            $windows = [];
        }
        return $windows;
    }

    public static function isWithinOpeningHours(DateTime $start): bool
    {
        $windows = self::getOpeningWindowsForDate($start);
        if ($windows === []) {
            return false;
        }
        $time = $start->format('H:i');
        foreach ($windows as [$from, $to]) {
            if ($time >= $from && $time < $to) {
                return true;
            }
        }
        return false;
    }

    public static function nextValidStart(DateTime $proposed): ?DateTime
    {
        // Snap to next window if outside
        for ($i = 0; $i < 8; $i++) { // look up to a week ahead
            $windows = self::getOpeningWindowsForDate($proposed);
            foreach ($windows as [$from, $to]) {
                $startOfWindow = DateTime::createFromFormat('Y-m-d H:i', $proposed->format('Y-m-d') . ' ' . $from);
                $endOfWindow = DateTime::createFromFormat('Y-m-d H:i', $proposed->format('Y-m-d') . ' ' . $to);
                if ($proposed < $startOfWindow) {
                    return $startOfWindow;
                }
                if ($proposed >= $startOfWindow && $proposed < $endOfWindow) {
                    return $proposed; // already valid
                }
            }
            // move to next day 08:00
            $proposed->modify('tomorrow 08:00');
        }
        return null;
    }

    public static function generateTimeSlots(string $dateYmd, int $intervalMinutes = 30): array
    {
        $date = DateTime::createFromFormat('Y-m-d', $dateYmd);
        if (!$date) {
            return [];
        }
        $slots = [];
        foreach (self::getOpeningWindowsForDate($date) as [$from, $to]) {
            $cursor = DateTime::createFromFormat('Y-m-d H:i', $dateYmd . ' ' . $from);
            $end = DateTime::createFromFormat('Y-m-d H:i', $dateYmd . ' ' . $to);
            while ($cursor < $end) {
                $slots[] = $cursor->format('H:i');
                $cursor->modify('+' . $intervalMinutes . ' minutes');
            }
        }
        return $slots;
    }
    /**
     * Get base URL of the application
     *
     * @return string Base URL including application directory
     */
    public static function getBaseUrl(): string
    {
        return getBaseUrl(); // Use the global function for now, can be migrated here later
    }

    /**
     * Sanitize input data
     *
     * @param mixed $data Data to sanitize
     * @param object $conn Optional database connection for SQL escaping
     * @return mixed Sanitized data
     */
    public static function sanitize(mixed $data, $conn = null)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitize($value, $conn);
            }
        } else {
            $data = trim((string) $data);
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
    public static function formatDate(string $date, string $format = 'Y-m-d'): string
    {
        if ($date === '' || $date === '0') {
            return '';
        }

        $datetime = new DateTime($date);
        return $datetime->format($format);
    }

    /**
     * Generate a secure random token
     *
     * @param int $length Length of token
     * @return string Random token
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Check if a string is a valid JSON
     *
     * @param string $string String to check
     * @return bool True if valid JSON
     */
    public static function isValidJson(string $string): bool
    {
        if (!is_string($string)) {
            return false;
        }

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
    public static function getRouteUrl(string $route, array $params = []): string
    {
        $baseUrl = self::getBaseUrl();
        $url = $baseUrl . '/' . $route;

        if ($params !== []) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }
}
