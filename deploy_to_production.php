<?php
/**
 * Nyalife HMS - Production Deployment Script
 * 
 * This script prepares your system for production deployment
 * when you can't run Composer on the hosting platform.
 */

echo "=== NYALIFE HMS PRODUCTION DEPLOYMENT PREPARATION ===\n\n";

// Configuration
$sourceDir = __DIR__;
$deployDir = __DIR__ . '/deployment_package';
$excludeDirs = [
    '.git',
    '.vscode',
    'logs',
    'uploads',
    'DB',
    'database',
    'Schema',
    'vendor',
    'node_modules',
    'tests',
    'deployment_package'
];

$excludeFiles = [
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
    '.env',
    '.env.local',
    '.env.development',
    'env.production',
    'deploy_to_production.php',
    'check_database_structure.php',
    'cookies.txt',
    'nurse_cookies.txt',
    'README.md',
    '.gitignore'
];

echo "🔧 PREPARING DEPLOYMENT PACKAGE...\n\n";

// Create deployment directory
if (!is_dir($deployDir)) {
    mkdir($deployDir, 0755, true);
    echo "✅ Created deployment directory: $deployDir\n";
} else {
    echo "✅ Deployment directory already exists\n";
}

// Copy essential files and directories
$essentialDirs = [
    'includes',
    'assets',
    'api',
    'public',
    'config'
];

$essentialFiles = [
    'index.php',
    'config.php',
    'constants.php',
    'functions.php',
    'validation_functions.php',
    'id_generator.php',
    'modal_functions.php',
    '.htaccess'
];

echo "\n📁 COPYING ESSENTIAL DIRECTORIES:\n";
foreach ($essentialDirs as $dir) {
    if (is_dir($sourceDir . '/' . $dir)) {
        $destDir = $deployDir . '/' . $dir;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        
        // Copy directory contents recursively
        copyDirectory($sourceDir . '/' . $dir, $destDir);
        echo "✅ Copied: $dir\n";
    }
}

echo "\n📄 COPYING ESSENTIAL FILES:\n";
foreach ($essentialFiles as $file) {
    if (file_exists($sourceDir . '/' . $file)) {
        copy($sourceDir . '/' . $file, $deployDir . '/' . $file);
        echo "✅ Copied: $file\n";
    }
}

// Copy vendor dependencies (since you can't run composer on hosting)
echo "\n📦 COPYING VENDOR DEPENDENCIES:\n";
if (is_dir($sourceDir . '/vendor')) {
    $vendorDest = $deployDir . '/vendor';
    if (!is_dir($vendorDest)) {
        mkdir($vendorDest, 0755, true);
    }
    copyDirectory($sourceDir . '/vendor', $vendorDest);
    echo "✅ Copied: vendor dependencies\n";
}

// Create production configuration
echo "\n⚙️ CREATING PRODUCTION CONFIGURATION:\n";
createProductionConfig($deployDir);

// Create deployment instructions
echo "\n📋 CREATING DEPLOYMENT INSTRUCTIONS:\n";
createDeploymentInstructions($deployDir);

// Create file list
echo "\n📝 CREATING FILE LIST:\n";
createFileList($deployDir);

echo "\n=== DEPLOYMENT PACKAGE READY! ===\n";
echo "✅ Location: $deployDir\n";
echo "✅ Size: " . formatBytes(getDirSize($deployDir)) . "\n";
echo "✅ Ready for upload to production server\n\n";

echo "🚀 NEXT STEPS:\n";
echo "1. Upload the 'deployment_package' folder to your hosting\n";
echo "2. Extract/upload files to your web root\n";
echo "3. Update database configuration\n";
echo "4. Set proper file permissions\n";
echo "5. Test the system\n\n";

echo "📚 See 'DEPLOYMENT_INSTRUCTIONS.md' in the package for detailed steps!\n";

/**
 * Copy directory recursively
 */
function copyDirectory($source, $dest) {
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        if ($item->isDir()) {
            mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName(), 0755, true);
        } else {
            copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        }
    }
}

