# Online General Diary System

A comprehensive web-based General Diary (GD) management system with role-based access control for Administrators, Sub-Inspectors (SI), and Citizens.

## 🚀 Features

### Core Features
- **Role-Based Authentication**: Secure login system for Admin, SI, and User roles
- **GD Management**: Complete lifecycle management of General Diary cases
- **File Upload System**: Support for multiple file types with secure storage
- **Real-time Notifications**: Instant updates on case status changes
- **Custom SQL Panel**: Advanced query capabilities for administrators
- **Activity Logging**: Complete audit trail of all system actions
- **Responsive Design**: Works seamlessly on all devices

### Admin Panel
- User management (create, edit, activate/deactivate users)
- GD assignment to Sub-Inspectors
- Status management (CRUD operations)
- Custom SQL query execution with predefined queries
- System notifications management
- Admin notes on GDs
- Comprehensive dashboard with statistics

### SI Dashboard
- View assigned GDs
- Update case status
- Add investigation notes
- Track case progress

### User Portal
- File new GDs with detailed forms
- Upload supporting documents
- Track GD status and progress
- View notifications and updates
- Access case history

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript, jQuery
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Security**: Password hashing, CSRF protection, SQL injection prevention
- **File Handling**: Secure file upload with type validation

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.3 or higher
- Web server (Apache/Nginx)
- PHP extensions: PDO, PDO_MySQL, GD, fileinfo

## 🔧 Installation

### 1. Clone/Download the Project
```bash
git clone <repository-url>
# or download and extract the ZIP file
```

### 2. Database Setup

