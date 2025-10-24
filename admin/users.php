<?php
/**
 * User Management - Admin Panel
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

$pageTitle = 'User Management - Admin Panel';

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_user') {
        $userData = [
            'f_name' => $_POST['f_name'] ?? '',
            'l_name' => $_POST['l_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'nid' => $_POST['nid'] ?? '',
            'address' => $_POST['address'] ?? '',
            'role' => $_POST['role'] ?? 'user'
        ];
        
        $result = registerUser($userData);
        
        if (isset($result['success'])) {
            $message = $result['success'];
        } else {
            $error = $result['error'];
        }
    } elseif ($action === 'toggle_user_status') {
        $userId = $_POST['user_id'] ?? 0;
        $currentStatus = $_POST['current_status'] ?? 0;
        
        if ($currentStatus) {
            $result = deactivateUser($userId);
            $message = $result ? 'User deactivated successfully' : 'Failed to deactivate user';
        } else {
            $result = activateUser($userId);
            $message = $result ? 'User activated successfully' : 'Failed to activate user';
        }
    }
}

// Get all users with pagination
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';

$filters = [];
if (!empty($search)) {
    $filters['search'] = $search;
}
if (!empty($role)) {
    $filters['role'] = $role;
}

// Build query
$whereClause = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $whereClause .= " AND (f_name LIKE ? OR l_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($role)) {
    $whereClause .= " AND role = ?";
    $params[] = $role;
}

$limit = RECORDS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Get total count
$countSql = "SELECT COUNT(*) as total FROM users {$whereClause}";
$totalResult = fetchRow($countSql, $params);
$total = $totalResult['total'];

// Get users
$sql = "SELECT * FROM users {$whereClause} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}";
$users = fetchAll($sql, $params);

$totalPages = ceil($total / $limit);
?>

<?php include '../header.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">User Management</li>
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
    <!-- User List -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>User Management</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                    <i class="fas fa-plus me-2"></i>Add User
                </button>
            </div>
            <div class="card-body">
                <!-- Search and Filter -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="search" placeholder="Search users..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="role">
                            <option value="">All Roles</option>
                            <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="si" <?php echo $role === 'si' ? 'selected' : ''; ?>>SI</option>
                            <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>User</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- Users Table -->
                <?php if (!empty($users)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                    <?php echo strtoupper(substr($user['f_name'], 0, 1) . substr($user['l_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($user['f_name'] . ' ' . $user['l_name']); ?></div>
                                                    <small class="text-muted">NID: <?php echo htmlspecialchars($user['nid']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                switch($user['role']) {
                                                    case 'admin': echo 'danger'; break;
                                                    case 'si': echo 'success'; break;
                                                    case 'user': echo 'primary'; break;
                                                    default: echo 'secondary';
                                                }
                                            ?>">
                                                <?php echo strtoupper($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($user['created_at']); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-info" onclick="viewUser(<?php echo $user['user_id']; ?>)" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning" onclick="editUser(<?php echo $user['user_id']; ?>)" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to <?php echo $user['is_active'] ? 'deactivate' : 'activate'; ?> this user?')">
                                                    <input type="hidden" name="action" value="toggle_user_status">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                    <input type="hidden" name="current_status" value="<?php echo $user['is_active']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-<?php echo $user['is_active'] ? 'danger' : 'success'; ?>" title="<?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                        <i class="fas fa-<?php echo $user['is_active'] ? 'ban' : 'check'; ?>"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="User pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No users found</h5>
                        <p class="text-muted">Try adjusting your search criteria</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>User Statistics</h5>
            </div>
            <div class="card-body">
                <?php
                $sql = "SELECT role, COUNT(*) as count FROM users WHERE is_active = 1 GROUP BY role";
                $roleStats = fetchAll($sql);
                ?>
                <?php foreach ($roleStats as $stat): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-bold"><?php echo ucfirst($stat['role']); ?>s</div>
                            <small class="text-muted">Active users</small>
                        </div>
                        <div class="h4 text-primary"><?php echo $stat['count']; ?></div>
                    </div>
                <?php endforeach; ?>
                
                <hr>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">Total Users</div>
                        <small class="text-muted">All time</small>
                    </div>
                    <div class="h4 text-success"><?php echo $total; ?></div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Quick Info</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb me-2"></i>Tips:</h6>
                    <ul class="mb-0 small">
                        <li>Use search to find specific users</li>
                        <li>Filter by role to see specific user types</li>
                        <li>Deactivated users cannot login</li>
                        <li>Only admins can create SI accounts</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_user">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="f_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="f_name" name="f_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="l_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="l_name" name="l_name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nid" class="form-label">NID Number *</label>
                                <input type="text" class="form-control" id="nid" name="nid" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role" class="form-label">Role *</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="user">User</option>
                                    <option value="si">Sub-Inspector</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewUser(userId) {
    // Implement view user functionality
    alert('View user functionality will be implemented');
}

function editUser(userId) {
    // Implement edit user functionality
    alert('Edit user functionality will be implemented');
}
</script>

<?php include '../footer.php'; ?>