/**
 * Create production configuration
 */
function createProductionConfig($deployDir) {
    $configContent = "<?php
/**
 * Nyalife HMS - Production Configuration
 * 
 * IMPORTANT: Update these values for your production environment
 */

// Database Configuration
define('DB_HOST', 'your_production_db_host');
define('DB_NAME', 'your_production_db_name');
define('DB_USER', 'your_production_db_user');
define('DB_PASS', 'your_production_db_password');

// Application Configuration
define('APP_ENV', 'production');
define('APP_DEBUG', false);
define('APP_URL', 'https://yourdomain.com');

// Security Configuration
define('SESSION_SECURE', true);
define('SESSION_HTTP_ONLY', true);
define('SESSION_SAME_SITE', 'Strict');

// File Upload Configuration
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Error Reporting (disable in production)
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Timezone
date_default_timezone_set('Africa/Nairobi');

echo '<!-- Production configuration loaded -->';
?>";
    
    file_put_contents($deployDir . '/production_config.php', $configContent);
    echo "✅ Created: production_config.php\n";
}

/**
 * Create deployment instructions
 */
function createDeploymentInstructions($deployDir) {
    $instructions = "# Nyalife HMS - Production Deployment Instructions

## 🚀 Quick Deployment Steps

### 1. Upload Files
- Upload the entire `deployment_package` folder to your hosting
- Extract/upload files to your web root directory

### 2. Database Setup
- Create a new MySQL database on your hosting
- Import your database schema (if you have a .sql file)
- Update database credentials in `production_config.php`

### 3. File Permissions
Set these permissions on your hosting:
```bash
# Directories
chmod 755 includes/
chmod 755 assets/
chmod 755 api/
chmod 755 config/

# Files
chmod 644 *.php
chmod 644 .htaccess

# Upload directories (if they exist)
chmod 777 uploads/
chmod 777 logs/
```

### 4. Configuration
- Rename `production_config.php` to `config.php` (or update your existing config)
- Update database connection details
- Set your production domain URL

### 5. Test
- Visit your domain to ensure the system loads
- Check for any error messages
- Test login functionality

## 📁 File Structure
```
/
├── index.php (main entry point)
├── config.php (production configuration)
├── includes/ (core system files)
├── assets/ (CSS, JS, images)
├── api/ (API endpoints)
├── vendor/ (dependencies)
└── .htaccess (URL rewriting)
```

## ⚠️ Important Notes
- **Never** upload development files (.env, composer files, etc.)
- **Always** backup your production database before updates
- **Test** thoroughly in a staging environment first
- **Monitor** error logs after deployment

## 🔧 Troubleshooting
- Check file permissions
- Verify database connection
- Check error logs
- Ensure all required PHP extensions are enabled

## 📞 Support
If you encounter issues, check:
1. PHP error logs
2. Database connection
3. File permissions
4. Required PHP extensions

Good luck with your deployment! 🎉
";
    
    file_put_contents($deployDir . '/DEPLOYMENT_INSTRUCTIONS.md', $instructions);
    echo "✅ Created: DEPLOYMENT_INSTRUCTIONS.md\n";
}

/**
 * Create file list
 */
function createFileList($deployDir) {
    $fileList = "Nyalife HMS - Production Deployment File List\n";
    $fileList .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($deployDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        if ($item->isFile()) {
            $relativePath = str_replace($deployDir . DIRECTORY_SEPARATOR, '', $item->getPathname());
            $size = formatBytes(filesize($item->getPathname()));
            $fileList .= "- $relativePath ($size)\n";
        }
    }
    
    file_put_contents($deployDir . '/FILE_LIST.txt', $fileList);
    echo "✅ Created: FILE_LIST.txt\n";
}

/**
 * Get directory size
 */
function getDirSize($dir) {
    $size = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        if ($item->isFile()) {
            $size += filesize($item->getPathname());
        }
    }
    
    return $size;
}

/**
 * Format bytes to human readable
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
?>
