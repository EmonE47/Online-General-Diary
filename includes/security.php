<?php
/**
 * Security Helper Functions
 * Online General Diary System
 *
 * This file contains security-related functions for input validation,
 * SQL injection prevention, and other security measures
 */

// Get the project root directory
$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/config/config.php';
require_once $projectRoot . '/config/db.php';

/**
 * Validate and sanitize SQL query for custom SQL panel
 * @param string $query
 * @return array
 */
function validateSQLQuery($query) {
    $query = trim($query);
    
    // Check if query is empty
    if (empty($query)) {
        return ['error' => 'Query cannot be empty'];
    }
    
    // Convert to uppercase for checking
    $upperQuery = strtoupper($query);
    
    // Only allow SELECT statements
    if (!preg_match('/^\s*SELECT\s+/i', $query)) {
        return ['error' => 'Only SELECT queries are allowed'];
    }
    
    // Block dangerous keywords
    $dangerousKeywords = [
        'INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE', 'ALTER', 'TRUNCATE',
        'EXEC', 'EXECUTE', 'UNION', 'INTO', 'OUTFILE', 'LOAD_FILE',
        'INFORMATION_SCHEMA', 'SYS', 'MYSQL', 'PERFORMANCE_SCHEMA'
    ];
    
    foreach ($dangerousKeywords as $keyword) {
        if (strpos($upperQuery, $keyword) !== false) {
            return ['error' => "Keyword '{$keyword}' is not allowed"];
        }
    }
    
    // Block semicolons (except at the end)
    if (substr_count($query, ';') > 1 || (substr_count($query, ';') == 1 && !preg_match('/;\s*$/', $query))) {
        return ['error' => 'Multiple statements not allowed'];
    }
    
    // Limit query length
    if (strlen($query) > 1000) {
        return ['error' => 'Query too long (max 1000 characters)'];
    }
    
    return ['success' => true];
}

/**
 * Execute safe SQL query
 * @param string $query
 * @return array
 */
function executeSafeQuery($query) {
    $validation = validateSQLQuery($query);
    
    if (isset($validation['error'])) {
        return $validation;
    }
    
    try {
        global $pdo;
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        // Log the query
        logActivity($_SESSION['user_id'], 'SQL_QUERY', "Executed custom SQL query", null);
        
        return [
            'success' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'row_count' => $stmt->rowCount()
        ];
        
    } catch (PDOException $e) {
        error_log("SQL Query Error: " . $e->getMessage());
        return ['error' => 'Query execution failed: ' . $e->getMessage()];
    }
}

/**
 * Get predefined SQL queries
 * @return array
 */
