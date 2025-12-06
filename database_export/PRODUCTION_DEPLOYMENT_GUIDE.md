# NYALIFE HMS PRODUCTION DEPLOYMENT GUIDE

## Overview
This guide provides step-by-step instructions for deploying the Nyalife HMS system to your production hosting server.

## Prerequisites
- **Server Requirements:**
  - PHP 7.4 or higher
  - MySQL 5.7+ or MariaDB 10.4+
  - Apache/Nginx web server
  - SSL certificate (recommended for production)
  - Minimum 2GB RAM
  - Minimum 20GB storage

- **Database Access:**
  - Database creation privileges
  - User management privileges
  - Backup and restore capabilities

## Pre-Deployment Checklist
- [ ] Backup existing database (if any)
- [ ] Verify server meets requirements
- [ ] Prepare production database credentials
- [ ] Test migration script on staging environment
- [ ] Plan maintenance window

## Step 1: Database Migration

### 1.1 Upload Migration Script
Upload the `production_migration_complete.sql` file to your server.

### 1.2 Run Migration Script
```bash
# Option 1: Command line (recommended)
mysql -u your_username -p your_database_name < production_migration_complete.sql

# Option 2: phpMyAdmin
# - Import the SQL file through phpMyAdmin interface
# - Ensure "SQL compatibility mode" is set to "NONE"
```

### 1.3 Verify Migration
The script will automatically run verification queries. Ensure all tables are created successfully.

## Step 2: Application Configuration

### 2.1 Update Database Configuration
Update your production configuration files with the correct database credentials:

```php
// config/database.php or .env file
define('DB_HOST', 'your_production_db_host');
define('DB_NAME', 'nyalifew_hms_prod');
define('DB_USER', 'your_production_db_user');
define('DB_PASS', 'your_secure_password');
```

### 2.2 Environment Configuration
Set production environment variables:
```php
define('APP_ENV', 'production');
define('APP_DEBUG', false);
define('APP_URL', 'https://yourdomain.com');
```

## Step 3: File Upload and Permissions

### 3.1 Upload Application Files
Upload all application files to your web server directory.

### 3.2 Set File Permissions
```bash
# Set proper permissions for web server
chmod 755 /path/to/your/application
chmod 644 /path/to/your/application/*.php
chmod 755 /path/to/your/application/uploads/
chmod 755 /path/to/your/application/logs/
```

### 3.3 Configure Web Server
Ensure your web server is configured to:
- Serve PHP files correctly
- Handle .htaccess files (Apache)
- Set proper document root

## Step 4: Security Configuration

### 4.1 SSL/HTTPS Setup
- Install SSL certificate
- Force HTTPS redirects
- Update application URLs

### 4.2 Database Security
- Use strong, unique passwords
- Limit database user privileges
- Enable database logging
- Regular security updates

### 4.3 Application Security
- Disable error reporting in production
- Enable logging
- Set secure session configuration
- Implement rate limiting

## Step 5: Testing and Verification

### 5.1 Functional Testing
- [ ] User login/logout
- [ ] Patient registration
- [ ] Appointment booking
- [ ] Consultation creation
- [ ] Report generation

### 5.2 Performance Testing
- [ ] Page load times
- [ ] Database query performance
- [ ] Concurrent user handling
- [ ] File upload functionality

### 5.3 Security Testing
- [ ] SQL injection prevention
- [ ] XSS protection
- [ ] CSRF protection
- [ ] Authentication bypass attempts

## Step 6: Post-Deployment

### 6.1 Monitoring Setup
- Database performance monitoring
- Application error logging
- Server resource monitoring
- Uptime monitoring

### 6.2 Backup Configuration
- Automated database backups
- File system backups
- Backup verification
- Disaster recovery plan

### 6.3 User Training
- Admin user setup
- Staff training
- Documentation distribution
- Support contact information

## Troubleshooting

### Common Issues

#### Database Connection Errors
- Verify database credentials
- Check database server status
- Ensure proper network access
- Verify database user privileges

#### Permission Errors
- Check file permissions
- Verify web server user
- Check directory ownership
- Review .htaccess configuration

#### Performance Issues
- Optimize database queries
- Enable caching
- Review server resources
- Check for memory leaks

## Support and Maintenance

### Regular Maintenance
- Weekly database backups
- Monthly security updates
- Quarterly performance reviews
- Annual system audits

### Emergency Procedures
- Database corruption recovery
- Server failure procedures
- Data breach response
- System rollback procedures

## Contact Information
For technical support or questions:
- Email: support@nyalife.com
- Phone: +254 XXX XXX XXX
- Documentation: https://docs.nyalife.com

---

**Important:** Always test migration scripts in a staging environment before running them in production. Keep regular backups and have a rollback plan ready.
