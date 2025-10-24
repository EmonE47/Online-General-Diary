<?php
/**
 * Login Page
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
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

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    // Validate CSRF token
    if (!validateCSRFToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check rate limiting
        if (!checkLoginRateLimit($email)) {
            $error = 'Too many failed login attempts. Please try again later.';
        } else {
            $user = loginUser($email, $password);
            
            if ($user) {
                // Redirect based on role
                switch ($user['role']) {
                    case 'admin':
                        header('Location: ' . APP_URL . '/admin/dashboard.php');
                        break;
                    case 'si':
                        header('Location: ' . APP_URL . '/si/dashboard.php');
                        break;
                    case 'user':
                        header('Location: ' . APP_URL . '/user/dashboard.php');
                        break;
                    default:
                        $error = 'Invalid user role.';
                }
                exit();
            } else {
                logFailedLogin($email);
                $error = 'Invalid email or password.';
            }
        }
    }
}

// Handle logout message
if (isset($_GET['logout'])) {
    $success = 'You have been logged out successfully.';
}

// Handle timeout message
if (isset($_GET['timeout'])) {
    $error = 'Your session has expired. Please login again.';
}

// Handle access denied message
if (isset($_GET['error']) && $_GET['error'] === 'access_denied') {
    $error = 'Access denied. You do not have permission to access that page.';
}

$csrfToken = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .role-badges {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 1rem;
        }
        .role-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card login-card">
                    <div class="login-header">
                        <h2><i class="fas fa-shield-alt me-2"></i><?php echo APP_NAME; ?></h2>
                        <p class="mb-0">Secure Login Portal</p>
                        <div class="role-badges">
                            <span class="role-badge">Admin</span>
                            <span class="role-badge">SI</span>
                            <span class="role-badge">User</span>
                        </div>
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
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email Address
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0">Don't have an account?</p>
                            <a href="register.php" class="text-decoration-none">
                                <i class="fas fa-user-plus me-1"></i>Register as User
                            </a>
                        </div>
                        
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Demo Credentials:</h6>
                            <small class="text-muted">
                                <strong>Admin:</strong> admin@gd.com / password123<br>
                                <strong>SI:</strong> sarah.si@gd.com / password123<br>
                                <strong>User:</strong> rahim.user@gd.com / password123
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
