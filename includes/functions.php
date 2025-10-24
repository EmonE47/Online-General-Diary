<?php
/**
 * General Utility Functions
 * Online General Diary System
 * 
 * This file contains general utility functions used throughout the application
 */

/**
 * Generate GD number
 * @return string
 */
function generateGDNumber() {
    $date = date('Ymd');
    $sql = "SELECT COUNT(*) as count FROM gds WHERE DATE(created_at) = CURDATE()";
    $result = fetchRow($sql);
    $count = $result['count'] + 1;
    
    return GD_PREFIX . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
}

/**
 * Get GD by ID
 * @param int $gdId
 * @return array|false
 */
function getGDById($gdId) {
    $sql = "SELECT g.*, u.f_name, u.l_name, u.email, u.phone, 
                   s.status_name, s.description as status_description,
                   si.f_name as si_f_name, si.l_name as si_l_name
            FROM gds g
            LEFT JOIN users u ON g.user_id = u.user_id
            LEFT JOIN gd_statuses s ON g.status_id = s.status_id
            LEFT JOIN users si ON g.assigned_si_id = si.user_id
            WHERE g.gd_id = ?";
    
    return fetchRow($sql, [$gdId]);
}

/**
 * Get all GDs with pagination
 * @param int $page
 * @param int $limit
 * @param array $filters
 * @return array
 */
