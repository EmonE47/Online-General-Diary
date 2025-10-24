# Online General Diary System

A comprehensive web-based General Diary (GD) management system built with PHP, MySQL, and Bootstrap. This system provides role-based access control for Administrators, Sub-Inspectors (SI), and Citizens to manage GD cases efficiently.

## 🚀 Features

### Core Functionality
- **Role-Based Access Control**: Admin, SI, and User roles with different permissions
- **GD Management**: File, track, and manage General Diary cases
- **File Upload**: Support for multiple file types (images, documents, audio, video)
- **Real-time Notifications**: Instant updates on case status changes
- **Activity Logging**: Complete audit trail of all system actions
- **Responsive Design**: Works on all devices and screen sizes

### Admin Features
- **Dashboard**: System overview with statistics
- **User Management**: Manage users, roles, and permissions
- **User Registration**: Create new admin, SI, and user accounts
- **SI Approval**: Approve/reject SI self-registrations
- **GD Management**: Assign cases to SIs and update status
- **Status Management**: Create and manage GD statuses
- **SQL Panel**: Execute custom queries for advanced analysis
- **Notifications**: View all system notifications
- **Admin Notes**: Add internal and public notes to cases

### SI (Sub-Inspector) Features
- **Dashboard**: View assigned cases and statistics
- **Assigned Cases**: Manage assigned GD cases
- **Status Updates**: Update case status and progress
- **Notifications**: Receive case assignments and updates

### User Features
- **Dashboard**: Personal GD overview and statistics
- **File New GD**: Submit new General Diary cases
- **My GDs**: Track all filed cases
- **Notifications**: Receive status updates

## 🛠️ Installation & Setup

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Web browser
- MySQL database access

### Installation Steps

1. **Clone/Download the project** to your XAMPP htdocs directory:
   ```
   C:\xampp\htdocs\DB\Online-General-Diary\
   ```

2. **Start XAMPP services**:
   - Start Apache
   - Start MySQL

3. **Set up the database**:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `online_gd_system`
   - Import the schema from `database/schema.sql`
   - Import the seed data from `database/seed_data.sql`

4. **Configure the application**:
   - Database settings are in `config/db.php`
   - Application settings are in `config/config.php`
   - Default settings work with XAMPP installation

5. **Access the application**:
   - Open your browser and go to: `http://localhost/DB/Online-General-Diary`

## 🔐 Registration & Login System

### Registration Methods

#### 1. **Admin Registration** (Admin Panel)
- **Access**: Admin dashboard → "Register User" button
- **Who can use**: Only existing administrators
- **Process**: Immediate activation
- **Roles available**: Admin, SI, User
- **Features**: 
  - Full role selection
  - Immediate account activation
  - Automatic notifications to new users

#### 2. **SI Self-Registration** (Public)
- **Access**: Login page → "Register as SI" link
- **Who can use**: Police personnel
- **Process**: Requires admin approval
- **Role**: Sub-Inspector only
- **Features**:
  - Account created but inactive
  - Admin notification for approval
  - Email notification upon approval

#### 3. **User Registration** (Public)
- **Access**: Login page → "Register as User" link
- **Who can use**: General public
- **Process**: Immediate activation
- **Role**: Regular user only
- **Features**: 
  - Immediate account activation
  - Can file GDs immediately

### Default Login Credentials

### Admin Account
- **Email**: admin@gd.com
- **Password**: password123
- **Access**: Full system administration

### SI Account
- **Email**: sarah.si@gd.com
- **Password**: password123
- **Access**: Manage assigned cases

### User Account
- **Email**: rahim.user@gd.com
- **Password**: password123
- **Access**: File and track GDs

## 📁 Project Structure

