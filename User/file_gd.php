<?php
/**
 * File New GD - User Panel
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require user access
requireUser();

$pageTitle = 'File New GD - User Panel';

$message = '';
$error = '';
$formData = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    // Validate CSRF token
    if (!validateCSRFToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $formData = [
            'user_id' => $_SESSION['user_id'],
            'subject' => $_POST['subject'] ?? '',
            'description' => $_POST['description'] ?? '',
            'incident_date' => $_POST['incident_date'] ?? '',
            'incident_time' => $_POST['incident_time'] ?? '',
            'location' => $_POST['location'] ?? ''
        ];
        
        // Validate required fields
        $requiredFields = ['subject', 'description', 'incident_date', 'incident_time', 'location'];
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (empty($formData[$field])) {
                $missingFields[] = $field;
            }
        }
        
        if (!empty($missingFields)) {
            $error = 'Please fill in all required fields: ' . implode(', ', $missingFields);
        } else {
            // Validate date
            if (strtotime($formData['incident_date']) > time()) {
                $error = 'Incident date cannot be in the future';
            } else {
                $result = createGD($formData);
                
                if (isset($result['success'])) {
                    $message = $result['success'];
                    $formData = []; // Clear form data
                    
                    // Handle file uploads if any
                    if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
                        $uploadedFiles = [];
                        $uploadErrors = [];
                        
                        for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
                            if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                                $file = [
                                    'name' => $_FILES['files']['name'][$i],
                                    'type' => $_FILES['files']['type'][$i],
                                    'tmp_name' => $_FILES['files']['tmp_name'][$i],
                                    'error' => $_FILES['files']['error'][$i],
                                    'size' => $_FILES['files']['size'][$i]
                                ];
                                
                                $uploadResult = secureFileUpload($file, $result['gd_id']);
                                
                                if (isset($uploadResult['success'])) {
                                    $uploadedFiles[] = $file['name'];
                                } else {
                                    $uploadErrors[] = $file['name'] . ': ' . $uploadResult['error'];
                                }
                            }
                        }
                        
                        if (!empty($uploadedFiles)) {
                            $message .= ' Files uploaded: ' . implode(', ', $uploadedFiles);
                        }
                        
                        if (!empty($uploadErrors)) {
                            $error .= ' Upload errors: ' . implode(', ', $uploadErrors);
                        }
                    }
                } else {
                    $error = $result['error'];
                }
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

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>File New General Diary</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="gdForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">
                                <i class="fas fa-info-circle me-2"></i>Incident Information
                            </h6>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject/Title *</label>
                        <input type="text" class="form-control" id="subject" name="subject" 
                               value="<?php echo htmlspecialchars($formData['subject'] ?? ''); ?>" 
                               placeholder="Brief description of the incident" required>
                        <div class="form-text">Provide a clear, concise title for your complaint</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Detailed Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="6" 
                                  placeholder="Provide detailed information about the incident..." required><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
                        <div class="form-text">Include all relevant details: what happened, when, where, who was involved, etc.</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="incident_date" class="form-label">Incident Date *</label>
                                <input type="date" class="form-control" id="incident_date" name="incident_date" 
                                       value="<?php echo htmlspecialchars($formData['incident_date'] ?? ''); ?>" 
                                       max="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="incident_time" class="form-label">Incident Time *</label>
                                <input type="time" class="form-control" id="incident_time" name="incident_time" 
                                       value="<?php echo htmlspecialchars($formData['incident_time'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="location" class="form-label">Location/Address *</label>
                        <textarea class="form-control" id="location" name="location" rows="2" 
                                  placeholder="Exact location where the incident occurred..." required><?php echo htmlspecialchars($formData['location'] ?? ''); ?></textarea>
                        <div class="form-text">Be as specific as possible with the location</div>
                    </div>
                    
                    <!-- File Upload -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">
                                <i class="fas fa-paperclip me-2"></i>Supporting Documents (Optional)
                            </h6>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="files" class="form-label">Upload Files</label>
                        <input type="file" class="form-control" id="files" name="files[]" multiple 
                               accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.mp3,.wav,.mp4">
                        <div class="form-text">
                            <strong>Allowed file types:</strong> Images (JPG, PNG, GIF), Documents (PDF, DOC, DOCX, TXT), Audio (MP3, WAV), Video (MP4)<br>
                            <strong>Maximum file size:</strong> 5MB per file<br>
                            <strong>Maximum files:</strong> 10 files
                        </div>
                    </div>
                    
                    <!-- File Preview -->
                    <div id="filePreview" class="mb-3" style="display: none;">
                        <h6>Selected Files:</h6>
                        <div id="fileList" class="list-group"></div>
                    </div>
                    
                    <!-- Terms and Conditions -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I declare that the information provided is true and accurate to the best of my knowledge. 
                                I understand that providing false information may result in legal consequences.
                            </label>
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Submit GD
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-lg" onclick="resetForm()">
                            <i class="fas fa-undo me-2"></i>Reset Form
                        </button>
                        <a href="dashboard.php" class="btn btn-outline-danger btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Help Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>Need Help?</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-lightbulb me-2"></i>Tips for Filing:</h6>
                        <ul class="small">
                            <li>Be specific and detailed in your description</li>
                            <li>Include exact dates, times, and locations</li>
                            <li>Mention any witnesses if available</li>
                            <li>Upload relevant documents or photos</li>
                            <li>Keep your GD number for future reference</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-info-circle me-2"></i>Important Notes:</h6>
                        <ul class="small">
                            <li>GD will be assigned a unique number automatically</li>
                            <li>You will receive notifications about status updates</li>
                            <li>You can track your GD status anytime</li>
                            <li>Contact the assigned SI for case updates</li>
                            <li>Keep all related documents safe</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// File preview functionality
document.getElementById('files').addEventListener('change', function(e) {
    const files = e.target.files;
    const preview = document.getElementById('filePreview');
    const fileList = document.getElementById('fileList');
    
    if (files.length > 0) {
        preview.style.display = 'block';
        fileList.innerHTML = '';
        
        Array.from(files).forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'list-group-item d-flex justify-content-between align-items-center';
            
            const fileInfo = document.createElement('div');
            fileInfo.innerHTML = `
                <div class="fw-bold">${file.name}</div>
                <small class="text-muted">${formatFileSize(file.size)}</small>
            `;
            
            const removeBtn = document.createElement('button');
            removeBtn.className = 'btn btn-sm btn-outline-danger';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.onclick = function() {
                // Remove file from input
                const dt = new DataTransfer();
                Array.from(files).forEach((f, i) => {
                    if (i !== index) dt.items.add(f);
                });
                e.target.files = dt.files;
                
                // Update preview
                if (dt.files.length === 0) {
                    preview.style.display = 'none';
                } else {
                    e.target.dispatchEvent(new Event('change'));
                }
            };
            
            fileItem.appendChild(fileInfo);
            fileItem.appendChild(removeBtn);
            fileList.appendChild(fileItem);
        });
    } else {
        preview.style.display = 'none';
    }
});

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
    
    if (!document.getElementById('terms').checked) {
        alert('Please accept the terms and conditions');
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields');
    }
});

// Reset form
function resetForm() {
    if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
        document.getElementById('gdForm').reset();
        document.getElementById('filePreview').style.display = 'none';
    }
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Auto-save draft (optional)
let autoSaveTimer;
document.querySelectorAll('input, textarea').forEach(function(element) {
    element.addEventListener('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // Auto-save functionality can be implemented here
            console.log('Auto-saving draft...');
        }, 5000);
    });
});
</script>

<style>
.form-control.is-invalid {
    border-color: #dc3545;
}

.list-group-item {
    border-radius: 8px !important;
    margin-bottom: 5px;
}

.card {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-lg {
    padding: 12px 24px;
    font-size: 1.1rem;
}
</style>

<?php include '../footer.php'; ?>
