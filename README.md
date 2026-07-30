# GuideSched - Guidance Counseling Appointment System

A comprehensive web-based appointment scheduling system for university guidance counseling services.

## 🎯 Features

### ✅ Phase 1: Foundation (COMPLETED)
- **Database Schema**: Complete MySQL database with normalized tables
- **Authentication System**: Secure login with role-based access control
- **User Roles**: Student, Admin, and Counselor roles
- **Student Registration**: Complete registration flow with profile creation
- **Admin/Counselor Management**: Interface to add and manage counselors
- **Landing Page**: Professional homepage with system information
- **Session Management**: Secure session handling with timeout

### ✅ Phase 2: Student Portal (COMPLETED)
- **Student Dashboard**: Real-time statistics and quick actions
- **Profile Management**: Personal and academic information updates
- **Appointment Booking**: Multi-step booking system with counselor selection
- **Appointment History**: View and filter all appointments
- **Notification System**: Real-time notifications for appointment updates
- **Cancellation**: Cancel pending/approved appointments

### ✅ Phase 3: Admin Portal (COMPLETED)
- **Admin Dashboard**: Comprehensive statistics and pending request alerts
- **Appointment Management**: Approve, decline, complete, and manage appointments
- **Schedule Management**: Create and manage counselor availability slots
- **Student Management**: Search, view profiles, and appointment history
- **Counselor Management**: Add, activate, and deactivate counselors

### ✅ Phase 4: Analytics (COMPLETED)
- **Statistics Dashboard**: Key performance indicators and metrics
- **Chart.js Integration**: Visual charts for appointment status and trends
- **Trend Analysis**: Monthly appointment tracking
- **No-Show Tracking**: Monitor and analyze no-show rates
- **Report Generation**: Filter and export reports (CSV/PDF)

### ✅ Phase 5: Finalization (COMPLETED)
- **Responsive Design**: Mobile-friendly Bootstrap 5 interface
- **Security Features**: CSRF protection, rate limiting, input validation
- **System Testing**: Comprehensive test suite for all components
- **Deployment Documentation**: Complete setup and maintenance guide

## 🗄️ Database Structure

- `users` - User accounts and authentication
- `student_profiles` - Student-specific information
- `counselor_profiles` - Counselor-specific information
- `appointments` - Appointment records
- `availability` - Counselor availability slots
- `notifications` - User notifications
- `appointment_history` - Audit trail for appointments
- `reports` - Generated reports storage

## 🚀 Installation Instructions

### Prerequisites
- XAMPP (or equivalent PHP/MySQL environment)
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Modern web browser (Chrome, Firefox, Safari, Edge)

### Installation Steps

1. **Place the files**:
   - Copy the entire project folder to `C:\xampp\htdocs\APPOINTMENT IN GUIDANCE\`

2. **Start XAMPP**:
   - Open XAMPP Control Panel
   - Start Apache server
   - Start MySQL database

3. **Run system tests** (optional but recommended):
   - Open your browser and go to: `http://localhost/APPOINTMENT%20IN%20GUIDANCE/test_system.php`
   - Review the test results to ensure all components are working

4. **Set up the database**:
   - Open your browser and go to: `http://localhost/APPOINTMENT%20IN%20GUIDANCE/setup_database.php`
   - This will create the database and all required tables
   - Default credentials will be created automatically

5. **Access the application**:
   - Landing page: `http://localhost/APPOINTMENT%20IN%20GUIDANCE/`
   - Student login: `http://localhost/APPOINTMENT%20IN%20GUIDANCE/login.php`
   - Student registration: `http://localhost/APPOINTMENT%20IN%20GUIDANCE/register.php`

### Default Login Credentials

**Admin Account:**
- Email: `admin@guidesched.com`
- Password: `admin123`

**Counselor Account:**
- Email: `maria.santos@guidesched.com`
- Password: `counselor123`

⚠️ **Important**: Change these default passwords immediately after first login!

## 📁 Project Structure

