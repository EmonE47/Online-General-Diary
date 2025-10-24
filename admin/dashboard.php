<?php
/**
 * Admin Dashboard
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

$pageTitle = 'Admin Dashboard - ' . APP_NAME;

// Get dashboard statistics
$stats = getDashboardStats();

// Get recent GDs
$recentGDs = getGDs(1, 5);

// Get recent activity
$sql = "SELECT al.*, u.f_name, u.l_name 
        FROM activity_log al 
        LEFT JOIN users u ON al.user_id = u.user_id 
        ORDER BY al.created_at DESC 
        LIMIT 10";
$recentActivity = fetchAll($sql);

// Get unassigned GDs count
$sql = "SELECT COUNT(*) as count FROM gds WHERE assigned_si_id IS NULL";
$unassignedCount = fetchRow($sql)['count'];

// Get pending notifications count
$sql = "SELECT COUNT(*) as count FROM notifications WHERE is_read = 0";
$pendingNotifications = fetchRow($sql)['count'];
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

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number"><?php echo $stats['total_users']; ?></div>
                    <div class="text-white-50">Total Users</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number"><?php echo $stats['total_sis']; ?></div>
                    <div class="text-white-50">Sub-Inspectors</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number"><?php echo $stats['total_gds']; ?></div>
                    <div class="text-white-50">Total GDs</div>
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
                    <div class="stats-number"><?php echo $unassignedCount; ?></div>
                    <div class="text-white-50">Unassigned GDs</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <a href="gd_management.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-file-alt me-2"></i>Manage GDs
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="users.php" class="btn btn-outline-success w-100">
                            <i class="fas fa-users me-2"></i>User Management
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="register_user.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-user-plus me-2"></i>Register User
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="status_management.php" class="btn btn-outline-info w-100">
                            <i class="fas fa-tags me-2"></i>Status Management
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="sql_panel.php" class="btn btn-outline-warning w-100">
                            <i class="fas fa-database me-2"></i>SQL Panel
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="notifications.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-bell me-2"></i>Notifications
                        </a>
                    </div>
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
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Recent GDs</h5>
                <a href="gd_management.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($recentGDs['gds'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>GD Number</th>
                                    <th>Subject</th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentGDs['gds'] as $gd): ?>
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
                                        <td>
                                            <?php echo formatDate($gd['created_at']); ?>
                                        </td>
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
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No GDs found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity & Notifications -->
    <div class="col-lg-4">
        <!-- Recent Activity -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recentActivity)): ?>
                    <div class="timeline">
                        <?php foreach ($recentActivity as $activity): ?>
                            <div class="timeline-item mb-3">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-<?php 
                                                switch($activity['action']) {
                                                    case 'LOGIN': echo 'sign-in-alt'; break;
                                                    case 'LOGOUT': echo 'sign-out-alt'; break;
                                                    case 'GD_CREATED': echo 'plus'; break;
                                                    case 'GD_ASSIGNED': echo 'user-tie'; break;
                                                    case 'STATUS_UPDATE': echo 'edit'; break;
                                                    default: echo 'circle';
                                                }
                                            ?> text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="fw-bold">
                                            <?php echo htmlspecialchars($activity['f_name'] . ' ' . $activity['l_name']); ?>
                                        </div>
                                        <div class="text-muted small">
                                            <?php echo htmlspecialchars($activity['description']); ?>
                                        </div>
                                        <div class="text-muted small">
                                            <?php echo formatDateTime($activity['created_at']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-history fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No recent activity</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- System Status -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>System Status</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 text-primary"><?php echo $pendingNotifications; ?></div>
                            <small class="text-muted">Pending Notifications</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 text-warning"><?php echo $unassignedCount; ?></div>
                            <small class="text-muted">Unassigned Cases</small>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <h6 class="mb-3">GDs by Status</h6>
                <?php foreach ($stats['gds_by_status'] as $status): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small"><?php echo htmlspecialchars($status['status_name']); ?></span>
                        <span class="badge bg-primary"><?php echo $status['count']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
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
</style>

<?php include '../footer.php'; ?>
