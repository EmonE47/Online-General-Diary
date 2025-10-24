<?php
/**
 * Admin Status Management
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Require admin access
requireAdmin();

$pageTitle = 'Status Management - ' . APP_NAME;

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create':
                $statusData = [
                    'status_name' => sanitizeInput($_POST['status_name'] ?? ''),
                    'description' => sanitizeInput($_POST['description'] ?? '')
                ];
                $result = createGDStatus($statusData);
                if (isset($result['success'])) {
                    $success = $result['success'];
                } else {
                    $error = $result['error'];
                }
                break;
                
            case 'update':
                $statusId = (int)($_POST['status_id'] ?? 0);
                $statusData = [
                    'status_name' => sanitizeInput($_POST['status_name'] ?? ''),
                    'description' => sanitizeInput($_POST['description'] ?? '')
                ];
                $result = updateGDStatusRecord($statusId, $statusData);
                if (isset($result['success'])) {
                    $success = $result['success'];
                } else {
                    $error = $result['error'];
                }
                break;
                
            case 'delete':
                $statusId = (int)($_POST['status_id'] ?? 0);
                $result = deleteGDStatus($statusId);
                if (isset($result['success'])) {
                    $success = $result['success'];
                } else {
                    $error = $result['error'];
                }
                break;
        }
    }
}

// Get all statuses
$statuses = getGDStatuses();
$csrfToken = generateCSRFToken();
?>

<?php include '../header.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Status Management</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-tags me-2"></i>Status Management</h2>
                <p class="text-muted mb-0">Manage GD statuses and their descriptions</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createStatusModal">
                    <i class="fas fa-plus me-1"></i>Add New Status
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Error/Success Messages -->
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Statuses Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-tags me-2"></i>GD Statuses
            <span class="badge bg-primary ms-2"><?php echo count($statuses); ?></span>
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($statuses)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Status Name</th>
                            <th>Description</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statuses as $status): ?>
                            <tr>
                                <td><?php echo $status['status_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($status['status_name']); ?></strong>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($status['description']); ?>">
                                        <?php echo htmlspecialchars($status['description']); ?>
                                    </div>
                                </td>
                                <td><?php echo formatDate($status['created_at']); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="editStatus(<?php echo htmlspecialchars(json_encode($status)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this status?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="status_id" value="<?php echo $status['status_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No statuses found</h5>
                <p class="text-muted">Create your first GD status to get started</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Status Modal -->
<div class="modal fade" id="createStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="create">
                
                <div class="modal-header">
                    <h5 class="modal-title">Add New Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="create_status_name" class="form-label">Status Name</label>
                        <input type="text" class="form-control" id="create_status_name" name="status_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_description" class="form-label">Description</label>
                        <textarea class="form-control" id="create_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Status Modal -->
<div class="modal fade" id="editStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="status_id" id="edit_status_id">
                
                <div class="modal-header">
                    <h5 class="modal-title">Edit Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_status_name" class="form-label">Status Name</label>
                        <input type="text" class="form-control" id="edit_status_name" name="status_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editStatus(status) {
    document.getElementById('edit_status_id').value = status.status_id;
    document.getElementById('edit_status_name').value = status.status_name;
    document.getElementById('edit_description').value = status.description || '';
    
    var editModal = new bootstrap.Modal(document.getElementById('editStatusModal'));
    editModal.show();
}
</script>

<?php include '../footer.php'; ?>
