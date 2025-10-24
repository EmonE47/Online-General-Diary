<?php
/**
 * Notifications API
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$currentUser = getCurrentUser();

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'mark_read':
            $notifId = isset($_POST['notif_id']) ? (int)$_POST['notif_id'] : 0;
            if ($notifId && markNotificationAsRead($notifId, $currentUser['user_id'])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Failed to mark notification as read']);
            }
            break;
            
        case 'mark_all_read':
            if (markAllNotificationsAsRead($currentUser['user_id'])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Failed to mark all notifications as read']);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_unread_count':
            $count = getUnreadNotificationCount($currentUser['user_id']);
            echo json_encode(['count' => $count]);
            break;
            
        case 'get_notifications':
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $notifications = getNotifications($currentUser['user_id'], $limit);
            echo json_encode(['notifications' => $notifications]);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}