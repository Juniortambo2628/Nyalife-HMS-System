# Nyalife HMS System - Complete Folder Structure

```
Nyalife-HMS-System/
│
├── api/
│   └── index.php           # API entry point
│
├── assets/
│   ├── css/               # Stylesheets
│   │   ├── components/      # Reusable UI components
│   │   ├── pages/           # Page-specific styles
│   │   └── vendor/          # Third-party CSS
│   │
│   ├── img/              # Image assets
│   │   ├── doctors/         # Doctor profile images
│   │   ├── gallery/         # Gallery images
│   │   ├── hero/            # Hero section images
│   │   ├── illustrations/   # Illustration assets
│   │   ├── logo/            # Application logos
│   │   ├── placeholders/    # Placeholder images
│   │   └── slider/          # Slider images
│   │
│   └── js/               # JavaScript files
│       ├── common/          # Shared utilities
│       ├── components/      # UI components
│       ├── core/            # Core application logic
│       ├── pages/           # Page-specific scripts
│       ├── vendor/          # Third-party JS libraries
│       └── vendors/         # Additional vendor scripts
│
├── config/
│   └── database.php      # Database configuration
│
├── includes/             # Core application files
│   ├── components/         # Reusable UI components
│   │
│   ├── controllers/               # Application controllers
│   │   │
│   │   ├── BaseController.php      # Base controller class
│   │   │
│   │   ├── ajax/                  # AJAX request handlers
│   │   │   └── auth_handler.php     # Authentication AJAX handler
│   │   │
│   │   ├── api/                   # API controllers
│   │   │   ├── ApiController.php       # Base API controller
│   │   │   ├── AppointmentController.php # Appointment API endpoints
│   │   │   ├── CommunicationController.php # Communication API
│   │   │   ├── ConsultationController.php # Consultation API
│   │   │   ├── InsuranceController.php   # Insurance API
│   │   │   ├── LabTestController.php     # Lab test API
│   │   │   ├── MedicationsController.php # Medication management API
│   │   │   └── NotificationsController.php # Notifications API
│   │   │
│   │   └── web/                   # Web controllers
│   │       ├── AppointmentController.php  # Appointment management
│   │       ├── AuthController.php         # Authentication
│   │       ├── ConsultationController.php # Consultation management
│   │       ├── DashboardController.php    # Dashboard
│   │       ├── HomeController.php         # Home page
│   │       ├── LabRequestController.php   # Lab requests
│   │       ├── LabTestController.php      # Lab tests
│   │       ├── PatientController.php      # Patient management
│   │       ├── PrescriptionController.php # Prescriptions
│   │       ├── UserController.php         # User management
│   │       └── WebController.php          # Base web controller
│   │
│   ├── core/             # Core system files
│   ├── data/               # Data handling
│   ├── helpers/            # Helper functions
│   ├── models/             # Data models
│   │   ├── AppointmentModel.php    # Appointment data operations
│   │   ├── BaseModel.php          # Base model class
│   │   ├── ConsultationModel.php  # Consultation data operations
│   │   ├── LabTestModel.php       # Lab test data operations
│   │   ├── MedicationModel.php    # Medication data operations
│   │   ├── PatientModel.php       # Patient data operations
│   │   ├── PrescriptionModel.php  # Prescription data operations
│   │   ├── StaffModel.php         # Staff data operations
│   │   └── UserModel.php          # User authentication and management
│   ├── templates/          # Email/SMS templates
│   ├── utils/              # Utility classes
│   │
│   └── views/                  # View templates
│       │
│       ├── appointments/        # Appointment views
│       │   ├── create.php      # Create new appointment
│       │   ├── index.php       # List all appointments
│       │   └── view.php        # View appointment details
│       │
│       ├── auth/               # Authentication views
│       │   ├── login.php       # Login page
│       │   └── register.php    # User registration
│       │
│       ├── components/        # Reusable view components
│       │
│       ├── consultations/    # Consultation views
│       │   ├── _form.php       # Consultation form (partial)
│       │   ├── create.php      # Create new consultation
│       │   ├── edit.php        # Edit consultation
│       │   ├── index.php       # List consultations
│       │   ├── print.php       # Print consultation
│       │   └── view.php        # View consultation details
│       │
│       ├── dashboard/         # Dashboard views
│       │   ├── admin.php       # Admin dashboard
│       │   ├── default.php     # Default dashboard
│       │   ├── doctor.php      # Doctor dashboard
│       │   ├── lab_technician.php # Lab technician dashboard
│       │   ├── nurse.php       # Nurse dashboard
│       │   ├── patient.php     # Patient dashboard
│       │   └── pharmacist.php  # Pharmacist dashboard
│       │
│       ├── home/              # Home page views
│       │   └── index.php       # Main home page
│       │
│       ├── layouts/           # Layout templates
│       │   ├── default.php     # Default application layout
│       │   └── landing.php     # Landing page layout
│       │
│       ├── patients/          # Patient management views
│       │   ├── create.php      # Create new patient
│       │   ├── index.php       # List all patients
│       │   └── view.php        # View patient details
│       │
│       └── users/             # User management views
│           ├── create.php      # Create new user
│           ├── edit.php        # Edit user
│           ├── index.php       # List all users
│           ├── profile.php     # User profile
│           └── view.php        # View user details
│
│       error.php                # Error page template
│
│   # Core include files
│   ├── alerts.php          # Alert system
│   ├── api_utils.php       # API utilities
│   ├── autoload.php        # Class autoloader
│   ├── config.php          # Application config
│   ├── constants.php       # Application constants
│   ├── date_utils.php      # Date utilities
│   ├── db_utils.php        # Database utilities
│   ├── debug_helper.php    # Debugging helpers
│   ├── error_utils.php     # Error handling
│   ├── functions.php       # Global functions
│   ├── id_generator.php    # ID generation
│   ├── loader.php          # Class loader
│   ├── modal_functions.php # Modal dialogs
│   ├── notification_functions.php # Notification system
│   ├── report_utils.php    # Report generation
│   ├── utils_autoload.php  # Utility autoloader
│   └── validation_functions.php # Validation helpers
│
├── logs/                 # Application logs
│
├── Schema/              # Database schema files
│
├── uploads/             # User-uploaded files
│   ├── documents/         # Patient/Staff documents
│   ├── lab_reports/       # Laboratory reports
│   └── patients/          # Patient-related uploads
│
├── vendor/              # Composer dependencies
│
# Root files
├── .htaccess             # Apache configuration
├── check_schema.php      # Database schema validation
├── CODE_STRUCTURE.md     # Code structure documentation
├── composer.json         # PHP dependencies
├── composer.lock         # Lock file for dependencies
├── FOLDER_STRUCTURE.md   # This file
├── index.php             # Main entry point
└── solution.php          # Development file
```

## Key Files and Their Purposes

### Configuration Files
- `config/database.php`: Database connection settings
- `includes/config.php`: Main application configuration
- `includes/constants.php`: Application-wide constants

### Core Application Files
- `includes/autoload.php`: Handles class autoloading
- `includes/functions.php`: Global helper functions
- `includes/db_utils.php`: Database utilities and helpers

### Entry Points
- `index.php`: Main web entry point
- `api/index.php`: API endpoint

### Important Views
- `includes/views/auth/login.php`: User authentication
- `includes/views/layouts/default.php`: Main application layout
- `includes/views/layouts/landing.php`: Landing page layout

### Asset Management
- `assets/`: Contains all static assets (CSS, JS, images)
- `uploads/`: User-generated content storage

### Logs and Debugging
- `logs/`: Application log files
- `includes/debug_helper.php`: Debugging utilities

This structure follows the MVC pattern with clear separation of concerns between models, views, and controllers, along with proper organization of assets and configuration files.