```
APPOINTMENT IN GUIDANCE/
├── admin/                      # Admin & Counselor Portal
│   ├── dashboard.php          # Admin dashboard with statistics
│   ├── appointments.php       # Appointment management
│   ├── schedule.php           # Schedule management
│   ├── students.php           # Student management
│   ├── counselors.php         # Counselor management
│   ├── add-counselor.php      # Add new counselor
│   ├── analytics.php          # Analytics and charts
│   └── reports.php            # Report generation
├── student/                   # Student Portal
│   ├── dashboard.php          # Student dashboard
│   ├── profile.php            # Profile management
│   ├── book-appointment.php   # Appointment booking
│   ├── appointments.php       # Appointment history
│   └── notifications.php      # Notifications
├── config/                    # Configuration files
│   ├── config.php            # Application configuration
│   └── database.php          # Database connection settings
├── includes/                  # Reusable functions
│   ├── auth_functions.php    # Authentication functions
│   ├── appointment_functions.php # Appointment operations
│   ├── admin_functions.php   # Admin-specific functions
│   └── security_functions.php # Security validation
├── database/                  # Database files
│   └── schema.sql            # Database schema
├── uploads/                   # File uploads directory
├── .htaccess                  # Security and server configuration
├── index.php                  # Landing page
├── login.php                  # Login page
├── register.php              # Student registration
├── logout.php                # Logout handler
├── setup_database.php        # Database setup script
├── test_system.php           # System testing script
└── README.md                 # This file
```

## 🎨 Design Features

- **Modern UI**: Clean, professional interface using Bootstrap 5
- **Responsive Design**: Mobile-friendly layout with adaptive components
- **Color Scheme**: Soft blue/purple gradient theme (#667eea to #764ba2)
- **Accessibility**: Semantic HTML, proper contrast ratios, keyboard navigation
- **Typography**: Clear, readable fonts (Segoe UI, system fonts)
- **Iconography**: Font Awesome 6 for consistent iconography
- **Card-based Layout**: Organized content in rounded cards with shadows

## 🔒 Security Features

- **Password Hashing**: Uses PHP's `password_hash()` with BCRYPT algorithm
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Input sanitization and output encoding
- **CSRF Protection**: Token-based protection for all forms
- **Session Management**: Secure session handling with timeout (1 hour)
- **Rate Limiting**: Login attempt limiting (5 attempts per 5 minutes)
- **Security Headers**: X-Frame-Options, X-XSS-Protection, HSTS
- **Input Validation**: Email, phone, student number format validation
- **Password Strength**: Enforced strong password requirements
- **File Upload Validation**: Type and size validation for uploads
- **Security Logging**: Event logging for security incidents

## 🛠️ Technologies Used

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend**: PHP 8+
- **Database**: MySQL 5.7+
- **Icons**: Font Awesome 6
- **Charts**: Chart.js
- **Security**: Custom security functions

## 📝 Configuration

### Database Configuration
Edit `config/database.php` to change database settings:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'guidesched');
```

### Application Configuration
Edit `config/config.php` to change application settings:
```php
define('BASE_URL', 'http://localhost/APPOINTMENT%20IN%20GUIDANCE/');
define('SITE_NAME', 'GuideSched');
define('SESSION_TIMEOUT', 3600); // 1 hour
```

### Security Configuration
Security settings in `includes/security_functions.php`:
- Rate limiting limits
- Password requirements
- File upload restrictions
- CSRF token configuration

## 🔧 Maintenance

### Regular Tasks
1. **Database Backups**: Regular MySQL backups using phpMyAdmin or command line
2. **Log Review**: Check security.log for suspicious activity
3. **User Management**: Review and deactivate inactive accounts
4. **System Updates**: Keep PHP and MySQL updated
5. **File Cleanup**: Remove old rate limit files and temporary files

### Troubleshooting

**Database Connection Issues**:
- Verify MySQL is running in XAMPP
- Check database credentials in config/database.php
- Ensure database exists and user has proper permissions

**Session Timeout Issues**:
- Check SESSION_TIMEOUT in config/config.php
- Verify browser cookies are enabled
- Clear browser cache and cookies

**File Upload Issues**:
- Verify uploads directory exists and is writable
- Check PHP upload_max_filesize and post_max_size settings
- Review file type restrictions in security_functions.php

**CSRF Token Errors**:
- Clear browser cookies
- Check server time synchronization
- Verify security_functions.php is properly included

## 📊 System Testing

Run the system test script to verify installation:
```
http://localhost/APPOINTMENT%20IN%20GUIDANCE/test_system.php
```

The test script checks:
- Database connectivity
- Configuration files
- Required directories
- Key pages existence
- Security features
- Helper functions

## 🆘 Support

For technical support or issues:
- **Email**: guidance@university.edu
- **In-Person**: Visit the Guidance Office
- **Documentation**: Refer to this README and inline code comments

## 🔄 Updates & Improvements

Future enhancement suggestions:
- Email notifications for appointments
- Calendar integration (Google Calendar, Outlook)
- Mobile app development
- Advanced reporting features
- Multi-language support
- SMS notifications
- Video conferencing integration

## 📄 License

This project is developed for educational purposes for university guidance counseling services.

---

**Version**: 2.0 (Complete System)
**Last Updated**: 2026-07-30
**Status**: ✅ All Phases Complete
**Development**: Devin AI Assistant
