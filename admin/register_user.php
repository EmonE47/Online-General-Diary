<?php
/**
 * Admin User Registration
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Require admin access
requireAdmin();

$pageTitle = 'Register New User - ' . APP_NAME;

$currentUser = getCurrentUser();
$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    // Validate CSRF token
    if (!validateCSRFToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Get form data
        $userData = [
            'f_name' => sanitizeInput($_POST['f_name'] ?? ''),
            'l_name' => sanitizeInput($_POST['l_name'] ?? ''),
            'email' => sanitizeInput($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'phone' => sanitizeInput($_POST['phone'] ?? ''),
            'nid' => sanitizeInput($_POST['nid'] ?? ''),
            'address' => sanitizeInput($_POST['address'] ?? ''),
            'role' => sanitizeInput($_POST['role'] ?? 'user')
        ];
        
        // Validate passwords match
        if ($userData['password'] !== $userData['confirm_password']) {
            $error = 'Passwords do not match.';
        } else {
            // Remove confirm_password from userData
            unset($userData['confirm_password']);
            
            // Register user with specified role
            $result = registerUserWithRole($userData);
            
            if (isset($result['success'])) {
                $success = $result['success'] . ' User can now login with their credentials.';
                
                // Clear form data on success
                $userData = array_fill_keys(array_keys($userData), '');
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
        <li class="breadcrumb-item"><a href="users.php">User Management</a></li>
        <li class="breadcrumb-item active" aria-current="page">Register New User</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-user-plus me-2"></i>Register New User</h2>
                <p class="text-muted mb-0">Create new admin, SI, or user accounts</p>
            </div>
            <div>
                <a href="users.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Users
                </a>
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

<!-- Registration Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>User Registration Form</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="registerForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="row g-3">
                        <!-- Role Selection -->
                        <div class="col-12">
                            <label for="role" class="form-label">User Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin" <?php echo (isset($userData['role']) && $userData['role'] === 'admin') ? 'selected' : ''; ?>>
                                    Administrator
                                </option>
                                <option value="si" <?php echo (isset($userData['role']) && $userData['role'] === 'si') ? 'selected' : ''; ?>>
                                    Sub-Inspector (SI)
                                </option>
                                <option value="user" <?php echo (isset($userData['role']) && $userData['role'] === 'user') ? 'selected' : ''; ?>>
                                    Regular User
                                </option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="f_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="f_name" name="f_name" 
                                   value="<?php echo htmlspecialchars($userData['f_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="l_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="l_name" name="l_name" 
                                   value="<?php echo htmlspecialchars($userData['l_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-12">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required
                                   minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                            <div class="form-text">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>" required
                                   pattern="01[3-9]\d{8}" placeholder="01XXXXXXXXX">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="nid" class="form-label">NID Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nid" name="nid" 
                                   value="<?php echo htmlspecialchars($userData['nid'] ?? ''); ?>" required
                                   pattern="\d{10,17}" placeholder="10-17 digits">
                        </div>
                        
                        <div class="col-12">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="address" name="address" rows="3" required
                                      placeholder="Complete address"><?php echo htmlspecialchars($userData['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="col-12">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="users.php" class="btn btn-outline-secondary me-md-2">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-1"></i>Register User
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Role Information -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Role Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-primary">Administrator</h6>
                    <ul class="list-unstyled small">
                        <li><i class="fas fa-check text-success me-1"></i>Full system access</li>
                        <li><i class="fas fa-check text-success me-1"></i>User management</li>
                        <li><i class="fas fa-check text-success me-1"></i>GD assignment</li>
                        <li><i class="fas fa-check text-success me-1"></i>SQL panel access</li>
                        <li><i class="fas fa-check text-success me-1"></i>System configuration</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-success">Sub-Inspector (SI)</h6>
                    <ul class="list-unstyled small">
                        <li><i class="fas fa-check text-success me-1"></i>Manage assigned cases</li>
                        <li><i class="fas fa-check text-success me-1"></i>Update case status</li>
                        <li><i class="fas fa-check text-success me-1"></i>View case details</li>
                        <li><i class="fas fa-check text-success me-1"></i>Receive notifications</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-info">Regular User</h6>
                    <ul class="list-unstyled small">
                        <li><i class="fas fa-check text-success me-1"></i>File new GDs</li>
                        <li><i class="fas fa-check text-success me-1"></i>Track case progress</li>
                        <li><i class="fas fa-check text-success me-1"></i>View own cases</li>
                        <li><i class="fas fa-check text-success me-1"></i>Receive notifications</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Only create admin accounts for trusted personnel. Admin accounts have full system access.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const role = document.getElementById('role').value;
    
    if (!role) {
        e.preventDefault();
        alert('Please select a user role!');
        return false;
    }
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
    
    if (password.length < <?php echo PASSWORD_MIN_LENGTH; ?>) {
        e.preventDefault();
        alert('Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters long!');
        return false;
    }
    
    // Confirm admin creation
    if (role === 'admin') {
        if (!confirm('Are you sure you want to create an Administrator account? Admin accounts have full system access.')) {
            e.preventDefault();
            return false;
        }
    }
});

// Real-time password confirmation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include '../footer.php'; ?>
