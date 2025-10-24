<?php
/**
 * Common Header
 * Online General Diary System
 */

if (!isset($pageTitle)) {
    $pageTitle = APP_NAME;
}

// Ensure functions are available (some pages include header.php before loading functions)
if (!function_exists('getUnreadNotificationCount')) {
    require_once __DIR__ . '/includes/functions.php';
}

$currentUser = getCurrentUser();
$unreadNotifications = $currentUser ? getUnreadNotificationCount($currentUser['user_id']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover {
            color: white !important;
            transform: translateY(-1px);
        }
        
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .dropdown-item {
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .dropdown-item:hover {
            background-color: var(--light-color);
            transform: translateX(5px);
        }
        
        .sidebar {
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            min-height: calc(100vh - 76px);
        }
        
        .sidebar .nav-link {
            color: var(--dark-color);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background: var(--light-color);
            border: none;
            font-weight: 600;
        }
        
        .badge {
            border-radius: 20px;
            padding: 6px 12px;
        }
        
        .alert {
            border: none;
            border-radius: 10px;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .stats-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .stats-card .stats-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .stats-card .stats-number {
            font-size: 2rem;
            font-weight: 700;
        }
        
        .breadcrumb {
            background: none;
            padding: 0;
        }
        
        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: var(--dark-color);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            .main-content {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo APP_URL; ?>">
                <i class="fas fa-shield-alt me-2"></i><?php echo APP_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($currentUser): ?>
                        <!-- Notifications -->
                        <li class="nav-item dropdown">
                            <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <?php if ($unreadNotifications > 0): ?>
                                    <span class="notification-badge"><?php echo $unreadNotifications; ?></span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Notifications</h6></li>
                                <?php if ($unreadNotifications > 0): ?>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/<?php echo $currentUser['role']; ?>/notifications.php">
                                        <i class="fas fa-eye me-2"></i>View All Notifications
                                    </a></li>
                                <?php else: ?>
                                    <li><span class="dropdown-item text-muted">No new notifications</span></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        
                        <!-- User Menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($currentUser['f_name'] . ' ' . $currentUser['l_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header"><?php echo ucfirst($currentUser['role']); ?> Account</h6></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/<?php echo $currentUser['role']; ?>/profile.php">
                                    <i class="fas fa-user me-2"></i>Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/<?php echo $currentUser['role']; ?>/settings.php">
                                    <i class="fas fa-cog me-2"></i>Settings
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/auth/login.php">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <?php if ($currentUser): ?>
                <!-- Sidebar -->
                <div class="col-md-3 col-lg-2 p-0">
                    <div class="sidebar">
                        <nav class="nav flex-column">
                            <?php
                            $currentRole = $currentUser['role'];
                            $currentPage = basename($_SERVER['PHP_SELF'], '.php');
                            
                            // Define menu items based on role
                            $menuItems = [];
                            
                            if ($currentRole === 'admin') {
                                $menuItems = [
                                    ['dashboard.php', 'fas fa-tachometer-alt', 'Dashboard'],
                                    ['users.php', 'fas fa-users', 'User Management'],
                                    ['gd_management.php', 'fas fa-file-alt', 'GD Management'],
                                    ['status_management.php', 'fas fa-tags', 'Status Management'],
                                    ['sql_panel.php', 'fas fa-database', 'SQL Panel'],
                                    ['notifications.php', 'fas fa-bell', 'Notifications'],
                                    ['admin_notes.php', 'fas fa-sticky-note', 'Admin Notes']
                                ];
                            } elseif ($currentRole === 'si') {
                                $menuItems = [
                                    ['dashboard.php', 'fas fa-tachometer-alt', 'Dashboard'],
                                    ['assigned_gds.php', 'fas fa-tasks', 'Assigned GDs'],
                                    ['notifications.php', 'fas fa-bell', 'Notifications']
                                ];
                            } elseif ($currentRole === 'user') {
                                $menuItems = [
                                    ['dashboard.php', 'fas fa-tachometer-alt', 'Dashboard'],
                                    ['file_gd.php', 'fas fa-plus-circle', 'File New GD'],
                                    ['my_gds.php', 'fas fa-file-alt', 'My GDs'],
                                    ['notifications.php', 'fas fa-bell', 'Notifications']
                                ];
                            }
                            
                            foreach ($menuItems as $item):
                                $isActive = ($item[0] === $currentPage) ? 'active' : '';
                            ?>
                                <a class="nav-link <?php echo $isActive; ?>" href="<?php echo APP_URL; ?>/<?php echo $currentRole; ?>/<?php echo $item[0]; ?>">
                                    <i class="<?php echo $item[1]; ?> me-2"></i><?php echo $item[2]; ?>
                                </a>
                            <?php endforeach; ?>
                        </nav>
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="col-md-9 col-lg-10">
                    <div class="main-content">
            <?php else: ?>
                <!-- Full width for non-authenticated pages -->
                <div class="col-12">
                    <div class="main-content">
            <?php endif; ?>
