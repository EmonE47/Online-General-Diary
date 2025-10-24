<?php
/**
 * Admin Notes Management
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Require admin access
requireAdmin();

$pageTitle = 'Admin Notes - ' . APP_NAME;

$currentUser = getCurrentUser();
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
                $noteData = [
                    'gd_id' => (int)($_POST['gd_id'] ?? 0),
                    'admin_id' => $currentUser['user_id'],
                    'note_text' => sanitizeInput($_POST['note_text'] ?? ''),
                    'is_internal' => isset($_POST['is_internal']) ? 1 : 0
                ];
                $result = createAdminNote($noteData);
                if (isset($result['success'])) {
                    $success = $result['success'];
                } else {
                    $error = $result['error'];
                }
                break;
        }
    }
}

// Get all admin notes
$sql = "SELECT an.*, u.f_name, u.l_name, g.gd_number, g.subject 
        FROM admin_notes an 
        LEFT JOIN users u ON an.admin_id = u.user_id 
        LEFT JOIN gds g ON an.gd_id = g.gd_id 
        ORDER BY an.created_at DESC 
        LIMIT 100";
$notes = fetchAll($sql);

// Get recent GDs for dropdown
$recentGDs = getGDs(1, 20);
$csrfToken = generateCSRFToken();
?>

<?php include '../header.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Admin Notes</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-sticky-note me-2"></i>Admin Notes</h2>
                <p class="text-muted mb-0">Manage internal and external notes for GD cases</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNoteModal">
                    <i class="fas fa-plus me-1"></i>Add New Note
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

<!-- Notes List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-sticky-note me-2"></i>Admin Notes
            <span class="badge bg-primary ms-2"><?php echo count($notes); ?></span>
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($notes)): ?>
            <div class="list-group list-group-flush">
                <?php foreach ($notes as $note): ?>
                    <div class="list-group-item">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-<?php echo $note['is_internal'] ? 'warning' : 'info'; ?> rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-<?php echo $note['is_internal'] ? 'lock' : 'eye'; ?> text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <?php if ($note['gd_id']): ?>
                                                <a href="gd_details.php?id=<?php echo $note['gd_id']; ?>" class="text-decoration-none">
                                                    GD #<?php echo htmlspecialchars($note['gd_number']); ?>
                                                </a>
                                                - <?php echo htmlspecialchars($note['subject']); ?>
                                            <?php else: ?>
                                                General Note
                                            <?php endif; ?>
                                            <?php if ($note['is_internal']): ?>
                                                <span class="badge bg-warning ms-2">Internal</span>
                                            <?php else: ?>
                                                <span class="badge bg-info ms-2">Public</span>
                                            <?php endif; ?>
                                        </h6>
                                        <div class="mb-2">
                                            <?php echo nl2br(htmlspecialchars($note['note_text'])); ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-user me-1"></i>
                                            <strong>Admin:</strong> <?php echo htmlspecialchars($note['f_name'] . ' ' . $note['l_name']); ?>
                                            <span class="ms-3">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo formatDateTime($note['created_at']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-sticky-note fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No admin notes found</h5>
                <p class="text-muted">Create your first admin note to get started</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Note Modal -->
<div class="modal fade" id="createNoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="create">
                
                <div class="modal-header">
                    <h5 class="modal-title">Add New Admin Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="gd_id" class="form-label">Related GD (Optional)</label>
                        <select class="form-select" id="gd_id" name="gd_id">
                            <option value="">Select a GD (optional)</option>
                            <?php foreach ($recentGDs['gds'] as $gd): ?>
                                <option value="<?php echo $gd['gd_id']; ?>">
                                    <?php echo htmlspecialchars($gd['gd_number'] . ' - ' . $gd['subject']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="note_text" class="form-label">Note Text</label>
                        <textarea class="form-control" id="note_text" name="note_text" rows="5" required
                                  placeholder="Enter your note here..."></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_internal" name="is_internal">
                            <label class="form-check-label" for="is_internal">
                                Internal Note (not visible to users)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
