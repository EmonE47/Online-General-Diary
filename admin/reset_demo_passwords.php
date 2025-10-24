<?php
/**
 * Reset Demo Account Passwords
 * Online General Diary System
 */

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/config/config.php';
require_once $projectRoot . '/includes/auth.php';
require_once $projectRoot . '/includes/security.php';

// Connect to database
$db = new Database();
$conn = $db->getConnection();

// Demo accounts to reset
$demoAccounts = [
    ['email' => 'admin@gd.com', 'password' => 'password123'],
    ['email' => 'sarah.si@gd.com', 'password' => 'password123'],
    ['email' => 'hasan.si@gd.com', 'password' => 'password123'],
    ['email' => 'fatima.si@gd.com', 'password' => 'password123'],
    ['email' => 'rahim.user@gd.com', 'password' => 'password123']
];

foreach ($demoAccounts as $account) {
    $hashedPassword = password_hash($account['password'], PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hashedPassword, $account['email']]);
    echo "Reset password for " . $account['email'] . "<br>";
}

echo "All demo account passwords have been reset.";