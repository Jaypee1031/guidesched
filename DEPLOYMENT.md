# GuideSched Deployment Guide

This guide provides detailed instructions for deploying the GuideSched system in a production environment.

## 📋 Pre-Deployment Checklist

### Server Requirements
- [ ] PHP 8.0 or higher
- [ ] MySQL 5.7 or higher
- [ ] Apache web server with mod_rewrite enabled
- [ ] SSL certificate for HTTPS
- [ ] Sufficient disk space (recommended: 1GB+)
- [ ] Regular backup capability

### Security Checklist
- [ ] Change all default passwords
- [ ] Configure SSL/HTTPS
- [ ] Set proper file permissions
- [ ] Configure firewall rules
- [ ] Enable security headers
- [ ] Set up regular backups
- [ ] Configure error logging
- [ ] Review and adjust security settings

## 🚀 Deployment Steps

### 1. Server Setup

#### XAMPP Deployment (Development/Staging)
```bash
# Copy files to XAMPP htdocs
Copy project to: C:\xampp\htdocs\guidesched\

# Configure Apache
# Ensure mod_rewrite is enabled in httpd.conf
LoadModule rewrite_module modules/mod_rewrite.so

# Configure PHP
# Edit php.ini to enable necessary extensions
extension=mysqli
extension=gd
extension=fileinfo
```

#### Production Server Deployment
```bash
# Upload files to production server
# Using FTP/SFTP or git deployment

# Set file permissions
chmod 755 /var/www/html/guidesched
chmod 644 /var/www/html/guidesched/*.php
chmod 755 /var/www/html/guidesched/includes
chmod 644 /var/www/html/guidesched/includes/*.php
chmod 755 /var/www/html/guidesched/uploads
chmod 755 /var/www/html/guidesched/config
chmod 600 /var/www/html/guidesched/config/*.php
```

### 2. Database Configuration

#### Create Production Database
```sql
-- Create database user with limited privileges
CREATE USER 'guidesched_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX ON guidesched.* TO 'guidesched_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Update Configuration
```php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_USER', 'guidesched_user');
define('DB_PASS', 'strong_password_here');
define('DB_NAME', 'guidesched');
```

### 3. Security Configuration

#### Update .htaccess for Production
```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "DENY"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
    Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;"
</IfModule>
```

#### Update Application Configuration
```php
// config/config.php
define('BASE_URL', 'https://yourdomain.com/guidesched/');
define('SITE_NAME', 'GuideSched');
define('SESSION_TIMEOUT', 3600); // 1 hour

// Production settings
error_reporting(0); // Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');
```

### 4. SSL Configuration

#### Obtain SSL Certificate
- Use Let's Encrypt (free)
- Purchase from certificate authority
- Configure in Apache

#### Apache SSL Configuration
```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/html/guidesched
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLCertificateChainFile /path/to/chain.crt
    
    # SSL security settings
    SSLProtocol all -SSLv2 -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:!aNULL:!MD5:!3DES
    SSLHonorCipherOrder on
</VirtualHost>
```

### 5. Database Setup

#### Import Schema
```bash
# Via command line
mysql -u guidesched_user -p guidesched < database/schema.sql

# Or via phpMyAdmin
# Import the schema.sql file
```

#### Create Default Admin
```sql
-- Update default admin password
UPDATE users 
SET password = '$2y$10$yourhashedpasswordhere' 
WHERE email = 'admin@guidesched.com';
```

### 6. Testing

#### Run System Tests
```bash
# Access test script
https://yourdomain.com/guidesched/test_system.php
```

#### Manual Testing Checklist
- [ ] Landing page loads correctly
- [ ] Student registration works
- [ ] Student login works
- [ ] Admin login works
- [ ] Appointment booking works
- [ ] Admin appointment management works
- [ ] Notifications work
- [ ] File uploads work (if enabled)
- [ ] Responsive design works on mobile
- [ ] All pages load with HTTPS

## 🔒 Security Hardening

### File Permissions
```bash
# Set restrictive permissions
find /var/www/html/guidesched -type f -exec chmod 644 {} \;
find /var/www/html/guidesched -type d -exec chmod 755 {} \;
chmod 600 /var/www/html/guidesched/config/*.php
chmod 600 /var/www/html/guidesched/.htaccess
```

### Disable Directory Browsing
```apache
# Already in .htaccess
Options -Indexes
```

### Protect Sensitive Files
```apache
# Already in .htaccess
<FilesMatch "^\.(htaccess|htpasswd|log|json)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### Configure Firewall
```bash
# Allow only necessary ports
# 80 (HTTP), 443 (HTTPS), 3306 (MySQL - local only)
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

## 📊 Monitoring & Maintenance

### Log Monitoring
```bash
# Monitor Apache logs
tail -f /var/log/apache2/error.log
tail -f /var/log/apache2/access.log

# Monitor application logs
tail -f /var/www/html/guidesched/security.log
tail -f /var/www/html/guidesched/error.log
```

### Database Maintenance
```sql
-- Regular optimization
OPTIMIZE TABLE users;
OPTIMIZE TABLE appointments;
OPTIMIZE TABLE notifications;

-- Check for issues
CHECK TABLE users;
ANALYZE TABLE appointments;
```

### Backup Strategy
```bash
# Daily database backup
mysqldump -u guidesched_user -p guidesched > backup_$(date +%Y%m%d).sql

# Weekly file backup
tar -czf guidesched_backup_$(date +%Y%m%d).tar.gz /var/www/html/guidesched

# Store backups offsite
# Use cloud storage or external backup service
```

## 🔄 Updates & Upgrades

### Update Procedure
1. Backup current installation
2. Test updates in staging environment
3. Update database schema if needed
4. Update application files
5. Test thoroughly
6. Deploy to production
7. Monitor for issues

### Rollback Procedure
1. Stop web server
2. Restore database from backup
3. Restore application files from backup
4. Restart web server
5. Verify functionality

## 🆘 Troubleshooting

### Common Issues

#### 500 Internal Server Error
- Check .htaccess syntax
- Verify file permissions
- Review Apache error logs
- Check PHP syntax

#### Database Connection Failed
- Verify MySQL is running
- Check database credentials
- Ensure database exists
- Review MySQL logs

#### Session Issues
- Check PHP session configuration
- Verify session directory permissions
- Clear browser cookies
- Check session timeout settings

#### File Upload Issues
- Verify uploads directory permissions
- Check PHP upload settings
- Review file size limits
- Verify disk space

## 📞 Support Contact

For deployment issues:
- **Technical Support**: guidance@university.edu
- **System Administrator**: [Contact Info]
- **Emergency**: [Emergency Contact]

---

**Document Version**: 1.0
**Last Updated**: 2026-07-30
**Next Review**: 2026-10-30