function getPredefinedQueries() {
    return [
        [
            'name' => 'All SIs (Sub-Inspectors)',
            'description' => 'List all Sub-Inspectors from users table',
            'query' => "SELECT user_id, f_name, l_name, email, phone, address, created_at FROM users WHERE role = 'si' AND is_active = 1 ORDER BY created_at DESC"
        ],
        [
            'name' => 'Count of GDs by Status',
            'description' => 'Show count of GDs grouped by their status',
            'query' => "SELECT s.status_name, COUNT(g.gd_id) as gd_count FROM gd_statuses s LEFT JOIN gds g ON s.status_id = g.status_id WHERE s.is_active = 1 GROUP BY s.status_id, s.status_name ORDER BY gd_count DESC"
        ],
        [
            'name' => 'GDs Filed in Last 30 Days',
            'description' => 'List all GDs filed in the last 30 days with Open status',
            'query' => "SELECT g.gd_number, g.subject, g.incident_date, g.location, u.f_name, u.l_name, s.status_name FROM gds g LEFT JOIN users u ON g.user_id = u.user_id LEFT JOIN gd_statuses s ON g.status_id = s.status_id WHERE g.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND s.status_name = 'Open' ORDER BY g.created_at DESC"
        ],
        [
            'name' => 'Users with More than 5 GDs',
            'description' => 'List users who have filed more than 5 GDs',
            'query' => "SELECT u.f_name, u.l_name, u.email, u.phone, COUNT(g.gd_id) as gd_count FROM users u LEFT JOIN gds g ON u.user_id = g.user_id WHERE u.role = 'user' AND u.is_active = 1 GROUP BY u.user_id HAVING gd_count > 5 ORDER BY gd_count DESC"
        ],
        [
            'name' => 'Files Uploaded for Each GD',
            'description' => 'List all files uploaded for each GD with file details',
            'query' => "SELECT g.gd_number, g.subject, f.original_filename, f.file_type, f.file_size, f.uploaded_at FROM gds g LEFT JOIN files f ON g.gd_id = f.gd_id WHERE f.file_id IS NOT NULL ORDER BY g.gd_number, f.uploaded_at DESC"
        ],
        [
            'name' => 'SIs with Assigned GDs',
            'description' => 'List SIs and count of GDs assigned to them',
            'query' => "SELECT si.f_name, si.l_name, si.email, COUNT(g.gd_id) as assigned_gds FROM users si LEFT JOIN gds g ON si.user_id = g.assigned_si_id WHERE si.role = 'si' AND si.is_active = 1 GROUP BY si.user_id ORDER BY assigned_gds DESC"
        ],
        [
            'name' => 'Recent Activity Log',
            'description' => 'Show recent system activities',
            'query' => "SELECT al.action, al.description, u.f_name, u.l_name, al.created_at FROM activity_log al LEFT JOIN users u ON al.user_id = u.user_id ORDER BY al.created_at DESC LIMIT 20"
        ],
        [
            'name' => 'GDs by Location',
            'description' => 'Count GDs by location (top 10)',
            'query' => "SELECT location, COUNT(*) as gd_count FROM gds GROUP BY location ORDER BY gd_count DESC LIMIT 10"
        ],
        [
            'name' => 'Monthly GD Statistics',
            'description' => 'GD count by month for current year',
            'query' => "SELECT MONTH(created_at) as month, COUNT(*) as gd_count FROM gds WHERE YEAR(created_at) = YEAR(NOW()) GROUP BY MONTH(created_at) ORDER BY month"
        ],
        [
            'name' => 'Unassigned GDs',
            'description' => 'List all GDs that are not yet assigned to any SI',
            'query' => "SELECT g.gd_number, g.subject, g.incident_date, g.location, u.f_name, u.l_name, s.status_name FROM gds g LEFT JOIN users u ON g.user_id = u.user_id LEFT JOIN gd_statuses s ON g.status_id = s.status_id WHERE g.assigned_si_id IS NULL ORDER BY g.created_at DESC"
        ],
        [
            'name' => 'Admin Notes Summary',
            'description' => 'Summary of admin notes by GD',
            'query' => "SELECT g.gd_number, g.subject, COUNT(an.note_id) as note_count, MAX(an.created_at) as last_note FROM gds g LEFT JOIN admin_notes an ON g.gd_id = an.gd_id GROUP BY g.gd_id ORDER BY note_count DESC"
        ],
        [
            'name' => 'User Registration Statistics',
            'description' => 'User registration count by role and month',
            'query' => "SELECT role, MONTH(created_at) as month, COUNT(*) as user_count FROM users WHERE YEAR(created_at) = YEAR(NOW()) GROUP BY role, MONTH(created_at) ORDER BY role, month"
        ]
    ];
}

/**
 * Validate file upload
 * @param array $file
 * @return array
 */
function validateFileUpload($file) {
    $errors = [];
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $errors[] = 'No file uploaded';
        return $errors;
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = 'File size exceeds maximum allowed size (' . formatFileSize(MAX_FILE_SIZE) . ')';
    }
    
    // Check file type
    if (!isAllowedFileType($file['type'])) {
        $errors[] = 'File type not allowed';
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload error: ' . $file['error'];
    }
    
    // Additional security checks
    $fileExtension = getFileExtension($file['name']);
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'mp3', 'wav', 'mp4'];
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        $errors[] = 'File extension not allowed';
    }
    
    return $errors;
}

