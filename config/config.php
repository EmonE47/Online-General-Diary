<?php
/**
 * Application Configuration
 * Online General Diary System
 * 
 * This file contains general application settings
 */

// Application settings
define('APP_NAME', 'Online General Diary System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/DB/Online-General-Diary');

// Session settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('SESSION_NAME', 'GD_SESSION');

// File upload settings
define('UPLOAD_DIR', '../assets/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', [
    'image/jpeg',
    'image/png', 
    'image/gif',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'text/plain',
    'audio/mpeg',
    'audio/wav',
    'video/mp4'
]);

// GD Number settings
define('GD_PREFIX', 'GD');
define('GD_NUMBER_FORMAT', 'Ymd'); // YearMonthDay

// Pagination settings
define('RECORDS_PER_PAGE', 10);

// Security settings
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Notification settings
define('NOTIFICATION_RETENTION_DAYS', 30);

// Email settings (if needed for notifications)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@gd-system.com');
define('FROM_NAME', 'GD System');

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    ini_set('session.cookie_secure', 0); // Allow cookies without HTTPS
    ini_set('session.cookie_httponly', 1); // Protect against XSS
    ini_set('session.use_only_cookies', 1); // Force cookies for session
    session_start();
    error_log("Session started with name: " . session_name());
}

// Set session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    error_log("Session timeout - Last activity: " . date('Y-m-d H:i:s', $_SESSION['last_activity']));
    session_unset();
    session_destroy();
    header('Location: ' . APP_URL . '/auth/login.php?timeout=1');
    exit();
}
$_SESSION['last_activity'] = time();
error_log("Session activity updated - Current time: " . date('Y-m-d H:i:s', time()));

// Helper function to get current timestamp
function getCurrentTimestamp() {
    return date('Y-m-d H:i:s');
}

// Helper function to format date
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

// Helper function to format datetime
function formatDateTime($datetime, $format = 'd M Y H:i') {
    return date($format, strtotime($datetime));
}

// Helper function to generate random string
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

// Helper function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Helper function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Helper function to validate phone number (Bangladesh format)
function isValidPhone($phone) {
    return preg_match('/^01[3-9]\d{8}$/', $phone);
}

// Helper function to validate NID
function isValidNID($nid) {
    return preg_match('/^\d{10,17}$/', $nid);
}

// Helper function to get file extension
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Helper function to format file size
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

// Helper function to check if file type is allowed
function isAllowedFileType($fileType) {
    return in_array($fileType, ALLOWED_FILE_TYPES);
}

// Helper function to create upload directory
function createUploadDirectory($gdId) {
    $uploadPath = UPLOAD_DIR . 'gd_' . $gdId;
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    return $uploadPath;
}

// Helper function to log activity
function logActivity($userId, $action, $description = '', $gdId = null) {
    global $pdo;
    
    $sql = "INSERT INTO activity_log (user_id, action, description, gd_id, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $params = [
        $userId,
        $action,
        $description,
        $gdId,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    executeQuery($sql, $params);
}

// Helper function to send notification
function sendNotification($userId, $message, $type = 'info', $gdId = null) {
    $sql = "INSERT INTO notifications (user_id, gd_id, message, type) VALUES (?, ?, ?, ?)";
    return executeQuery($sql, [$userId, $gdId, $message, $type]);
}
