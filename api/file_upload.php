<?php
/**
 * File Upload API
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/security.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get GD ID
$gdId = isset($_POST['gd_id']) ? (int)$_POST['gd_id'] : 0;

if (!$gdId) {
    echo json_encode(['error' => 'GD ID is required']);
    exit();
}

// Check if user has permission to upload files for this GD
$currentUser = getCurrentUser();
$sql = "SELECT user_id FROM gds WHERE gd_id = ?";
$gd = fetchRow($sql, [$gdId]);

if (!$gd) {
    echo json_encode(['error' => 'GD not found']);
    exit();
}

// Check permissions (user can upload to their own GDs, admin/SI can upload to any)
if (!isAdmin() && !isSI() && $gd['user_id'] != $currentUser['user_id']) {
    echo json_encode(['error' => 'Permission denied']);
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'No file uploaded or upload error']);
    exit();
}

// Upload file
$result = secureFileUpload($_FILES['file'], $gdId);

if (isset($result['success'])) {
    echo json_encode([
        'success' => true,
        'message' => $result['success'],
        'file_id' => $result['file_id'],
        'filename' => $result['filename']
    ]);
} else {
    echo json_encode(['error' => $result['error']]);
}