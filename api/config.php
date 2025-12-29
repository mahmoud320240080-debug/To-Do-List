<?php
/**
 * ============================================
 * TASKMASTER - CONFIGURATION FILE
 * ============================================
 */

// Prevent direct access
if (!defined('TASKMASTER')) {
    die('Direct access not allowed');
}

// ============================================
// ENVIRONMENT
// ============================================
define('ENVIRONMENT', 'development'); // 'development' or 'production'

// ============================================
// DATABASE CONFIGURATION
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'taskmaster_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ============================================
// APPLICATION SETTINGS
// ============================================
define('APP_NAME', 'TaskMaster');
define('APP_VERSION', '2.0');
define('APP_URL', 'http://localhost/taskmaster');

// ============================================
// SECURITY SETTINGS
// ============================================
define('CORS_ALLOWED_ORIGINS', '*'); // In production, set specific domain
define('API_KEY', 'your-secret-api-key-here');
define('JWT_SECRET', 'your-jwt-secret-key-here');
define('JWT_EXPIRY', 86400); // 24 hours

// ============================================
// ERROR REPORTING
// ============================================
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================
// TIMEZONE
// ============================================
date_default_timezone_set('UTC');

// ============================================
// SESSION CONFIGURATION
// ============================================
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', ENVIRONMENT === 'production' ? 1 : 0);