#### Create Database
```sql
CREATE DATABASE online_gd_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Import Database Schema
```bash
mysql -u root -p online_gd_system < database/schema.sql
```

#### Import Sample Data
```bash
mysql -u root -p online_gd_system < database/seed_data.sql
```

### 3. Configuration

#### Database Configuration
Edit `config/db.php` and update the database credentials:

```php
private $host = 'localhost';
private $db_name = 'online_gd_system';
private $username = 'your_username';
private $password = 'your_password';
```

#### Application Configuration
Edit `config/config.php` and update the application settings:

```php
define('APP_URL', 'http://localhost/your-project-path');
```

#### File Upload Directory
Create the uploads directory and set proper permissions:

```bash
mkdir -p assets/uploads
chmod 755 assets/uploads
```

### 4. Web Server Configuration

#### Apache (.htaccess)
Create a `.htaccess` file in the project root:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

#### Nginx
Add the following to your Nginx configuration:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

## 👥 Default User Accounts

After importing the seed data, you can login with these accounts:

### Admin Account
- **Email**: admin@gd.com
- **Password**: password123
- **Access**: Full administrative privileges

### Sub-Inspector Account
- **Email**: sarah.si@gd.com
- **Password**: password123
- **Access**: SI dashboard and assigned GDs

### User Account
- **Email**: rahim.user@gd.com
- **Password**: password123
- **Access**: User portal for filing GDs

## 📁 Project Structure

```
Online-General-Diary/
├── config/
│   ├── db.php                 # Database connection
│   └── config.php             # Application configuration
├── includes/
│   ├── auth.php               # Authentication functions
│   ├── functions.php          # Utility functions
│   └── security.php           # Security functions
├── assets/
│   ├── css/                   # CSS files
│   ├── js/                    # JavaScript files
│   └── uploads/               # File upload directory
├── admin/
│   ├── dashboard.php          # Admin dashboard
│   ├── users.php              # User management
│   ├── gd_management.php     # GD management
│   ├── status_management.php # Status management
│   ├── sql_panel.php          # SQL query panel
│   └── notifications.php      # Notifications
├── si/
│   ├── dashboard.php          # SI dashboard
│   ├── assigned_gds.php       # Assigned GDs
│   └── gd_details.php         # GD details
├── user/
│   ├── dashboard.php          # User dashboard
│   ├── file_gd.php           # File new GD
│   ├── my_gds.php            # User's GDs
│   └── notifications.php      # Notifications
├── auth/
│   ├── login.php              # Login page
│   ├── register.php           # Registration
│   └── logout.php             # Logout handler
├── api/
│   ├── file_upload.php        # File upload API
│   └── notifications.php      # Notifications API
├── database/
│   ├── schema.sql             # Database schema
│   └── seed_data.sql          # Sample data
├── index.php                  # Landing page
├── header.php                 # Common header
├── footer.php                 # Common footer
└── README.md                  # This file
```

## 🔒 Security Features

### Authentication & Authorization
- Password hashing using PHP's `password_hash()`
- Session management with timeout
- Role-based access control
- CSRF token protection

### Input Validation
- SQL injection prevention using prepared statements
- XSS protection with input sanitization
- File upload validation and type checking
- Rate limiting for login attempts

### Data Protection
- Secure file storage outside web root
- Unique filename generation
- File type and size validation
- Activity logging for audit trails

## 📊 Database Schema

### Core Tables
- **users**: User accounts and profiles
- **gds**: General Diary records
- **gd_statuses**: Status definitions
- **files**: Uploaded file metadata
- **admin_notes**: Admin notes on GDs
- **notifications**: System notifications
- **activity_log**: System activity tracking

### Key Relationships
- Users can file multiple GDs
- GDs can be assigned to SIs
- GDs can have multiple files and notes
- All actions are logged for audit purposes

## 🚀 Usage Guide

### For Administrators
1. **Login** with admin credentials
2. **Manage Users**: Create, edit, or deactivate user accounts
3. **Assign GDs**: Assign cases to Sub-Inspectors
4. **Monitor System**: View statistics and activity logs
5. **Execute Queries**: Use the SQL panel for advanced reporting

### For Sub-Inspectors
1. **Login** with SI credentials
2. **View Assigned Cases**: Check your assigned GDs
3. **Update Status**: Change case status as investigation progresses
4. **Add Notes**: Document investigation findings

### For Users
1. **Register** for a new account or login
2. **File New GD**: Complete the detailed form
3. **Upload Documents**: Attach supporting files
4. **Track Progress**: Monitor case status updates
5. **Receive Notifications**: Get updates on case progress

## 🔧 Customization

### Adding New Status Types
1. Go to Admin Panel → Status Management
2. Click "Add New Status"
3. Define status name and description
4. Status will be available for GD assignment

### Modifying File Upload Limits
Edit `config/config.php`:
```php
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
```

### Customizing Email Notifications
Update email settings in `config/config.php`:
```php
define('SMTP_HOST', 'your-smtp-host');
define('SMTP_USERNAME', 'your-username');
define('SMTP_PASSWORD', 'your-password');
```

## 🐛 Troubleshooting

### Common Issues

#### Database Connection Error
- Check database credentials in `config/db.php`
- Ensure MySQL service is running
- Verify database exists and user has proper permissions

#### File Upload Issues
- Check `assets/uploads/` directory permissions (755)
- Verify PHP upload limits in `php.ini`
- Check file type restrictions in `config/config.php`

#### Session Issues
- Ensure session directory is writable
- Check session configuration in `php.ini`
- Verify session timeout settings

#### Permission Errors
- Check file and directory permissions
- Ensure web server has read access to all files
- Verify write permissions for uploads directory

### Debug Mode
Enable debug mode in `config/config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📈 Performance Optimization

### Database Optimization
- Regular database maintenance and optimization
- Proper indexing on frequently queried columns
- Query optimization for large datasets

### File Storage
- Implement file compression for large files
- Use CDN for static assets
- Regular cleanup of old files

### Caching
- Implement Redis/Memcached for session storage
- Use browser caching for static assets
- Database query result caching

## 🔄 Backup & Maintenance

### Database Backup
```bash
mysqldump -u username -p online_gd_system > backup.sql
```

### File Backup
```bash
tar -czf uploads_backup.tar.gz assets/uploads/
```

### Regular Maintenance
- Clean old activity logs (older than 90 days)
- Remove old notifications (older than 30 days)
- Optimize database tables
- Update system dependencies

## 📝 License

This project is licensed under the MIT License. See the LICENSE file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📞 Support

For support and questions:
- Create an issue in the repository
- Check the troubleshooting section
- Review the documentation

## 🔮 Future Enhancements

- Mobile app development
- Advanced reporting and analytics
- Email/SMS notifications
- Multi-language support
- API for third-party integrations
- Advanced file management
- Case priority system
- Automated status updates

---

**Note**: This system is designed for educational and demonstration purposes. For production use, additional security measures and testing are recommended.