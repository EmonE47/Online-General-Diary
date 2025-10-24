<?php
/**
 * User File GD Page
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Require user access
requireUser();

$pageTitle = 'File New GD - ' . APP_NAME;

$currentUser = getCurrentUser();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    // Validate CSRF token
    if (!validateCSRFToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Get form data
        $subject = sanitizeInput($_POST['subject'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $incidentDate = $_POST['incident_date'] ?? '';
        $incidentTime = $_POST['incident_time'] ?? '';
        $location = sanitizeInput($_POST['location'] ?? '');
        
        // Validate required fields
        if (empty($subject) || empty($description) || empty($incidentDate) || empty($incidentTime) || empty($location)) {
            $error = 'Please fill in all required fields.';
        } else {
            // Create GD
            $gdData = [
                'user_id' => $currentUser['user_id'],
                'subject' => $subject,
                'description' => $description,
                'incident_date' => $incidentDate,
                'incident_time' => $incidentTime,
                'location' => $location
            ];
            
            $result = createGD($gdData);
            
            if (isset($result['success'])) {
                $success = $result['success'] . ' GD Number: ' . $result['gd_number'];
                
                // Handle file uploads if any
                if (!empty($_FILES['files']['name'][0])) {
                    $uploadDir = createUploadDirectory($result['gd_id']);
                    
                    foreach ($_FILES['files']['name'] as $key => $filename) {
                        if (!empty($filename)) {
                            $file = [
                                'name' => $filename,
                                'type' => $_FILES['files']['type'][$key],
                                'tmp_name' => $_FILES['files']['tmp_name'][$key],
                                'error' => $_FILES['files']['error'][$key],
                                'size' => $_FILES['files']['size'][$key]
                            ];
                            
                            $uploadResult = secureFileUpload($file, $result['gd_id']);
                            if (isset($uploadResult['error'])) {
                                $error .= ' File upload error: ' . $uploadResult['error'];
                            }
                        }
                    }
                }
                
                // Clear form data on success
                $subject = $description = $incidentDate = $incidentTime = $location = '';
            } else {
                $error = $result['error'];
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>

<?php include '../header.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">File New GD</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-plus-circle me-2"></i>File New General Diary</h2>
                <p class="text-muted mb-0">Submit a new General Diary case for investigation</p>
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

<!-- GD Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>General Diary Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="gdForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   value="<?php echo htmlspecialchars($subject ?? ''); ?>" required
                                   placeholder="Brief description of the incident">
                        </div>
                        
                        <div class="col-12">
                            <label for="description" class="form-label">Detailed Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="5" required
                                      placeholder="Provide detailed information about the incident..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="incident_date" class="form-label">Incident Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="incident_date" name="incident_date" 
                                   value="<?php echo $incidentDate ?? ''; ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="incident_time" class="form-label">Incident Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="incident_time" name="incident_time" 
                                   value="<?php echo $incidentTime ?? ''; ?>" required>
                        </div>
                        
                        <div class="col-12">
                            <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?php echo htmlspecialchars($location ?? ''); ?>" required
                                   placeholder="Where did the incident occur?">
                        </div>
                        
                        <div class="col-12">
                            <label for="files" class="form-label">Supporting Documents (Optional)</label>
                            <input type="file" class="form-control" id="files" name="files[]" multiple
                                   accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.mp3,.wav,.mp4">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Allowed file types: Images (JPG, PNG, GIF), Documents (PDF, DOC, DOCX, TXT), Audio (MP3, WAV), Video (MP4)
                                <br>Maximum file size: <?php echo formatFileSize(MAX_FILE_SIZE); ?>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="dashboard.php" class="btn btn-outline-secondary me-md-2">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i>Submit GD
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Guidelines -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Guidelines</h5>
            </div>
            <div class="card-body">
                <h6>Before filing a GD:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i>Ensure all information is accurate</li>
                    <li><i class="fas fa-check text-success me-2"></i>Provide detailed description</li>
                    <li><i class="fas fa-check text-success me-2"></i>Include exact date and time</li>
                    <li><i class="fas fa-check text-success me-2"></i>Specify precise location</li>
                    <li><i class="fas fa-check text-success me-2"></i>Attach supporting documents if available</li>
                </ul>
                
                <hr>
                
                <h6>What happens next:</h6>
                <ol>
                    <li>Your GD will be reviewed by admin</li>
                    <li>It will be assigned to a Sub-Inspector</li>
                    <li>You'll receive notifications about status updates</li>
                    <li>You can track progress in your dashboard</li>
                </ol>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> False information may result in legal consequences.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.getElementById('gdForm').addEventListener('submit', function(e) {
    const requiredFields = ['subject', 'description', 'incident_date', 'incident_time', 'location'];
    let isValid = true;
    
    requiredFields.forEach(function(fieldName) {
        const field = document.getElementById(fieldName);
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields.');
    }
});

// Set maximum date to today
document.getElementById('incident_date').max = new Date().toISOString().split('T')[0];
</script>

<?php include '../footer.php'; ?>