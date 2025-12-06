# Nyalife HMS System - Code Structure

## Overview
Nyalife HMS is a comprehensive Hospital Management System built with PHP, MySQL, and modern web technologies. The system follows the MVC (Model-View-Controller) architecture pattern with a clear separation of concerns.

## Directory Structure

### Root Directory
- `index.php` - Main entry point
- `check_schema.php` - Database schema validation
- `solution.php` - Temporary/development file

### API
- `api/` - REST API endpoints
  - `index.php` - API entry point

### Assets
- `assets/` - Frontend assets
  - `css/` - Stylesheets
    - `components/` - Reusable UI components
    - `pages/` - Page-specific styles
    - `vendor/` - Third-party CSS libraries
  - `img/` - Image assets
    - `doctors/` - Doctor profile images
    - `gallery/` - Gallery images
    - `logo/` - Application logos
  - `js/` - JavaScript files
    - `common/` - Shared utilities
    - `components/` - UI components
    - `core/` - Core application logic
    - `pages/` - Page-specific scripts
    - `vendor/` - Third-party JavaScript libraries
    - `vendors/` - Additional vendor scripts

### Config
- `config/` - Configuration files
  - `database.php` - Database connection settings

### Includes
- `includes/` - Core application files
  - `components/` - Reusable UI components
  - `controllers/` - Application controllers
    - `ajax/` - AJAX request handlers
    - `api/` - API controllers
    - `web/` - Web controllers
  - `core/` - Core system files
  - `data/` - Data handling
  - `helpers/` - Helper functions
  - `models/` - Data models
  - `templates/` - Email/SMS templates
  - `utils/` - Utility classes
  - `views/` - View templates
    - `appointments/` - Appointment views
    - `auth/` - Authentication views
    - `components/` - Reusable view components
    - `dashboard/` - Dashboard views
    - `layouts/` - Layout templates
    - `patients/` - Patient management views
    - `users/` - User management views
  - `autoload.php` - Class autoloader
  - `config.php` - Application configuration
  - `constants.php` - Application constants
  - `db_utils.php` - Database utilities
  - `functions.php` - Global functions

### Logs
- `logs/` - Application logs

### Schema
- `Schema/` - Database schema files

### Uploads
- `uploads/` - User-uploaded files
  - `documents/` - Patient/Staff documents
  - `lab_reports/` - Laboratory reports
  - `patients/` - Patient-related uploads

### Vendor
- `vendor/` - Composer dependencies
  - `autoload.php` - Composer autoloader

## Key Components

### Authentication System
- Login/Logout functionality
- Session management
- Role-based access control

### Appointment Management
- Calendar view
- Appointment scheduling
- Status tracking

### Staff Management
- Staff profiles
- Department management
- Role assignment

### Patient Management
- Patient records
- Medical history
- Document management

### API Layer
- RESTful endpoints
- JSON responses
- Authentication/Authorization

## Technical Stack

### Backend
- PHP 7.4+
- MySQL 5.7+
- MVC Architecture
- PDO for database access

### Frontend
- HTML5
- CSS3 (with Bootstrap)
- JavaScript (jQuery)
- AJAX for dynamic content

### Development Tools
- Composer for dependency management
- Git for version control

## Security
- Prepared statements
- Input validation
- CSRF protection
- Password hashing
- Session security

## Error Handling
- Custom error pages
- Logging system
- Debug mode

## Deployment
- XAMPP/LAMP stack compatible
- Environment-based configuration
- Database migration support

## Future Improvements
- Unit testing
- API documentation
- Performance optimization
- Enhanced security measures
