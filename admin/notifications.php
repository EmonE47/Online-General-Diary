<?php
/**
 * Admin Notifications
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

$pageTitle = 'Notifications - ' . APP_NAME;

$currentUser = getCurrentUser();

// Handle mark as read action
if (isset($_POST['action']) && $_POST['action'] === 'mark_read' && isset($_POST['notif_id'])) {
    $notifId = (int)$_POST['notif_id'];
    if (markNotificationAsRead($notifId, $currentUser['user_id'])) {
        header('Location: notifications.php?success=marked_read');
        exit();
    }
}

// Handle mark all as read
if (isset($_POST['action']) && $_POST['action'] === 'mark_all_read') {
    if (markAllNotificationsAsRead($currentUser['user_id'])) {
        header('Location: notifications.php?success=all_marked_read');
        exit();
    }
}

// Get all notifications (admin can see all)
$sql = "SELECT n.*, u.f_name, u.l_name, u.email, g.gd_number 
        FROM notifications n 
        LEFT JOIN users u ON n.user_id = u.user_id 
        LEFT JOIN gds g ON n.gd_id = g.gd_id 
        ORDER BY n.created_at DESC 
        LIMIT 100";
$notifications = fetchAll($sql);
?>

<?php include '../header.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Notifications</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-bell me-2"></i>System Notifications</h2>
                <p class="text-muted mb-0">View and manage all system notifications</p>
            </div>
            <div>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="mark_all_read">
                    <button type="submit" class="btn btn-outline-primary" onclick="return confirm('Mark all notifications as read?')">
                        <i class="fas fa-check-double me-1"></i>Mark All as Read
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Success Messages -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?php 
        switch($_GET['success']) {
            case 'marked_read':
                echo 'Notification marked as read';
                break;
            case 'all_marked_read':
                echo 'All notifications marked as read';
                break;
        }
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Notifications List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-bell me-2"></i>All Notifications
            <span class="badge bg-primary ms-2"><?php echo count($notifications); ?></span>
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($notifications)): ?>
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item <?php echo $notification['is_read'] ? '' : 'bg-light'; ?>">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-<?php 
                                    switch($notification['type']) {
                                        case 'success': echo 'success'; break;
                                        case 'warning': echo 'warning'; break;
                                        case 'error': echo 'danger'; break;
                                        default: echo 'info';
                                    }
                                ?> rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-<?php 
                                        switch($notification['type']) {
                                            case 'success': echo 'check'; break;
                                            case 'warning': echo 'exclamation-triangle'; break;
                                            case 'error': echo 'times'; break;
                                            default: echo 'info-circle';
                                        }
                                    ?> text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1 <?php echo $notification['is_read'] ? '' : 'fw-bold'; ?>">
                                            <?php echo htmlspecialchars($notification['message']); ?>
                                        </h6>
                                        <div class="text-muted small">
                                            <i class="fas fa-user me-1"></i>
                                            <strong>User:</strong> <?php echo htmlspecialchars($notification['f_name'] . ' ' . $notification['l_name']); ?>
                                            (<?php echo htmlspecialchars($notification['email']); ?>)
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo formatDateTime($notification['created_at']); ?>
                                        </div>
                                        <?php if ($notification['gd_id']): ?>
                                            <div class="text-primary small">
                                                <i class="fas fa-file-alt me-1"></i>
                                                <strong>Related GD:</strong> <?php echo htmlspecialchars($notification['gd_number']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <?php if (!$notification['is_read']): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="mark_read">
                                                <input type="hidden" name="notif_id" value="<?php echo $notification['notif_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Mark as read">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No notifications</h5>
                <p class="text-muted">There are no notifications in the system</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?>
