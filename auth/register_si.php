<?php
/**
 * SI Self Registration
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $userRole = getCurrentUserRole();
    switch ($userRole) {
        case 'admin':
            header('Location: ' . APP_URL . '/admin/dashboard.php');
            break;
        case 'si':
            header('Location: ' . APP_URL . '/si/dashboard.php');
            break;
        case 'user':
            header('Location: ' . APP_URL . '/user/dashboard.php');
            break;
    }
    exit();
}

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
            'role' => 'si' // Force SI role
        ];
        
        // Validate passwords match
        if ($userData['password'] !== $userData['confirm_password']) {
            $error = 'Passwords do not match.';
        } else {
            // Remove confirm_password from userData
            unset($userData['confirm_password']);
            
            // Register user as SI (pending approval)
            $result = registerSIWithApproval($userData);
            
            if (isset($result['success'])) {
                $success = $result['success'];
                
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI Registration - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .register-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card register-card">
                    <div class="register-header">
                        <h2><i class="fas fa-user-tie me-2"></i>Sub-Inspector Registration</h2>
                        <p class="mb-0">Register for SI account (requires admin approval)</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="siRegisterForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="f_name" class="form-label">
                                        <i class="fas fa-user me-2"></i>First Name
                                    </label>
                                    <input type="text" class="form-control" id="f_name" name="f_name" 
                                           value="<?php echo htmlspecialchars($userData['f_name'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="l_name" class="form-label">
                                        <i class="fas fa-user me-2"></i>Last Name
                                    </label>
                                    <input type="text" class="form-control" id="l_name" name="l_name" 
                                           value="<?php echo htmlspecialchars($userData['l_name'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-12">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email Address
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Password
                                    </label>
                                    <input type="password" class="form-control" id="password" name="password" required
                                           minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                                    <div class="form-text">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Confirm Password
                                    </label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-2"></i>Phone Number
                                    </label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>" required
                                           pattern="01[3-9]\d{8}" placeholder="01XXXXXXXXX">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="nid" class="form-label">
                                        <i class="fas fa-id-card me-2"></i>NID Number
                                    </label>
                                    <input type="text" class="form-control" id="nid" name="nid" 
                                           value="<?php echo htmlspecialchars($userData['nid'] ?? ''); ?>" required
                                           pattern="\d{10,17}" placeholder="10-17 digits">
                                </div>
                                
                                <div class="col-12">
                                    <label for="address" class="form-label">
                                        <i class="fas fa-map-marker-alt me-2"></i>Station/Office Address
                                    </label>
                                    <textarea class="form-control" id="address" name="address" rows="3" required
                                              placeholder="Your police station or office address"><?php echo htmlspecialchars($userData['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success btn-register">
                                            <i class="fas fa-user-tie me-2"></i>Register as SI
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0">Already have an account?</p>
                            <a href="login.php" class="text-decoration-none">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                            <span class="mx-2">|</span>
                            <a href="../auth/register.php" class="text-decoration-none">
                                <i class="fas fa-user me-1"></i>Register as User
                            </a>
                        </div>
                        
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Important Notes:</h6>
                            <ul class="mb-0 small">
                                <li>Your registration will be reviewed by an administrator</li>
                                <li>You will receive an email notification once approved</li>
                                <li>Only verified police personnel can register as SI</li>
                                <li>Please provide accurate information for verification</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('siRegisterForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
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
</body>
</html>
