<?php
/**
 * Admin Profile Page
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

requireAdmin();

$pageTitle = 'Profile - Admin';
$currentUser = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($csrf)) {
        $error = 'Invalid request (CSRF).';
    } else {
        if ($action === 'update_profile') {
            $data = [
                'f_name' => $_POST['f_name'] ?? '',
                'l_name' => $_POST['l_name'] ?? '',
                'phone'  => $_POST['phone'] ?? '',
                'address'=> $_POST['address'] ?? ''
            ];

            $res = updateProfile($currentUser['user_id'], $data);
            if (isset($res['success'])) {
                $success = $res['success'];
                // refresh current user
                $currentUser = getCurrentUser();
                $_SESSION['user_name'] = $currentUser['f_name'] . ' ' . $currentUser['l_name'];
            } else {
                $error = $res['error'];
            }

        } elseif ($action === 'change_password') {
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if ($new !== $confirm) {
                $error = 'New passwords do not match.';
            } else {
                $res = changePassword($currentUser['user_id'], $current, $new);
                if (isset($res['success'])) {
                    $success = $res['success'];
                } else {
                    $error = $res['error'];
                }
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>

<?php include '../header.php'; ?>

<div class="container">
    <h2 class="mt-4">Profile</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Update Profile</div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="f_name" class="form-control" value="<?php echo htmlspecialchars($currentUser['f_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="l_name" class="form-control" value="<?php echo htmlspecialchars($currentUser['l_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($currentUser['phone']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control"><?php echo htmlspecialchars($currentUser['address']); ?></textarea>
                        </div>

                        <button class="btn btn-primary">Save Profile</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Change Password</div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <button class="btn btn-warning">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>

