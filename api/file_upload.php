<?php
/**
 * File Upload API
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/security.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Authentication required']);
    exit();
}

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'upload_file') {
        $gdId = $_POST['gd_id'] ?? 0;
        
        if (!$gdId) {
            $response['error'] = 'GD ID is required';
        } elseif (!isset($_FILES['file'])) {
            $response['error'] = 'No file uploaded';
        } else {
            $file = $_FILES['file'];
            $result = secureFileUpload($file, $gdId);
            
            if (isset($result['success'])) {
                $response['success'] = true;
                $response['message'] = $result['success'];
                $response['file_id'] = $result['file_id'];
                $response['filename'] = $result['filename'];
            } else {
                $response['error'] = $result['error'];
            }
        }
    } elseif ($action === 'delete_file') {
        $fileId = $_POST['file_id'] ?? 0;
        
        if (!$fileId) {
            $response['error'] = 'File ID is required';
        } else {
            $result = deleteFile($fileId, $_SESSION['user_id']);
            
            if (isset($result['success'])) {
                $response['success'] = true;
                $response['message'] = $result['success'];
            } else {
                $response['error'] = $result['error'];
            }
        }
    } else {
        $response['error'] = 'Invalid action';
    }
} else {
    $response['error'] = 'Invalid request method';
}

echo json_encode($response);
