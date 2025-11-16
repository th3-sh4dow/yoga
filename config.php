<?php
/**
 * Secure Configuration Manager
 * Loads environment variables from .env file or system environment
 */

class Config {
    private static $config = [];
    private static $loaded = false;
    
    /**
     * Load configuration from .env file or environment variables
     */
    public static function load() {
        if (self::$loaded) {
            return;
        }
        
        // Try to load from .env file first
        $envFile = __DIR__ . '/.env';
        if (file_exists($envFile)) {
            self::loadFromFile($envFile);
        }
        
        // Override with system environment variables
        self::loadFromEnvironment();
        
        self::$loaded = true;
    }
    
    /**
     * Load configuration from .env file
     */
    private static function loadFromFile($file) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }
                
                self::$config[$key] = $value;
            }
        }
    }
    
    /**
     * Load configuration from system environment variables
     */
    private static function loadFromEnvironment() {
        $envVars = [
            'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS',
            'CASHFREE_APP_ID', 'CASHFREE_CLIENT_SECRET', 'CASHFREE_PG_SECRET',
            'APP_ENV', 'SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS',
            'FROM_EMAIL', 'OWNER_EMAIL', 'WEBHOOK_SECRET'
        ];
        
        foreach ($envVars as $var) {
            $value = getenv($var);
            if ($value !== false) {
                self::$config[$var] = $value;
            }
        }
    }
    
    /**
     * Get configuration value
     */
    public static function get($key, $default = null) {
        self::load();
        return self::$config[$key] ?? $default;
    }
    
    /**
     * Check if configuration key exists
     */
    public static function has($key) {
        self::load();
        return isset(self::$config[$key]);
    }
    
    /**
     * Get database configuration
     */
    public static function getDatabase() {
        return [
            'host' => self::get('DB_HOST', 'localhost'),
            'name' => self::get('DB_NAME'),
            'user' => self::get('DB_USER'),
            'pass' => self::get('DB_PASS')
        ];
    }
    
    /**
     * Get Cashfree configuration
     */
    public static function getCashfree() {
        return [
            'app_id' => self::get('CASHFREE_APP_ID'),
            'client_secret' => self::get('CASHFREE_CLIENT_SECRET'),
            'pg_secret' => self::get('CASHFREE_PG_SECRET'),
            'env' => self::get('APP_ENV', 'production')
        ];
    }
    
    /**
     * Get email configuration
     */
    public static function getEmail() {
        return [
            'smtp_host' => self::get('SMTP_HOST', 'smtp.gmail.com'),
            'smtp_port' => self::get('SMTP_PORT', 587),
            'smtp_user' => self::get('SMTP_USER'),
            'smtp_pass' => self::get('SMTP_PASS'),
            'from_email' => self::get('FROM_EMAIL'),
            'owner_email' => self::get('OWNER_EMAIL')
        ];
    }
    
    /**
     * Validate required configuration
     */
    public static function validate() {
        $required = [
            'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS',
            'CASHFREE_APP_ID', 'CASHFREE_CLIENT_SECRET', 'CASHFREE_PG_SECRET'
        ];
        
        $missing = [];
        foreach ($required as $key) {
            if (!self::has($key) || empty(self::get($key))) {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception('Missing required configuration: ' . implode(', ', $missing));
        }
        
        return true;
    }
}