<?php
/**
 * AssetHelper Class
 * Handles loading of versioned assets from manifest.json
 */
class AssetHelper {
    private static $manifest;
    private static string $manifestPath = __DIR__ . '/../../assets/dist/manifest.json';
    private static string $distUrl = '/assets/dist/';

    /**
     * Load the manifest file if not already loaded
     */
    private static function loadManifest(): void {
        if (self::$manifest === null) {
            if (file_exists(self::$manifestPath)) {
                $content = file_get_contents(self::$manifestPath);
                $data = json_decode($content, true);
                // Handle structure with 'files' key or flat structure
                self::$manifest = isset($data['files']) && is_array($data['files']) ? $data['files'] : $data;
            } else {
                self::$manifest = [];
            }
        }
    }

    /**
     * Get the base URL for assets
     */
    private static function getBaseUrl(): string {
        if (defined('APP_PATH')) {
            return APP_PATH . '/';
        }
        return '/';
    }

    /**
     * Get the URL for a JavaScript entry point
     * 
     * @param string $entryPoint The entry point name (e.g., 'dashboard-admin')
     * @return string The full URL to the asset
     */
    public static function getJs(string $entryPoint) {
        self::loadManifest();
        
        // Check for .js extension in manifest
        $key = $entryPoint . '.js';
        
        if (isset(self::$manifest[$key])) {
            $manifestPath = self::$manifest[$key];
            // If manifest path is already absolute (starts with /), use it directly
            // Otherwise, prepend base URL
            if (str_starts_with((string) $manifestPath, '/')) {
                return $manifestPath;
            }
            return self::getBaseUrl() . $manifestPath;
        }
        
        // Fallback for dev mode or missing manifest
        return self::getBaseUrl() . 'assets/dist/js/' . $entryPoint . '.js';
    }

    /**
     * Get the URL for a CSS entry point
     * 
     * @param string $entryPoint The entry point name (e.g., 'dashboard-admin')
     * @return string The full URL to the asset
     */
    public static function getCss(string $entryPoint) {
        self::loadManifest();
        
        // Check for .css extension in manifest
        $key = $entryPoint . '.css';
        
        if (isset(self::$manifest[$key])) {
            $manifestPath = self::$manifest[$key];
            // If manifest path is already absolute (starts with /), use it directly
            // Otherwise, prepend base URL
            if (str_starts_with((string) $manifestPath, '/')) {
                return $manifestPath;
            }
            return self::getBaseUrl() . $manifestPath;
        }
        
        // Fallback for dev mode or missing manifest
        return self::getBaseUrl() . 'assets/dist/css/' . $entryPoint . '.css';
    }
    
    /**
     * Get the URL for any asset by filename
     * 
     * @param string $filename The filename in the manifest
     * @return string The full URL to the asset
     */
    public static function getAsset(string $filename) {
        self::loadManifest();
        
        if (isset(self::$manifest[$filename])) {
            $manifestPath = self::$manifest[$filename];
            // If manifest path is already absolute (starts with /), use it directly
            // Otherwise, prepend base URL
            if (str_starts_with((string) $manifestPath, '/')) {
                return $manifestPath;
            }
            return self::getBaseUrl() . $manifestPath;
        }
        
        return self::getBaseUrl() . 'assets/dist/' . $filename;
    }
}
