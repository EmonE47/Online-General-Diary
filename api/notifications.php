<?php
/**
 * Notifications API
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Authentication required']);
    exit();
}

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'get_notifications') {
        $limit = $_GET['limit'] ?? 10;
        $notifications = getNotifications($_SESSION['user_id'], $limit);
        
        $response['success'] = true;
        $response['notifications'] = $notifications;
        
    } elseif ($action === 'get_unread_count') {
        $count = getUnreadNotificationCount($_SESSION['user_id']);
        
        $response['success'] = true;
        $response['count'] = $count;
        
    } else {
        $response['error'] = 'Invalid action';
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'mark_read') {
        $notifId = $_POST['notif_id'] ?? 0;
        
        if (!$notifId) {
            $response['error'] = 'Notification ID is required';
        } else {
            $result = markNotificationAsRead($notifId, $_SESSION['user_id']);
            
            if ($result) {
                $response['success'] = true;
                $response['message'] = 'Notification marked as read';
            } else {
                $response['error'] = 'Failed to mark notification as read';
            }
        }
        
    } elseif ($action === 'mark_all_read') {
        $result = markAllNotificationsAsRead($_SESSION['user_id']);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = 'All notifications marked as read';
        } else {
            $response['error'] = 'Failed to mark all notifications as read';
        }
        
    } elseif ($action === 'send_notification') {
        // Only admin can send notifications
        if (!isAdmin()) {
            $response['error'] = 'Permission denied';
        } else {
            $userId = $_POST['user_id'] ?? 0;
            $message = $_POST['message'] ?? '';
            $type = $_POST['type'] ?? 'info';
            $gdId = $_POST['gd_id'] ?? null;
            
            if (!$userId || !$message) {
                $response['error'] = 'User ID and message are required';
            } else {
                $result = sendNotification($userId, $message, $type, $gdId);
                
                if ($result) {
                    $response['success'] = true;
                    $response['message'] = 'Notification sent successfully';
                } else {
                    $response['error'] = 'Failed to send notification';
                }
            }
        }
        
    } else {
        $response['error'] = 'Invalid action';
    }
    
} else {
    $response['error'] = 'Invalid request method';
}

echo json_encode($response);
