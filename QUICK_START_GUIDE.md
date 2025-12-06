# Quick Start Guide - Development Tools

## ✅ All Dependencies Successfully Installed!

Your development environment is now equipped with powerful tools for:
- Database management and migrations
- Static code analysis
- Code quality assessment
- Security vulnerability detection
- Deployment readiness checks

## 📋 Installed Tools Quick Reference

| Tool | Command | Purpose |
|------|---------|---------|
| **PHPStan** | `vendor/bin/phpstan analyze` | Static analysis - find bugs before runtime |
| **PHP_CodeSniffer** | `vendor/bin/phpcs` | Enforce coding standards (PSR-12, etc.) |
| **PHP CS Fixer** | `vendor/bin/php-cs-fixer fix` | Auto-fix code style issues |
| **PHPMD** | `vendor/bin/phpmd` | Detect code smells and complexity |
| **PHPMD (Duplication)** | `vendor/bin/phpmd includes/ text unusedcode` | Find duplicated code (via PHPMD) |
| **PhpMetrics** | `vendor/bin/phpmetrics` | Generate code quality reports |
| **Phinx** | `vendor/bin/phinx` | Database migrations |
| **Doctrine DBAL** | Use programmatically | Database abstraction & schema tools |
| **Composer Audit** | `composer audit` | Check for security vulnerabilities |

## 🚀 Quick Commands

### 1. Check Your Code Quality
```bash
# Run all quality checks
vendor/bin/phpstan analyze includes/ --level=5
vendor/bin/phpcs --standard=PSR12 includes/
vendor/bin/phpmd includes/ text cleancode,codesize,design,unusedcode,duplicatedcode
```

### 2. Auto-Fix Code Style
```bash
vendor/bin/php-cs-fixer fix includes/
```

### 3. Check Security
```bash
composer audit
```

### 4. Generate Quality Report
```bash
vendor/bin/phpmetrics --report-html=metrics_report includes/
# Open metrics_report/index.html in browser
```

### 5. Database Migration (after setup)
```bash
vendor/bin/phinx init
vendor/bin/phinx migrate
```

## 📝 Setup Configuration Files

### PHPStan Configuration (`phpstan.neon`)
Create this file in project root:
```yaml
parameters:
    level: 5
    paths:
        - includes/
    excludePaths:
        - includes/vendor/
    ignoreErrors:
        - '#Class .* located in .* does not comply with psr-4#'
```

### PHP_CodeSniffer Configuration (`.phpcs.xml`)
```xml
<?xml version="1.0"?>
<ruleset name="Nyalife HMS">
    <description>Coding standards for Nyalife HMS</description>
    <file>includes/</file>
    <exclude-pattern>*/vendor/*</exclude-pattern>
    <rule ref="PSR12"/>
</ruleset>
```

### PHP CS Fixer Configuration (`.php-cs-fixer.php`)
```php
<?php
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/includes')
    ->exclude('vendor');

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
```

## 🔍 Database Schema Validation

### Using Doctrine DBAL
```php
<?php
require 'vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

$connectionParams = [
    'dbname' => 'your_db',
    'user' => 'your_user',
    'password' => 'your_password',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
];

$conn = DriverManager::getConnection($connectionParams);
$schemaManager = $conn->createSchemaManager();

// List all tables
$tables = $schemaManager->listTables();

// Get table structure
$table = $schemaManager->introspectTable('your_table');
$columns = $table->getColumns();
```

### Using Phinx Migrations
```bash
# Initialize Phinx
vendor/bin/phinx init

# Create a migration
vendor/bin/phinx create AddNewColumn

# Run migrations
vendor/bin/phinx migrate

# Rollback
vendor/bin/phinx rollback
```

## 📊 Deployment Readiness Checklist

Run these before deployment:

1. ✅ **Security Check**: `composer audit`
2. ✅ **Static Analysis**: `vendor/bin/phpstan analyze`
3. ✅ **Code Style**: `vendor/bin/phpcs --standard=PSR12`
4. ✅ **Code Smells**: `vendor/bin/phpmd includes/ text`
5. ✅ **Duplication**: `vendor/bin/phpmd includes/ text duplicatedcode`
6. ✅ **Metrics**: `vendor/bin/phpmetrics --report-html=report`
7. ✅ **PHP Compatibility**: Check with phpcompatibility
8. ✅ **Database**: Verify schema with Doctrine DBAL

## 💡 Integration with Composer Scripts

Add to your `composer.json`:
```json
"scripts": {
    "quality": [
        "@quality:analyse",
        "@quality:style",
        "@quality:security"
    ],
    "quality:analyse": "phpstan analyze includes/ --level=5",
    "quality:style": "phpcs --standard=PSR12 includes/",
    "quality:fix": "php-cs-fixer fix includes/",
    "quality:smells": "phpmd includes/ text cleancode,codesize,design",
    "quality:duplicate": "phpmd includes/ text duplicatedcode",
    "quality:metrics": "phpmetrics --report-html=metrics_report includes/",
    "quality:security": "composer audit"
}
```

Then run: `composer run quality`

## 🎯 Next Steps

1. Create configuration files (see above)
2. Run initial analysis: `composer run quality`
3. Fix identified issues
4. Set up CI/CD pipeline with these tools
5. Schedule regular security audits

All tools are ready to use! 🎉

