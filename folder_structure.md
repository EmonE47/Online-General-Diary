# Online General Diary System - Folder Structure

```
Online-General-Diary/
├── config/
│   ├── db.php                 # Database connection configuration
│   └── config.php             # General application configuration
├── includes/
│   ├── auth.php               # Authentication helper functions
│   ├── functions.php          # General utility functions
│   └── security.php           # Security-related functions
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css  # Bootstrap CSS framework
│   │   └── custom.css         # Custom styles
│   ├── js/
│   │   ├── bootstrap.min.js   # Bootstrap JavaScript
│   │   ├── jquery.min.js      # jQuery library
│   │   └── custom.js          # Custom JavaScript functions
│   └── uploads/               # File upload directory (outside web root)
├── admin/
│   ├── dashboard.php          # Admin main dashboard
│   ├── users.php              # User management
│   ├── gd_management.php      # GD management and assignment
│   ├── status_management.php  # GD status CRUD
│   ├── sql_panel.php          # Custom SQL query panel
│   ├── notifications.php      # Admin notifications
│   └── admin_notes.php        # Admin notes management
├── si/
│   ├── dashboard.php          # SI dashboard
│   ├── assigned_gds.php       # View assigned GDs
│   └── gd_details.php         # GD details and updates
├── user/
│   ├── dashboard.php          # User dashboard
│   ├── file_gd.php           # File new GD form
│   ├── my_gds.php            # View user's GDs
│   └── notifications.php      # User notifications
├── auth/
│   ├── login.php              # Login page
│   ├── register.php           # User registration
│   └── logout.php             # Logout handler
├── api/
│   ├── file_upload.php        # File upload API
│   ├── notifications.php      # Notification API
│   └── ajax_handler.php       # General AJAX requests
├── database/
│   ├── schema.sql             # Database creation script
│   └── seed_data.sql          # Sample data
├── index.php                  # Landing page / redirect
├── header.php                 # Common header
├── footer.php                 # Common footer
├── sidebar.php                # Common sidebar
└── README.md                  # Setup and usage instructions
```

## File Descriptions:

### Core Configuration
- **config/db.php**: Database connection using PDO with error handling
- **config/config.php**: Application settings, file upload limits, etc.
- **includes/auth.php**: Session management, role checking, login verification
- **includes/functions.php**: Utility functions for formatting, validation
- **includes/security.php**: Input sanitization, SQL injection prevention

### Authentication System
- **auth/login.php**: Multi-role login form with validation
- **auth/register.php**: User registration with validation
- **auth/logout.php**: Session destruction and redirect

### Admin Panel
- **admin/dashboard.php**: Overview with statistics and quick actions
- **admin/users.php**: CRUD operations for all users
- **admin/gd_management.php**: View all GDs, assign to SIs, filter/search
- **admin/status_management.php**: Manage GD statuses
- **admin/sql_panel.php**: Custom SQL query interface with predefined queries
- **admin/notifications.php**: System notifications management
- **admin/admin_notes.php**: CRUD for admin notes on GDs

### SI Dashboard
- **si/dashboard.php**: SI overview with assigned GDs
- **si/assigned_gds.php**: List of GDs assigned to the SI
- **si/gd_details.php**: Detailed GD view with status updates

### User Interface
- **user/dashboard.php**: User overview with recent GDs
- **user/file_gd.php**: Form to file new GD with file upload
- **user/my_gds.php**: User's GD history and status
- **user/notifications.php**: User notifications

### API Endpoints
- **api/file_upload.php**: Secure file upload handling
- **api/notifications.php**: AJAX notification management
- **api/ajax_handler.php**: General AJAX request handler

### Database
- **database/schema.sql**: Complete database structure
- **database/seed_data.sql**: Sample data for testing

### Assets
- **assets/css/**: Bootstrap and custom styles
- **assets/js/**: JavaScript libraries and custom functions
- **assets/uploads/**: Secure file storage directory