```
Online-General-Diary/
├── admin/                 # Admin panel pages
│   ├── dashboard.php
│   ├── gd_management.php
│   ├── users.php
│   ├── status_management.php
│   ├── sql_panel.php
│   ├── notifications.php
│   └── admin_notes.php
├── api/                  # API endpoints
│   ├── file_upload.php
│   └── notifications.php
├── auth/                 # Authentication pages
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── config/               # Configuration files
│   ├── config.php
│   └── db.php
├── database/             # Database files
│   ├── schema.sql
│   └── seed_data.sql
├── includes/             # Core functions
│   ├── auth.php
│   ├── functions.php
│   └── security.php
├── si/                   # SI panel pages
│   ├── dashboard.php
│   ├── assigned_gds.php
│   └── notifications.php
├── User/                 # User panel pages
│   ├── dashboard.php
│   ├── file_gd.php
│   ├── my_gds.php
│   └── notifications.php
├── assets/               # Static assets
│   └── uploads/          # File uploads directory
├── header.php            # Common header
├── footer.php            # Common footer
└── index.php             # Landing page
```

## 🔧 Configuration

### Database Configuration (`config/db.php`)
```php
private $host = 'localhost';
private $db_name = 'online_gd_system';
private $username = 'root';
private $password = '';
```

### Application Configuration (`config/config.php`)
- **APP_URL**: Application base URL
- **UPLOAD_DIR**: File upload directory
- **MAX_FILE_SIZE**: Maximum file size (5MB)
- **SESSION_TIMEOUT**: Session timeout (1 hour)
- **PASSWORD_MIN_LENGTH**: Minimum password length (8)

## 📋 Usage Guide

### For Administrators
1. **Login** with admin credentials
2. **Dashboard**: View system statistics and recent activity
3. **User Management**: Add/edit users and manage roles
4. **GD Management**: Assign cases to SIs and update status
5. **Status Management**: Create custom GD statuses
6. **SQL Panel**: Execute custom queries for analysis

### For Sub-Inspectors (SI)
1. **Login** with SI credentials
2. **Dashboard**: View assigned cases and statistics
3. **Assigned Cases**: Manage assigned GD cases
4. **Update Status**: Change case status and add notes

### For Users
1. **Register** a new account or login with existing credentials
2. **Dashboard**: View personal GD statistics
3. **File New GD**: Submit new General Diary cases
4. **My GDs**: Track all filed cases and their status

## 🔒 Security Features

- **Password Hashing**: All passwords are securely hashed
- **CSRF Protection**: Cross-site request forgery protection
- **SQL Injection Prevention**: Prepared statements used throughout
- **File Upload Security**: File type and size validation
- **Session Management**: Secure session handling with timeout
- **Input Validation**: All user inputs are sanitized and validated
- **Role-Based Access**: Strict access control based on user roles

## 📊 Database Schema

### Main Tables
- **users**: User accounts and roles
- **gds**: General Diary cases
- **gd_statuses**: Available GD statuses
- **files**: Uploaded files linked to GDs
- **admin_notes**: Internal and public notes
- **notifications**: System notifications
- **activity_log**: Audit trail of all actions

## 🚨 Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Ensure MySQL is running in XAMPP
- Check database credentials in `config/db.php`
   - Verify database `online_gd_system` exists

2. **File Upload Issues**:
   - Check `assets/uploads/` directory permissions
   - Verify file size limits in `config/config.php`
   - Ensure PHP upload settings are correct

3. **Session Issues**:
   - Check PHP session configuration
- Verify session timeout settings
   - Clear browser cookies if needed

4. **Permission Errors**:
   - Ensure proper file permissions on upload directory
   - Check XAMPP Apache configuration

### Error Logs
- Check XAMPP error logs: `C:\xampp\apache\logs\error.log`
- Check PHP error logs: `C:\xampp\php\logs\php_error_log`

## 🔄 Updates & Maintenance

### Regular Maintenance
- Clean old activity logs (90+ days)
- Clean old notifications (30+ days)
- Monitor file upload directory size
- Backup database regularly

### Security Updates
- Keep PHP and MySQL updated
- Regularly review user accounts
- Monitor system logs for suspicious activity
- Update passwords periodically

## 📞 Support

For technical support or questions:
1. Check the troubleshooting section above
2. Review error logs for specific issues
3. Ensure all prerequisites are met
4. Verify XAMPP services are running properly

## 📝 License

This project is developed for educational and administrative purposes. Please ensure compliance with local laws and regulations when using this system for official purposes.

---

**Version**: 1.0.0  
**Last Updated**: December 2024  
**Compatibility**: PHP 7.4+, MySQL 5.7+, Bootstrap 5.1+