function getGDs($page = 1, $limit = RECORDS_PER_PAGE, $filters = []) {
    $offset = ($page - 1) * $limit;
    $whereClause = "WHERE 1=1";
    $params = [];
    
    // Apply filters
    if (!empty($filters['status_id'])) {
        $whereClause .= " AND g.status_id = ?";
        $params[] = $filters['status_id'];
    }
    
    if (!empty($filters['user_id'])) {
        $whereClause .= " AND g.user_id = ?";
        $params[] = $filters['user_id'];
    }
    
    if (!empty($filters['assigned_si_id'])) {
        $whereClause .= " AND g.assigned_si_id = ?";
        $params[] = $filters['assigned_si_id'];
    }
    
    if (!empty($filters['date_from'])) {
        $whereClause .= " AND g.incident_date >= ?";
        $params[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $whereClause .= " AND g.incident_date <= ?";
        $params[] = $filters['date_to'];
    }
    
    if (!empty($filters['search'])) {
        $whereClause .= " AND (g.subject LIKE ? OR g.description LIKE ? OR g.gd_number LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total 
                 FROM gds g 
                 LEFT JOIN users u ON g.user_id = u.user_id
                 LEFT JOIN gd_statuses s ON g.status_id = s.status_id
                 LEFT JOIN users si ON g.assigned_si_id = si.user_id
                 {$whereClause}";
    
    $totalResult = fetchRow($countSql, $params);
    $total = $totalResult['total'];
    
    // Get GDs
    $sql = "SELECT g.*, u.f_name, u.l_name, u.email, u.phone,
                   s.status_name, s.description as status_description,
                   si.f_name as si_f_name, si.l_name as si_l_name
            FROM gds g
            LEFT JOIN users u ON g.user_id = u.user_id
            LEFT JOIN gd_statuses s ON g.status_id = s.status_id
            LEFT JOIN users si ON g.assigned_si_id = si.user_id
            {$whereClause}
            ORDER BY g.created_at DESC
            LIMIT {$limit} OFFSET {$offset}";
    
    $gds = fetchAll($sql, $params);
    
    return [
        'gds' => $gds,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($total / $limit)
    ];
}

/**
 * Get GDs assigned to specific SI
 * @param int $siId
 * @param int $page
 * @param int $limit
 * @return array
 */
function getGDsBySI($siId, $page = 1, $limit = RECORDS_PER_PAGE) {
    $filters = ['assigned_si_id' => $siId];
    return getGDs($page, $limit, $filters);
}

/**
 * Get GDs by user
 * @param int $userId
 * @param int $page
 * @param int $limit
 * @return array
 */
function getGDsByUser($userId, $page = 1, $limit = RECORDS_PER_PAGE) {
    $filters = ['user_id' => $userId];
    return getGDs($page, $limit, $filters);
}

/**
 * Create new GD
 * @param array $gdData
 * @return array
 */
function createGD($gdData) {
    // Validate required fields
    $requiredFields = ['user_id', 'subject', 'description', 'incident_date', 'incident_time', 'location'];
    foreach ($requiredFields as $field) {
        if (empty($gdData[$field])) {
            return ['error' => "Field {$field} is required"];
        }
    }
    
    // Get default status (Open)
    $sql = "SELECT status_id FROM gd_statuses WHERE status_name = 'Open' LIMIT 1";
    $status = fetchRow($sql);
    
    if (!$status) {
        return ['error' => 'Default status not found'];
    }
    
    // Generate GD number
    $gdNumber = generateGDNumber();
    
    // Insert GD
    $sql = "INSERT INTO gds (gd_number, user_id, status_id, subject, description, incident_date, incident_time, location) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        $gdNumber,
        $gdData['user_id'],
        $status['status_id'],
        sanitizeInput($gdData['subject']),
        sanitizeInput($gdData['description']),
        $gdData['incident_date'],
        $gdData['incident_time'],
        sanitizeInput($gdData['location'])
    ];
    
    if (executeQuery($sql, $params)) {
        $gdId = getLastInsertId();
        
        // Log activity
        logActivity($gdData['user_id'], 'GD_CREATED', "Created GD #{$gdNumber}", $gdId);
        
        // Send notification to admin
        sendNotification(1, "New GD #{$gdNumber} filed by user", 'info', $gdId);
        
        return ['success' => 'GD created successfully', 'gd_id' => $gdId, 'gd_number' => $gdNumber];
    }
    
    return ['error' => 'GD creation failed'];
}

/**
 * Update GD status
 * @param int $gdId
 * @param int $statusId
 * @param int $assignedSiId
 * @return array
 */
function updateGDStatus($gdId, $statusId, $assignedSiId = null) {
    $sql = "UPDATE gds SET status_id = ?, assigned_si_id = ?, updated_at = ? WHERE gd_id = ?";
    
    $params = [
        $statusId,
        $assignedSiId,
        getCurrentTimestamp(),
        $gdId
    ];
    
    if (executeQuery($sql, $params)) {
        // Get GD details for notification
        $gd = getGDById($gdId);
        
        // Log activity
        logActivity($_SESSION['user_id'], 'GD_STATUS_UPDATE', "Updated GD #{$gd['gd_number']} status", $gdId);
        
        // Send notifications
        if ($assignedSiId) {
            sendNotification($assignedSiId, "You have been assigned GD #{$gd['gd_number']}", 'info', $gdId);
        }
        
        if ($gd['user_id'] != $_SESSION['user_id']) {
            sendNotification($gd['user_id'], "Your GD #{$gd['gd_number']} status has been updated", 'info', $gdId);
        }
        
        return ['success' => 'GD status updated successfully'];
    }
    
    return ['error' => 'GD status update failed'];
}

/**
 * Get GD statuses
 * @return array|false
 */
function getGDStatuses() {
    $sql = "SELECT * FROM gd_statuses WHERE is_active = 1 ORDER BY status_name";
    return fetchAll($sql);
}

/**
 * Get GD status by ID
 * @param int $statusId
 * @return array|false
 */
function getGDStatusById($statusId) {
    $sql = "SELECT * FROM gd_statuses WHERE status_id = ? AND is_active = 1";
    return fetchRow($sql, [$statusId]);
}

/**
 * Create GD status
 * @param array $statusData
 * @return array
 */
function createGDStatus($statusData) {
    if (empty($statusData['status_name'])) {
        return ['error' => 'Status name is required'];
    }
    
    $sql = "INSERT INTO gd_statuses (status_name, description) VALUES (?, ?)";
    $params = [
        sanitizeInput($statusData['status_name']),
        sanitizeInput($statusData['description'] ?? '')
    ];
    
    if (executeQuery($sql, $params)) {
        // Log activity
        logActivity($_SESSION['user_id'], 'STATUS_CREATE', "Created status: {$statusData['status_name']}");
        
        return ['success' => 'Status created successfully'];
    }
    
    return ['error' => 'Status creation failed'];
}

/**
 * Update GD status
 * @param int $statusId
 * @param array $statusData
 * @return array
 */
function updateGDStatusRecord($statusId, $statusData) {
    if (empty($statusData['status_name'])) {
        return ['error' => 'Status name is required'];
    }
    
    $sql = "UPDATE gd_statuses SET status_name = ?, description = ?, updated_at = ? WHERE status_id = ?";
    $params = [
        sanitizeInput($statusData['status_name']),
        sanitizeInput($statusData['description'] ?? ''),
        getCurrentTimestamp(),
        $statusId
    ];
    
    if (executeQuery($sql, $params)) {
        // Log activity
        logActivity($_SESSION['user_id'], 'STATUS_UPDATE', "Updated status ID: {$statusId}");
        
        return ['success' => 'Status updated successfully'];
    }
    
    return ['error' => 'Status update failed'];
}

/**
 * Delete GD status
 * @param int $statusId
 * @return array
 */
function deleteGDStatus($statusId) {
    // Check if status is being used
    $sql = "SELECT COUNT(*) as count FROM gds WHERE status_id = ?";
    $result = fetchRow($sql, [$statusId]);
    
    if ($result['count'] > 0) {
        return ['error' => 'Cannot delete status that is being used'];
    }
    
    $sql = "UPDATE gd_statuses SET is_active = 0, updated_at = ? WHERE status_id = ?";
    
    if (executeQuery($sql, [getCurrentTimestamp(), $statusId])) {
        // Log activity
        logActivity($_SESSION['user_id'], 'STATUS_DELETE', "Deleted status ID: {$statusId}");
        
        return ['success' => 'Status deleted successfully'];
    }
    
    return ['error' => 'Status deletion failed'];
}

/**
 * Get files for GD
 * @param int $gdId
 * @return array|false
 */
function getFilesByGD($gdId) {
    $sql = "SELECT * FROM files WHERE gd_id = ? ORDER BY uploaded_at DESC";
    return fetchAll($sql, [$gdId]);
}

/**
 * Get admin notes for GD
 * @param int $gdId
 * @param bool $includeInternal
 * @return array|false
 */
function getAdminNotesByGD($gdId, $includeInternal = false) {
    $whereClause = "WHERE gd_id = ?";
    $params = [$gdId];
    
    if (!$includeInternal) {
        $whereClause .= " AND is_internal = 0";
    }
    
    $sql = "SELECT an.*, u.f_name, u.l_name 
            FROM admin_notes an
            LEFT JOIN users u ON an.admin_id = u.user_id
            {$whereClause}
            ORDER BY an.created_at DESC";
    
    return fetchAll($sql, $params);
}

/**
 * Create admin note
 * @param array $noteData
 * @return array
 */
function createAdminNote($noteData) {
    $requiredFields = ['gd_id', 'admin_id', 'note_text'];
    foreach ($requiredFields as $field) {
        if (empty($noteData[$field])) {
            return ['error' => "Field {$field} is required"];
        }
    }
    
    $sql = "INSERT INTO admin_notes (gd_id, admin_id, note_text, is_internal) VALUES (?, ?, ?, ?)";
    $params = [
        $noteData['gd_id'],
        $noteData['admin_id'],
        sanitizeInput($noteData['note_text']),
        $noteData['is_internal'] ?? false
    ];
    
    if (executeQuery($sql, $params)) {
        // Log activity
        logActivity($noteData['admin_id'], 'NOTE_CREATE', "Created note for GD ID: {$noteData['gd_id']}", $noteData['gd_id']);
        
        return ['success' => 'Note created successfully'];
    }
    
    return ['error' => 'Note creation failed'];
}

/**
 * Get dashboard statistics
 * @return array
 */
function getDashboardStats() {
    $stats = [];
    
    // Total users
    $sql = "SELECT COUNT(*) as count FROM users WHERE is_active = 1";
    $result = fetchRow($sql);
    $stats['total_users'] = $result['count'];
    
    // Total SIs
    $sql = "SELECT COUNT(*) as count FROM users WHERE role = 'si' AND is_active = 1";
    $result = fetchRow($sql);
    $stats['total_sis'] = $result['count'];
    
    // Total GDs
    $sql = "SELECT COUNT(*) as count FROM gds";
    $result = fetchRow($sql);
    $stats['total_gds'] = $result['count'];
    
    // GDs by status
    $sql = "SELECT s.status_name, COUNT(g.gd_id) as count 
            FROM gd_statuses s
            LEFT JOIN gds g ON s.status_id = g.status_id
            WHERE s.is_active = 1
            GROUP BY s.status_id, s.status_name
            ORDER BY count DESC";
    $stats['gds_by_status'] = fetchAll($sql);
    
    // Recent GDs (last 7 days)
    $sql = "SELECT COUNT(*) as count FROM gds WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $result = fetchRow($sql);
    $stats['recent_gds'] = $result['count'];
    
    // Unassigned GDs
    $sql = "SELECT COUNT(*) as count FROM gds WHERE assigned_si_id IS NULL";
    $result = fetchRow($sql);
    $stats['unassigned_gds'] = $result['count'];
    
    return $stats;
}

/**
 * Get notifications for user
 * @param int $userId
 * @param int $limit
 * @return array|false
 */
function getNotifications($userId, $limit = 10) {
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
    return fetchAll($sql, [$userId, $limit]);
}

/**
 * Mark notification as read
 * @param int $notifId
 * @param int $userId
 * @return bool
 */
function markNotificationAsRead($notifId, $userId) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE notif_id = ? AND user_id = ?";
    return executeQuery($sql, [$notifId, $userId]);
}

/**
 * Mark all notifications as read for user
 * @param int $userId
 * @return bool
 */
function markAllNotificationsAsRead($userId) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
    return executeQuery($sql, [$userId]);
}

/**
 * Get unread notification count
 * @param int $userId
 * @return int
 */
function getUnreadNotificationCount($userId) {
    $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
    $result = fetchRow($sql, [$userId]);
    return $result['count'];
}
