<?php
/**
 * SI Dashboard
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require SI access
requireSI();

$pageTitle = 'SI Dashboard - ' . APP_NAME;

$currentUser = getCurrentUser();

// Get assigned GDs
$assignedGDs = getGDsBySI($currentUser['user_id'], 1, 5);

// Get notifications
$notifications = getNotifications($currentUser['user_id'], 5);

// Get statistics
$sql = "SELECT COUNT(*) as assigned_gds FROM gds WHERE assigned_si_id = ?";
$assignedCount = fetchRow($sql, [$currentUser['user_id']])['assigned_gds'];

$sql = "SELECT COUNT(*) as pending_gds FROM gds WHERE assigned_si_id = ? AND status_id IN (SELECT status_id FROM gd_statuses WHERE status_name IN ('Assigned', 'Under Investigation'))";
$pendingCount = fetchRow($sql, [$currentUser['user_id']])['pending_gds'];

$sql = "SELECT COUNT(*) as resolved_gds FROM gds WHERE assigned_si_id = ? AND status_id IN (SELECT status_id FROM gd_statuses WHERE status_name IN ('Resolved', 'Closed'))";
$resolvedCount = fetchRow($sql, [$currentUser['user_id']])['resolved_gds'];
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
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1">Welcome, SI <?php echo htmlspecialchars($currentUser['f_name'] . ' ' . $currentUser['l_name']); ?>!</h4>
                        <p class="mb-0">Manage your assigned cases and update their status.</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="assigned_gds.php" class="btn btn-light btn-lg">
                            <i class="fas fa-tasks me-2"></i>View All Assigned Cases
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
                    <div class="stats-number"><?php echo $assignedCount; ?></div>
                    <div class="text-white-50">Total Assigned</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number"><?php echo $pendingCount; ?></div>
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
                    <div class="stats-number"><?php echo $resolvedCount; ?></div>
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
    <!-- Assigned GDs -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>My Assigned Cases</h5>
                <a href="assigned_gds.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($assignedGDs['gds'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>GD Number</th>
                                    <th>Subject</th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Incident Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignedGDs['gds'] as $gd): ?>
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
                                            <?php echo htmlspecialchars($gd['f_name'] . ' ' . $gd['l_name']); ?>
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
                                        <td><?php echo formatDate($gd['incident_date']); ?></td>
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
                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No assigned cases</h5>
                        <p class="text-muted">You don't have any assigned cases yet</p>
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
                    <a href="assigned_gds.php" class="btn btn-primary">
                        <i class="fas fa-tasks me-2"></i>View All Assigned Cases
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
