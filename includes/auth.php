<?php
/**
 * Authentication Helper Functions
 * Online General Diary System
 * 
 * This file contains authentication and session management functions
 */

require_once 'config/config.php';
require_once 'config/db.php';

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 * @return array|false
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return false;
    }
    
    $sql = "SELECT * FROM users WHERE user_id = ? AND is_active = 1";
    return fetchRow($sql, [$_SESSION['user_id']]);
}

/**
 * Get current user role
 * @return string|false
 */
function getCurrentUserRole() {
    $user = getCurrentUser();
    return $user ? $user['role'] : false;
}

/**
 * Check if current user has specific role
 * @param string $role
 * @return bool
 */
function hasRole($role) {
    $userRole = getCurrentUserRole();
    return $userRole === $role;
}

/**
 * Check if current user is admin
 * @return bool
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Check if current user is SI
 * @return bool
 */
function isSI() {
    return hasRole('si');
}

/**
 * Check if current user is regular user
 * @return bool
 */
function isUser() {
    return hasRole('user');
}

/**
 * Require login - redirect to login if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/auth/login.php');
        exit();
    }
}

/**
 * Require specific role - redirect if user doesn't have required role
 * @param string $role
 */
function requireRole($role) {
    requireLogin();
    
    if (!hasRole($role)) {
        header('Location: ' . APP_URL . '/auth/login.php?error=access_denied');
        exit();
    }
}

/**
 * Require admin access
 */
function requireAdmin() {
    requireRole('admin');
}

/**
 * Require SI access
 */
function requireSI() {
    requireRole('si');
}

/**
 * Require user access
 */
function requireUser() {
    requireRole('user');
}

/**
 * Login user
 * @param string $email
 * @param string $password
 * @return array|false
 */
function loginUser($email, $password) {
    $sql = "SELECT * FROM users WHERE email = ? AND is_active = 1";
    $user = fetchRow($sql, [$email]);
    
    if ($user && password_verify($password, $user['password'])) {
        // Update last login
        $updateSql = "UPDATE users SET updated_at = ? WHERE user_id = ?";
        executeQuery($updateSql, [getCurrentTimestamp(), $user['user_id']]);
        
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['f_name'] . ' ' . $user['l_name'];
        $_SESSION['login_time'] = time();
        
        // Log activity
        logActivity($user['user_id'], 'LOGIN', 'User logged in successfully');
        
        return $user;
    }
    
    return false;
}

/**
 * Logout user
 */
function logoutUser() {
    if (isLoggedIn()) {
        // Log activity
        logActivity($_SESSION['user_id'], 'LOGOUT', 'User logged out');
        
        // Clear session
        session_unset();
        session_destroy();
        
        // Redirect to login
        header('Location: ' . APP_URL . '/auth/login.php?logout=1');
        exit();
    }
}

/**
 * Register new user
 * @param array $userData
 * @return array|false
 */
function registerUser($userData) {
    // Validate required fields
    $requiredFields = ['f_name', 'l_name', 'email', 'password', 'phone', 'nid', 'address'];
    foreach ($requiredFields as $field) {
        if (empty($userData[$field])) {
            return ['error' => "Field {$field} is required"];
        }
    }
    
    // Validate email format
    if (!isValidEmail($userData['email'])) {
        return ['error' => 'Invalid email format'];
    }
    
    // Validate phone format
    if (!isValidPhone($userData['phone'])) {
        return ['error' => 'Invalid phone number format'];
    }
    
    // Validate NID format
    if (!isValidNID($userData['nid'])) {
        return ['error' => 'Invalid NID format'];
    }
    
    // Check if email already exists
    $sql = "SELECT user_id FROM users WHERE email = ?";
    if (fetchRow($sql, [$userData['email']])) {
        return ['error' => 'Email already exists'];
    }
    
    // Check if NID already exists
    $sql = "SELECT user_id FROM users WHERE nid = ?";
    if (fetchRow($sql, [$userData['nid']])) {
        return ['error' => 'NID already exists'];
    }
    
    // Hash password
    $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
    
    // Insert user
    $sql = "INSERT INTO users (f_name, l_name, email, password, phone, nid, address, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'user')";
    
    $params = [
        sanitizeInput($userData['f_name']),
        sanitizeInput($userData['l_name']),
        sanitizeInput($userData['email']),
        $hashedPassword,
        sanitizeInput($userData['phone']),
        sanitizeInput($userData['nid']),
        sanitizeInput($userData['address'])
    ];
    
    if (executeQuery($sql, $params)) {
        $userId = getLastInsertId();
        
        // Log activity
        logActivity($userId, 'REGISTER', 'New user registered');
        
        return ['success' => 'User registered successfully', 'user_id' => $userId];
    }
    
    return ['error' => 'Registration failed'];
}

