<?php
/**
 * GD Management - Admin Panel
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

$pageTitle = 'GD Management - Admin Panel';

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'assign_gd') {
        $gdId = $_POST['gd_id'] ?? 0;
        $siId = $_POST['si_id'] ?? 0;
        $statusId = $_POST['status_id'] ?? 0;
        
        if ($gdId && $siId && $statusId) {
            $result = updateGDStatus($gdId, $statusId, $siId);
            
            if (isset($result['success'])) {
                $message = $result['success'];
            } else {
                $error = $result['error'];
            }
        } else {
            $error = 'Please select all required fields';
        }
    }
}

// Get filter parameters
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$assignedSi = $_GET['assigned_si'] ?? '';

// Build filters
$filters = [];
if (!empty($search)) $filters['search'] = $search;
if (!empty($status)) $filters['status_id'] = $status;
if (!empty($dateFrom)) $filters['date_from'] = $dateFrom;
if (!empty($dateTo)) $filters['date_to'] = $dateTo;
if (!empty($assignedSi)) $filters['assigned_si_id'] = $assignedSi;

// Get GDs with filters
$gdData = getGDs($page, RECORDS_PER_PAGE, $filters);

// Get all statuses for filter
$statuses = getGDStatuses();

// Get all SIs for filter and assignment
$sis = getUsersByRole('si');
?>

<?php include '../header.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">GD Management</li>
    </ol>
</nav>

<!-- Messages -->
<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- GD List -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>General Diary Management</h5>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Search GDs..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <?php foreach ($statuses as $statusOption): ?>
                                <option value="<?php echo $statusOption['status_id']; ?>" 
                                        <?php echo $status == $statusOption['status_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($statusOption['status_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="assigned_si">
                            <option value="">All SIs</option>
                            <?php foreach ($sis as $si): ?>
                                <option value="<?php echo $si['user_id']; ?>" 
                                        <?php echo $assignedSi == $si['user_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($si['f_name'] . ' ' . $si['l_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_from" 
                               value="<?php echo htmlspecialchars($dateFrom); ?>" placeholder="From Date">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_to" 
                               value="<?php echo htmlspecialchars($dateTo); ?>" placeholder="To Date">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- GDs Table -->
                <?php if (!empty($gdData['gds'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>GD Number</th>
                                    <th>Subject</th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Assigned SI</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gdData['gds'] as $gd): ?>
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
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($gd['f_name'] . ' ' . $gd['l_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($gd['email']); ?></small>
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
                                                <span class="text-warning">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Unassigned
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div><?php echo formatDate($gd['incident_date']); ?></div>
                                            <small class="text-muted"><?php echo formatDate($gd['created_at']); ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-info" onclick="viewGD(<?php echo $gd['gd_id']; ?>)" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-primary" onclick="assignGD(<?php echo $gd['gd_id']; ?>)" title="Assign">
                                                    <i class="fas fa-user-tie"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning" onclick="addNote(<?php echo $gd['gd_id']; ?>)" title="Add Note">
                                                    <i class="fas fa-sticky-note"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($gdData['total_pages'] > 1): ?>
                        <nav aria-label="GD pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($gdData['page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $gdData['page'] - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&assigned_si=<?php echo urlencode($assignedSi); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $gdData['page'] - 2); $i <= min($gdData['total_pages'], $gdData['page'] + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $gdData['page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&assigned_si=<?php echo urlencode($assignedSi); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($gdData['page'] < $gdData['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $gdData['page'] + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&assigned_si=<?php echo urlencode($assignedSi); ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No GDs found</h5>
                        <p class="text-muted">Try adjusting your search criteria</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Statistics and Quick Actions -->
    <div class="col-lg-4">
        <!-- Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>GD Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 text-primary"><?php echo $gdData['total']; ?></div>
                            <small class="text-muted">Total GDs</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 text-warning"><?php echo $gdData['total'] - count(array_filter($gdData['gds'], function($gd) { return $gd['assigned_si_id']; })); ?></div>
                            <small class="text-muted">Unassigned</small>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <h6 class="mb-3">By Status</h6>
                <?php
                $statusCounts = [];
                foreach ($gdData['gds'] as $gd) {
                    $statusName = $gd['status_name'];
                    $statusCounts[$statusName] = ($statusCounts[$statusName] ?? 0) + 1;
                }
                ?>
                <?php foreach ($statusCounts as $statusName => $count): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small"><?php echo htmlspecialchars($statusName); ?></span>
                        <span class="badge bg-primary"><?php echo $count; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="status_management.php" class="btn btn-outline-primary">
                        <i class="fas fa-tags me-2"></i>Manage Statuses
                    </a>
                    <a href="admin_notes.php" class="btn btn-outline-info">
                        <i class="fas fa-sticky-note me-2"></i>Admin Notes
                    </a>
                    <button class="btn btn-outline-success" onclick="exportGDs()">
                        <i class="fas fa-download me-2"></i>Export GDs
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign GD Modal -->
<div class="modal fade" id="assignGDModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-tie me-2"></i>Assign GD to SI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="assign_gd">
                    <input type="hidden" name="gd_id" id="assign_gd_id">
                    
                    <div class="mb-3">
                        <label for="si_id" class="form-label">Select Sub-Inspector *</label>
                        <select class="form-select" id="si_id" name="si_id" required>
                            <option value="">Choose SI...</option>
                            <?php foreach ($sis as $si): ?>
                                <option value="<?php echo $si['user_id']; ?>">
                                    <?php echo htmlspecialchars($si['f_name'] . ' ' . $si['l_name'] . ' (' . $si['email'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status_id" class="form-label">Status *</label>
                        <select class="form-select" id="status_id" name="status_id" required>
                            <option value="">Choose Status...</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo $status['status_id']; ?>">
                                    <?php echo htmlspecialchars($status['status_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign GD</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewGD(gdId) {
    window.open('gd_details.php?id=' + gdId, '_blank');
}

function assignGD(gdId) {
    document.getElementById('assign_gd_id').value = gdId;
    new bootstrap.Modal(document.getElementById('assignGDModal')).show();
}

function addNote(gdId) {
    // Implement add note functionality
    alert('Add note functionality will be implemented');
}

function exportGDs() {
    // Implement export functionality
    alert('Export functionality will be implemented');
}
</script>

<?php include '../footer.php'; ?>
