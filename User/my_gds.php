<?php
/**
 * User My GDs Page
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require user access
requireUser();

$pageTitle = 'My GDs - ' . APP_NAME;

$currentUser = getCurrentUser();

// Get page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);

// Get user's GDs with pagination
$userGDs = getGDsByUser($currentUser['user_id'], $page, RECORDS_PER_PAGE);

// Get GD statuses for filter
$statuses = getGDStatuses();
?>

<?php include '../header.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">My GDs</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-file-alt me-2"></i>My General Diary Cases</h2>
                <p class="text-muted mb-0">Track and manage all your filed General Diary cases</p>
            </div>
            <div>
                <a href="file_gd.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>File New GD
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo $status['status_id']; ?>" 
                                <?php echo (isset($_GET['status']) && $_GET['status'] == $status['status_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status['status_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" 
                       placeholder="Search by GD number or subject">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
                <a href="my_gds.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- GDs Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-file-alt me-2"></i>My GD Cases
            <span class="badge bg-primary ms-2"><?php echo $userGDs['total']; ?></span>
        </h5>
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
                            <th>Incident Date</th>
                            <th>Location</th>
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
                                <td><?php echo formatDate($gd['incident_date']); ?></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($gd['location']); ?>">
                                        <?php echo htmlspecialchars($gd['location']); ?>
                                    </div>
                                </td>
                                <td><?php echo formatDate($gd['created_at']); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="gd_details.php?id=<?php echo $gd['gd_id']; ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($userGDs['total_pages'] > 1): ?>
                <div class="card-footer">
                    <nav aria-label="GD pagination">
                        <ul class="pagination justify-content-center mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($userGDs['total_pages'], $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $userGDs['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No GDs found</h5>
                <p class="text-muted">You haven't filed any General Diary cases yet</p>
                <a href="file_gd.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>File Your First GD
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?>
