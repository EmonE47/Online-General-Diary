<?php
/**
 * Landing Page / Index
 * Online General Diary System
 */

require_once 'config/config.php';
require_once 'includes/auth.php';

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

$pageTitle = 'Welcome - ' . APP_NAME;
?>

<?php include 'header.php'; ?>

<div class="container-fluid">
    <div class="row min-vh-100 align-items-center">
        <div class="col-lg-6">
            <div class="text-center text-lg-start">
                <h1 class="display-4 fw-bold text-primary mb-4">
                    <i class="fas fa-shield-alt me-3"></i>
                    Online General Diary System
                </h1>
                <p class="lead mb-4">
                    A comprehensive digital platform for managing General Diary (GD) cases with 
                    role-based access control for Administrators, Sub-Inspectors, and Citizens.
                </p>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-user-shield fa-2x text-primary mb-3"></i>
                                <h5 class="card-title">Admin Panel</h5>
                                <p class="card-text small">Manage users, assign cases, and oversee the system</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-user-tie fa-2x text-success mb-3"></i>
                                <h5 class="card-title">SI Dashboard</h5>
                                <p class="card-text small">Handle assigned cases and update status</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-user fa-2x text-info mb-3"></i>
                                <h5 class="card-title">User Portal</h5>
                                <p class="card-text small">File new GDs and track case progress</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <a href="auth/login.php" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    <a href="auth/register.php" class="btn btn-outline-primary btn-lg px-4">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="text-center">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Features</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Role-Based Access</h6>
                                        <small class="text-muted">Secure access control for different user types</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">File Upload</h6>
                                        <small class="text-muted">Support for multiple file types and sizes</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Real-time Notifications</h6>
                                        <small class="text-muted">Instant updates on case status changes</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Custom SQL Panel</h6>
                                        <small class="text-muted">Advanced query capabilities for admins</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Activity Logging</h6>
                                        <small class="text-muted">Complete audit trail of all actions</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Responsive Design</h6>
                                        <small class="text-muted">Works on all devices and screen sizes</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>Demo Credentials</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6 class="text-primary">Admin</h6>
                                    <small class="text-muted">admin@gd.com</small><br>
                                    <small class="text-muted">password123</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6 class="text-success">SI</h6>
                                    <small class="text-muted">sarah.si@gd.com</small><br>
                                    <small class="text-muted">password123</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6 class="text-info">User</h6>
                                    <small class="text-muted">rahim.user@gd.com</small><br>
                                    <small class="text-muted">password123</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
