<?php
/**
 * User Dashboard
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require user access
requireUser();

$pageTitle = 'User Dashboard - ' . APP_NAME;

$currentUser = getCurrentUser();

// Get user's GDs
$userGDs = getGDsByUser($currentUser['user_id'], 1, 5);

// Get user's notifications
$notifications = getNotifications($currentUser['user_id'], 5);

// Get statistics
$sql = "SELECT COUNT(*) as total_gds FROM gds WHERE user_id = ?";
$totalGDs = fetchRow($sql, [$currentUser['user_id']])['total_gds'];

$sql = "SELECT COUNT(*) as pending_gds FROM gds WHERE user_id = ? AND status_id IN (SELECT status_id FROM gd_statuses WHERE status_name IN ('Open', 'Assigned', 'Under Investigation'))";
$pendingGDs = fetchRow($sql, [$currentUser['user_id']])['pending_gds'];

$sql = "SELECT COUNT(*) as resolved_gds FROM gds WHERE user_id = ? AND status_id IN (SELECT status_id FROM gd_statuses WHERE status_name IN ('Resolved', 'Closed'))";
$resolvedGDs = fetchRow($sql, [$currentUser['user_id']])['resolved_gds'];
?>

<?php include '../header.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">
            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
        </li>
    </ol>
</nav>

<!-- Welcome Message -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1">Welcome back, <?php echo htmlspecialchars($currentUser['f_name']); ?>!</h4>
                        <p class="mb-0">Manage your General Diary cases and stay updated with the latest status.</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="file_gd.php" class="btn btn-light btn-lg">
                            <i class="fas fa-plus-circle me-2"></i>File New GD
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number"><?php echo $totalGDs; ?></div>
                    <div class="text-white-50">Total GDs Filed</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number"><?php echo $pendingGDs; ?></div>
                    <div class="text-white-50">Pending Cases</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number"><?php echo $resolvedGDs; ?></div>
                    <div class="text-white-50">Resolved Cases</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number"><?php echo getUnreadNotificationCount($currentUser['user_id']); ?></div>
                    <div class="text-white-50">New Notifications</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-bell"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent GDs -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>My Recent GDs</h5>
                <a href="my_gds.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($userGDs['gds'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>GD Number</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Assigned SI</th>
                                    <th>Date Filed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userGDs['gds'] as $gd): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($gd['gd_number']); ?></strong>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($gd['subject']); ?>">
                                                <?php echo htmlspecialchars($gd['subject']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                switch($gd['status_name']) {
                                                    case 'Open': echo 'warning'; break;
                                                    case 'Assigned': echo 'info'; break;
                                                    case 'Under Investigation': echo 'primary'; break;
                                                    case 'Resolved': echo 'success'; break;
                                                    case 'Closed': echo 'secondary'; break;
                                                    case 'Rejected': echo 'danger'; break;
                                                    default: echo 'light';
                                                }
                                            ?>">
                                                <?php echo htmlspecialchars($gd['status_name']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($gd['assigned_si_id']): ?>
                                                <span class="text-success">
                                                    <i class="fas fa-user-tie me-1"></i>
                                                    <?php echo htmlspecialchars($gd['si_f_name'] . ' ' . $gd['si_l_name']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    Not Assigned
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDate($gd['created_at']); ?></td>
                                        <td>
                                            <a href="gd_details.php?id=<?php echo $gd['gd_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No GDs filed yet</h5>
                        <p class="text-muted">Start by filing your first General Diary</p>
                        <a href="file_gd.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>File New GD
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Notifications and Quick Actions -->
    <div class="col-lg-4">
        <!-- Notifications -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Recent Notifications</h5>
                <a href="notifications.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($notifications)): ?>
                    <div class="timeline">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="timeline-item mb-3">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
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
                                    <div class="flex-grow-1 ms-3">
                                        <div class="fw-bold">
                                            <?php echo htmlspecialchars($notification['message']); ?>
                                        </div>
                                        <div class="text-muted small">
                                            <?php echo formatDateTime($notification['created_at']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No notifications</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="file_gd.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>File New GD
                    </a>
                    <a href="my_gds.php" class="btn btn-outline-primary">
                        <i class="fas fa-file-alt me-2"></i>View All My GDs
                    </a>
                    <a href="notifications.php" class="btn btn-outline-info">
                        <i class="fas fa-bell me-2"></i>Notifications
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stats-card {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    border-radius: 15px;
    padding: 20px;
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

.stats-icon {
    font-size: 2.5rem;
    opacity: 0.8;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
}

.timeline-item {
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    width: 2px;
    height: calc(100% + 1rem);
    background: #e9ecef;
}
</style>

<?php include '../footer.php'; ?>
