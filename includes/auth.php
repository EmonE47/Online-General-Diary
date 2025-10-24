<?php
/**
 * Authentication Helper Functions
 * Online General Diary System
 * 
 * This file contains authentication and session management functions
 */

// Get the project root directory
$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/config/config.php';
require_once $projectRoot . '/config/db.php';

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
    error_log("Login attempt for email: " . $email);
    
    $sql = "SELECT * FROM users WHERE email = ? AND is_active = 1";
    $user = fetchRow($sql, [$email]);
    
    if (!$user) {
        error_log("No user found with email: " . $email);
        return false;
    }
    
    if (!password_verify($password, $user['password'])) {
        error_log("Password verification failed for email: " . $email);
        return false;
    }
    
    error_log("Password verified successfully for email: " . $email);
    
    // Update last login
    $updateSql = "UPDATE users SET updated_at = ? WHERE user_id = ?";
    executeQuery($updateSql, [getCurrentTimestamp(), $user['user_id']]);
    
    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['f_name'] . ' ' . $user['l_name'];
    $_SESSION['login_time'] = time();
    
    error_log("Session variables set for user: " . $user['email'] . ", Role: " . $user['role']);
    
    // Log activity
    logActivity($user['user_id'], 'LOGIN', 'User logged in successfully');
    
    return $user;
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
 * Register new user with specific role (Admin function)
 * @param array $userData
 * @return array|false
 */
function registerUserWithRole($userData) {
    // Validate required fields
    $requiredFields = ['f_name', 'l_name', 'email', 'password', 'phone', 'nid', 'address', 'role'];
    foreach ($requiredFields as $field) {
        if (empty($userData[$field])) {
            return ['error' => "Field {$field} is required"];
        }
    }
    
    // Validate role
    $allowedRoles = ['admin', 'si', 'user'];
    if (!in_array($userData['role'], $allowedRoles)) {
        return ['error' => 'Invalid user role'];
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
    
    // Insert user with specified role
    $sql = "INSERT INTO users (f_name, l_name, email, password, phone, nid, address, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $params = [
        sanitizeInput($userData['f_name']),
        sanitizeInput($userData['l_name']),
        sanitizeInput($userData['email']),
        $hashedPassword,
        sanitizeInput($userData['phone']),
        sanitizeInput($userData['nid']),
        sanitizeInput($userData['address']),
        $userData['role']
    ];
    
    if (executeQuery($sql, $params)) {
        $userId = getLastInsertId();
        
        // Log activity
        logActivity($_SESSION['user_id'], 'USER_CREATE', "Created new {$userData['role']} user: {$userData['email']}", null);
        
        // Send notification to the new user
        $roleName = ucfirst($userData['role']);
        if ($userData['role'] === 'si') {
            $roleName = 'Sub-Inspector';
        }
        sendNotification($userId, "Welcome! Your {$roleName} account has been created. You can now login with your credentials.", 'success');
        
        return ['success' => ucfirst($userData['role']) . ' user registered successfully', 'user_id' => $userId];
    }
    
    return ['error' => 'Registration failed'];
}

/**
 * Register new SI with admin approval required
 * @param array $userData
 * @return array|false
 */
function registerSIWithApproval($userData) {
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
    
    // Insert user as SI but inactive (requires admin approval)
    $sql = "INSERT INTO users (f_name, l_name, email, password, phone, nid, address, role, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'si', 0)";
    
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
        
        // Log activity (no user_id since it's a registration)
        logActivity(0, 'SI_REGISTER', "New SI registration: {$userData['email']}", null);
        
        // Send notification to all admins about new SI registration
        $sql = "SELECT user_id FROM users WHERE role = 'admin' AND is_active = 1";
        $admins = fetchAll($sql);
        
        foreach ($admins as $admin) {
            sendNotification($admin['user_id'], "New SI registration from {$userData['f_name']} {$userData['l_name']} ({$userData['email']}) - requires approval", 'warning');
        }
        
        return ['success' => 'SI registration submitted successfully. Your account will be activated after admin approval.'];
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
 * Update user email
 * @param int $userId
 * @param string $newEmail
 * @return array
 */
function updateEmail($userId, $newEmail) {
    // Validate email
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        return ['error' => 'Invalid email format'];
    }

    // Check if email already exists for another user
    $sql = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
    if (fetchRow($sql, [$newEmail, $userId])) {
        return ['error' => 'Email already in use'];
    }

    // Update email
    $sql = "UPDATE users SET email = ?, updated_at = ? WHERE user_id = ?";
    if (executeQuery($sql, [$newEmail, getCurrentTimestamp(), $userId])) {
        // Update session email if current user
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
            $_SESSION['user_email'] = $newEmail;
        }

        logActivity($userId, 'EMAIL_UPDATE', 'Email updated to ' . $newEmail);
        return ['success' => 'Email updated successfully'];
    }

    return ['error' => 'Failed to update email'];
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
