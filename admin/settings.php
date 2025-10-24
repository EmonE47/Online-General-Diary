<?php
/**
 * Admin Settings Page
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

requireAdmin();

$pageTitle = 'Settings - Admin';
$currentUser = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrf)) {
        $error = 'Invalid request (CSRF).';
    } else {
        $newEmail = sanitizeInput($_POST['email'] ?? '');
        if (!empty($newEmail)) {
            $res = updateEmail($currentUser['user_id'], $newEmail);
            if (isset($res['success'])) {
                $success = $res['success'];
                $currentUser = getCurrentUser();
            } else {
                $error = $res['error'];
            }
        } else {
            $error = 'Email cannot be empty';
        }
    }
}

$csrfToken = generateCSRFToken();
?>

<?php include '../header.php'; ?>

<div class="container">
    <h2 class="mt-4">Settings</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">Account Settings</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                </div>

                <button class="btn btn-primary">Update Email</button>
            </form>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