/**
 * Secure file upload
 * @param array $file
 * @param int $gdId
 * @return array
 */
function secureFileUpload($file, $gdId) {
    $validation = validateFileUpload($file);
    
    if (!empty($validation)) {
        return ['error' => implode(', ', $validation)];
    }
    
    // Create upload directory
    $uploadDir = createUploadDirectory($gdId);
    
    // Generate unique filename
    $fileExtension = getFileExtension($file['name']);
    $uniqueFilename = uniqid() . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . '/' . $uniqueFilename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Store file info in database
        $sql = "INSERT INTO files (gd_id, filename, original_filename, file_path, file_size, file_type) VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $gdId,
            $uniqueFilename,
            $file['name'],
            $filePath,
            $file['size'],
            $file['type']
        ];
        
        if (executeQuery($sql, $params)) {
            // Log activity
            logActivity($_SESSION['user_id'], 'FILE_UPLOAD', "Uploaded file: {$file['name']}", $gdId);
            
            return [
                'success' => 'File uploaded successfully',
                'file_id' => getLastInsertId(),
                'filename' => $uniqueFilename
            ];
        } else {
            // Remove file if database insert failed
            unlink($filePath);
            return ['error' => 'Failed to save file information'];
        }
    } else {
        return ['error' => 'Failed to move uploaded file'];
    }
}

/**
 * Delete file securely
 * @param int $fileId
 * @param int $userId
 * @return array
 */
function deleteFile($fileId, $userId) {
    // Get file info
    $sql = "SELECT f.*, g.user_id as gd_user_id FROM files f LEFT JOIN gds g ON f.gd_id = g.gd_id WHERE f.file_id = ?";
    $file = fetchRow($sql, [$fileId]);
    
    if (!$file) {
        return ['error' => 'File not found'];
    }
    
    // Check permissions (user can only delete their own files, admin can delete any)
    if (!isAdmin() && $file['gd_user_id'] != $userId) {
        return ['error' => 'Permission denied'];
    }
    
    // Delete file from filesystem
    if (file_exists($file['file_path'])) {
        unlink($file['file_path']);
    }
    
    // Delete from database
    $sql = "DELETE FROM files WHERE file_id = ?";
    if (executeQuery($sql, [$fileId])) {
        // Log activity
        logActivity($userId, 'FILE_DELETE', "Deleted file: {$file['original_filename']}", $file['gd_id']);
        
        return ['success' => 'File deleted successfully'];
    }
    
    return ['error' => 'Failed to delete file'];
}

/**
 * CSRF token generation and validation
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate limiting for login attempts
 * @param string $email
 * @return bool
 */
function checkLoginRateLimit($email) {
    $sql = "SELECT COUNT(*) as attempts FROM activity_log 
            WHERE action = 'LOGIN_FAILED' 
            AND description LIKE ? 
            AND created_at >= DATE_SUB(NOW(), INTERVAL ? SECOND)";
    
    $result = fetchRow($sql, ["%{$email}%", LOGIN_LOCKOUT_TIME]);
    
    return $result['attempts'] < MAX_LOGIN_ATTEMPTS;
}

/**
 * Log failed login attempt
 * @param string $email
 */
function logFailedLogin($email) {
    logActivity(0, 'LOGIN_FAILED', "Failed login attempt for email: {$email}");
}

/**
 * Clean old activity logs
 * @param int $days
 * @return bool
 */
function cleanOldActivityLogs($days = 90) {
    $sql = "DELETE FROM activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
    return executeQuery($sql, [$days]);
}

/**
 * Clean old notifications
 * @param int $days
 * @return bool
 */
function cleanOldNotifications($days = 30) {
    $sql = "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
    return executeQuery($sql, [$days]);
}