/**
 * Change user password
 * @param int $userId
 * @param string $currentPassword
 * @param string $newPassword
 * @return array
 */
function changePassword($userId, $currentPassword, $newPassword) {
    // Get current user
    $sql = "SELECT password FROM users WHERE user_id = ?";
    $user = fetchRow($sql, [$userId]);
    
    if (!$user) {
        return ['error' => 'User not found'];
    }
    
    // Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        return ['error' => 'Current password is incorrect'];
    }
    
    // Validate new password
    if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        return ['error' => 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'];
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $sql = "UPDATE users SET password = ?, updated_at = ? WHERE user_id = ?";
    if (executeQuery($sql, [$hashedPassword, getCurrentTimestamp(), $userId])) {
        // Log activity
        logActivity($userId, 'PASSWORD_CHANGE', 'Password changed successfully');
        
        return ['success' => 'Password changed successfully'];
    }
    
    return ['error' => 'Password change failed'];
}

/**
 * Update user profile
 * @param int $userId
 * @param array $userData
 * @return array
 */
function updateProfile($userId, $userData) {
    $allowedFields = ['f_name', 'l_name', 'phone', 'address'];
    $updateFields = [];
    $params = [];
    
    foreach ($allowedFields as $field) {
        if (isset($userData[$field]) && !empty($userData[$field])) {
            $updateFields[] = "{$field} = ?";
            $params[] = sanitizeInput($userData[$field]);
        }
    }
    
    if (empty($updateFields)) {
        return ['error' => 'No fields to update'];
    }
    
    $updateFields[] = "updated_at = ?";
    $params[] = getCurrentTimestamp();
    $params[] = $userId;
    
    $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE user_id = ?";
    
    if (executeQuery($sql, $params)) {
        // Log activity
        logActivity($userId, 'PROFILE_UPDATE', 'Profile updated');
        
        return ['success' => 'Profile updated successfully'];
    }
    
    return ['error' => 'Profile update failed'];
}

/**
 * Get user by ID
 * @param int $userId
 * @return array|false
 */
function getUserById($userId) {
    $sql = "SELECT * FROM users WHERE user_id = ? AND is_active = 1";
    return fetchRow($sql, [$userId]);
}

/**
 * Get all users by role
 * @param string $role
 * @return array|false
 */
function getUsersByRole($role) {
    $sql = "SELECT * FROM users WHERE role = ? AND is_active = 1 ORDER BY created_at DESC";
    return fetchAll($sql, [$role]);
}

/**
 * Get all users
 * @return array|false
 */
function getAllUsers() {
    $sql = "SELECT * FROM users WHERE is_active = 1 ORDER BY created_at DESC";
    return fetchAll($sql);
}

/**
 * Deactivate user
 * @param int $userId
 * @return bool
 */
function deactivateUser($userId) {
    $sql = "UPDATE users SET is_active = 0, updated_at = ? WHERE user_id = ?";
    $result = executeQuery($sql, [getCurrentTimestamp(), $userId]);
    
    if ($result) {
        // Log activity
        logActivity($_SESSION['user_id'], 'USER_DEACTIVATE', "Deactivated user ID: {$userId}");
    }
    
    return $result;
}

/**
 * Activate user
 * @param int $userId
 * @return bool
 */
function activateUser($userId) {
    $sql = "UPDATE users SET is_active = 1, updated_at = ? WHERE user_id = ?";
    $result = executeQuery($sql, [getCurrentTimestamp(), $userId]);
    
    if ($result) {
        // Log activity
        logActivity($_SESSION['user_id'], 'USER_ACTIVATE', "Activated user ID: {$userId}");
    }
    
    return $result;
}
