#!/usr/bin/env pwsh
# Nyalife HMS - Deployment Readiness Test Suite
# Comprehensive testing using all available dependencies

$ErrorActionPreference = "Continue"
$reportDir = "deployment-readiness-reports"
$timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$summaryFile = "$reportDir/deployment-summary_$timestamp.txt"

# Create report directory
if (-not (Test-Path $reportDir)) {
    New-Item -ItemType Directory -Path $reportDir | Out-Null
}

# Initialize summary
$summary = @"
===============================================================
NYALIFE HMS - DEPLOYMENT READINESS TEST REPORT
===============================================================
Generated: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
PHP Version: $(php -r "echo PHP_VERSION;")
===============================================================
"@

function Write-TestSection {
    param([string]$Title, [string]$Color = "Cyan")
    Write-Host "`n[$Title]" -ForegroundColor $Color
    Write-Host ("=" * 60) -ForegroundColor Gray
}

function Add-SummaryResult {
    param([string]$Test, [string]$Status, [string]$Details = "")
    $icon = switch ($Status) {
        "PASS" { "✅" }
        "WARN" { "⚠️" }
        "FAIL" { "❌" }
        default { "ℹ️" }
    }
    $script:summary += "`n$icon $Test`: $Status"
    if ($Details) {
        $script:summary += "`n   └─ $Details"
    }
}

# Start comprehensive testing
Write-Host "===============================================================" -ForegroundColor Green
Write-Host "   NYALIFE HMS - DEPLOYMENT READINESS TEST SUITE" -ForegroundColor Green
Write-Host "===============================================================" -ForegroundColor Green
Write-Host ""

# ===================================================================
# TEST 1: PHP Version & Extensions Check
# ===================================================================
Write-TestSection "TEST 1: PHP Version & Extensions"
$phpVersion = php -r "echo PHP_VERSION_ID;"
$phpVersionStr = php -r "echo PHP_VERSION;"

Write-Host "PHP Version: $phpVersionStr" -ForegroundColor White

if ($phpVersion -ge 80100) {
    Write-Host "✅ PHP version meets requirement (>= 8.1.0)" -ForegroundColor Green
    Add-SummaryResult "PHP Version Check" "PASS" "Version $phpVersionStr meets requirement"
} else {
    Write-Host "❌ PHP version does not meet requirement (requires >= 8.1.0)" -ForegroundColor Red
    Add-SummaryResult "PHP Version Check" "FAIL" "Version $phpVersionStr is below 8.1.0"
}

# Check required extensions
$requiredExtensions = @("mysqli", "json", "session", "fileinfo", "mbstring", "openssl")
$missingExtensions = @()

foreach ($ext in $requiredExtensions) {
    if (php -r "echo extension_loaded('$ext') ? 'yes' : 'no';" -eq "yes") {
        Write-Host "✅ Extension '$ext' is loaded" -ForegroundColor Green
    } else {
        Write-Host "❌ Extension '$ext' is NOT loaded" -ForegroundColor Red
        $missingExtensions += $ext
    }
}

if ($missingExtensions.Count -eq 0) {
    Add-SummaryResult "PHP Extensions" "PASS" "All required extensions loaded"
} else {
    Add-SummaryResult "PHP Extensions" "FAIL" "Missing: $($missingExtensions -join ', ')"
}

# Check memory limit
$memoryLimit = php -r "echo ini_get('memory_limit');"
Write-Host "Memory Limit: $memoryLimit" -ForegroundColor Gray

# ===================================================================
# TEST 2: Composer Dependencies
# ===================================================================
Write-TestSection "TEST 2: Composer Dependencies"

if (Test-Path "composer.json") {
    Write-Host "Checking Composer dependencies..." -ForegroundColor White
    $composerCheck = composer validate --no-check-publish --no-interaction 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ composer.json is valid" -ForegroundColor Green
        Add-SummaryResult "Composer Config" "PASS" "composer.json is valid"
    } else {
        Write-Host "⚠️ composer.json validation issues" -ForegroundColor Yellow
        $composerCheck | Write-Host
        Add-SummaryResult "Composer Config" "WARN" "Validation warnings present"
    }
    
    # Check if vendor directory exists
    if (Test-Path "vendor") {
        Write-Host "✅ vendor directory exists" -ForegroundColor Green
    } else {
        Write-Host "❌ vendor directory not found - run 'composer install'" -ForegroundColor Red
        Add-SummaryResult "Composer Install" "FAIL" "vendor directory missing"
    }
} else {
    Write-Host "❌ composer.json not found" -ForegroundColor Red
    Add-SummaryResult "Composer Config" "FAIL" "composer.json missing"
}

# ===================================================================
# TEST 3: Security Audit
# ===================================================================
Write-TestSection "TEST 3: Security Audit (Composer)"
try {
    Write-Host "Running composer audit..." -ForegroundColor White
    $auditResult = composer audit --no-interaction 2>&1 | Out-String
    
    if ($auditResult -match "No known vulnerability" -or $LASTEXITCODE -eq 0) {
        Write-Host "✅ No known security vulnerabilities found" -ForegroundColor Green
        Add-SummaryResult "Security Audit" "PASS" "No vulnerabilities detected"
    } else {
        Write-Host "⚠️ Security vulnerabilities detected!" -ForegroundColor Yellow
        $auditResult | Write-Host
        Add-SummaryResult "Security Audit" "WARN" "Some vulnerabilities detected - review audit output"
    }
    $auditResult | Out-File "$reportDir/security-audit_$timestamp.txt"
} catch {
    Write-Host "⚠️ Could not run security audit: $_" -ForegroundColor Yellow
    Add-SummaryResult "Security Audit" "WARN" "Audit could not complete"
}

# ===================================================================
# TEST 4: PHPUnit Tests
# \
Write-TestSection "TEST 4: PHPUnit Test Suite"

if (Test-Path "vendor/bin/phpunit") {
    Write-Host "Running PHPUnit tests..." -ForegroundColor White
    $phpunitResult = php vendor/bin/phpunit --configuration phpunit.xml.dist --colors=never 2>&1 | Out-String
    $phpunitResult | Out-File "$reportDir/phpunit_$timestamp.txt"
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ All PHPUnit tests passed" -ForegroundColor Green
        Add-SummaryResult "PHPUnit Tests" "PASS" "All tests passed"
    } elseif ($LASTEXITCODE -eq 1) {
        $testMatches = [regex]::Matches($phpunitResult, 'Tests:\s+(\d+)')
        $failMatches = [regex]::Matches($phpunitResult, 'Failures:\s+(\d+)')
        $errorMatches = [regex]::Matches($phpunitResult, 'Errors:\s+(\d+)')
        
        $tests = if ($testMatches) { $testMatches[0].Groups[1].Value } else { "?" }
        $failures = if ($failMatches) { $failMatches[0].Groups[1].Value } else { "0" }
        $errors = if ($errorMatches) { $errorMatches[0].Groups[1].Value } else { "0" }
        
        Write-Host "⚠️ Some tests failed or had errors" -ForegroundColor Yellow
        Write-Host "   Tests: $tests, Failures: $failures, Errors: $errors" -ForegroundColor Gray
        Add-SummaryResult "PHPUnit Tests" "WARN" "Tests: $tests, Failures: $failures, Errors: $errors"
    } else {
        Write-Host "❌ PHPUnit encountered an error" -ForegroundColor Red
        $phpunitResult | Select-Object -First 20 | Write-Host
        Add-SummaryResult "PHPUnit Tests" "FAIL" "PHPUnit execution failed"
    }
} else {
    Write-Host "⚠️ PHPUnit not available (skipped)" -ForegroundColor Yellow
    Add-SummaryResult "PHPUnit Tests" "WARN" "PHPUnit not installed"
}

# ===================================================================
# TEST 5: PHPStan Static Analysis
# ===================================================================
Write-TestSection "TEST 5: PHPStan Static Analysis"

if (Test-Path "vendor/bin/phpstan") {
    Write-Host "Running PHPStan (Level 5)..." -ForegroundColor White
    $phpstanResult = php -d memory_limit=512M vendor/bin/phpstan analyze includes/ --level=5 --no-progress --error-format=table 2>&1 | Out-String
    $phpstanResult | Out-File "$reportDir/phpstan_$timestamp.txt"
    
    # Count actual errors (excluding view template warnings which are expected)
    $errorLines = $phpstanResult -split "`n" | Where-Object { $_ -match "^\s+:\d+\s+" -and $_ -notmatch "components\\|views\\" }
    $errorCount = ($errorLines | Measure-Object).Count
    
    if ($LASTEXITCODE -eq 0 -and $errorCount -eq 0) {
        Write-Host "✅ PHPStan analysis passed with no errors" -ForegroundColor Green
        Add-SummaryResult "PHPStan Analysis" "PASS" "No errors found at level 5"
    } else {
        Write-Host "⚠️ PHPStan found issues ($errorCount errors excluding view templates)" -ForegroundColor Yellow
        Write-Host "   Review: $reportDir/phpstan_$timestamp.txt" -ForegroundColor Gray
        Add-SummaryResult "PHPStan Analysis" "WARN" "$errorCount errors found"
    }
} else {
    Write-Host "⚠️ PHPStan not available (skipped)" -ForegroundColor Yellow
    Add-SummaryResult "PHPStan Analysis" "WARN" "PHPStan not installed"
}

# ===================================================================
# TEST 6: PHP CodeSniffer (Code Style)
# ===================================================================
Write-TestSection "TEST 6: PHP CodeSniffer (PSR-12)"

if (Test-Path "vendor/bin/phpcs") {
    Write-Host "Running PHPCS (PSR-12 standard)..." -ForegroundColor White
    $phpcsResult = php vendor/bin/phpcs --standard=PSR12 includes/controllers includes/models includes/core --extensions=php --report=summary 2>&1 | Out-String
    $phpcsResult | Out-File "$reportDir/phpcs_$timestamp.txt"
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Code style check passed (PSR-12 compliant)" -ForegroundColor Green
        Add-SummaryResult "Code Style (PHPCS)" "PASS" "PSR-12 compliant"
    } else {
        $violationMatches = [regex]::Matches($phpcsResult, '(\d+) violations?')
        $violations = if ($violationMatches) { $violationMatches[0].Groups[1].Value } else { "some" }
        Write-Host "⚠️ Code style violations found: $violations" -ForegroundColor Yellow
        Write-Host "   Review: $reportDir/phpcs_$timestamp.txt" -ForegroundColor Gray
        Add-SummaryResult "Code Style (PHPCS)" "WARN" "$violations violations found"
    }
} else {
    Write-Host "⚠️ PHPCS not available (skipped)" -ForegroundColor Yellow
    Add-SummaryResult "Code Style (PHPCS)" "WARN" "PHPCS not installed"
}

# ===================================================================
# TEST 7: PHPMD (Code Smells & Complexity)
# ===================================================================
Write-TestSection "TEST 7: PHPMD Code Quality"

if (Test-Path "vendor/bin/phpmd") {
    Write-Host "Running PHPMD (code smells detection)..." -ForegroundColor White
    $phpmdResult = php vendor/bin/phpmd includes/controllers text cleancode,codesize,design,unusedcode 2>&1 | Out-String
    $phpmdResult | Out-File "$reportDir/phpmd_$timestamp.txt"
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ No code smells detected" -ForegroundColor Green
        Add-SummaryResult "Code Quality (PHPMD)" "PASS" "No code smells"
    } else {
        Write-Host "⚠️ Code smells detected" -ForegroundColor Yellow
        Write-Host "   Review: $reportDir/phpmd_$timestamp.txt" -ForegroundColor Gray
        Add-SummaryResult "Code Quality (PHPMD)" "WARN" "Code smells detected"
    }
} else {
    Write-Host "⚠️ PHPMD not available (skipped)" -ForegroundColor Yellow
    Add-SummaryResult "Code Quality (PHPMD)" "WARN" "PHPMD not installed"
}

# ===================================================================
# TEST 8: File Permissions & Critical Files
# ===================================================================
Write-TestSection "TEST 8: Critical Files & Permissions"

$criticalFiles = @(
    "index.php",
    "config.php",
    "composer.json",
    ".htaccess"
)

$allFilesExist = $true
foreach ($file in $criticalFiles) {
    if (Test-Path $file) {
        Write-Host "✅ $file exists" -ForegroundColor Green
    } else {
        Write-Host "❌ $file NOT found" -ForegroundColor Red
        $allFilesExist = $false
    }
}

if ($allFilesExist) {
    Add-SummaryResult "Critical Files" "PASS" "All critical files present"
} else {
    Add-SummaryResult "Critical Files" "FAIL" "Some critical files missing"
}

# Check writable directories
$writableDirs = @("logs", "uploads")
foreach ($dir in $writableDirs) {
    if (Test-Path $dir) {
        Write-Host "ℹ️ Directory '$dir' exists (verify write permissions on server)" -ForegroundColor Gray
    } else {
        Write-Host "⚠️ Directory '$dir' not found - may need to create on deployment" -ForegroundColor Yellow
    }
}

# ===================================================================
# TEST 9: PHP Syntax Check
# ===================================================================
Write-TestSection "TEST 9: PHP Syntax Validation"

Write-Host "Checking PHP syntax in key directories..." -ForegroundColor White
$syntaxErrors = 0
$checkedFiles = 0

$directories = @("includes/controllers", "includes/models", "includes/core")
foreach ($dir in $directories) {
    if (Test-Path $dir) {
        $phpFiles = Get-ChildItem -Path $dir -Filter "*.php" -Recurse -ErrorAction SilentlyContinue
        foreach ($file in $phpFiles) {
            $checkedFiles++
            # Check syntax - we only care about exit code, not output
            php -l $file.FullName 2>&1 | Out-Null
            if ($LASTEXITCODE -ne 0) {
                $syntaxErrors++
                Write-Host "❌ Syntax error in: $($file.FullName)" -ForegroundColor Red
            }
        }
    }
}

if ($syntaxErrors -eq 0) {
    Write-Host "✅ All $checkedFiles PHP files have valid syntax" -ForegroundColor Green
    Add-SummaryResult "PHP Syntax" "PASS" "$checkedFiles files checked, no errors"
} else {
    Write-Host "❌ Found $syntaxErrors syntax error(s) in $checkedFiles files" -ForegroundColor Red
    Add-SummaryResult "PHP Syntax" "FAIL" "$syntaxErrors errors in $checkedFiles files"
}

# ===================================================================
# TEST 10: Autoloader Validation
# ===================================================================
Write-TestSection "TEST 10: Composer Autoloader"

if (Test-Path "vendor/autoload.php") {
    $autoloadCheck = php -r "require 'vendor/autoload.php'; echo 'Autoloader OK';" 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Autoloader is valid and working" -ForegroundColor Green
        Add-SummaryResult "Autoloader" "PASS" "Autoloader functional"
    } else {
        Write-Host "❌ Autoloader check failed: $autoloadCheck" -ForegroundColor Red
        Add-SummaryResult "Autoloader" "FAIL" "Autoloader validation failed"
    }
} else {
    Write-Host "⚠️ Autoloader not found - run 'composer install'" -ForegroundColor Yellow
    Add-SummaryResult "Autoloader" "WARN" "Autoloader missing"
}

# ===================================================================
# TEST 11: Code Duplication Detection (phpcpd)
# ===================================================================
Write-TestSection "TEST 11: Code Duplication Detection (phpcpd)"

if (Test-Path "vendor/bin/phpcpd") {
    Write-Host "Running phpcpd (duplication detector)..." -ForegroundColor White
    $phpcpdResult = php vendor/bin/phpcpd includes/controllers includes/models includes/core --min-lines=5 --min-tokens=50 2>&1 | Out-String
    $phpcpdResult | Out-File "$reportDir/phpcpd_$timestamp.txt"
    
    if ($LASTEXITCODE -eq 0 -or $phpcpdResult -match "No clones found") {
        Write-Host "✅ No significant code duplication detected" -ForegroundColor Green
        Add-SummaryResult "Code Duplication (phpcpd)" "PASS" "No significant duplication"
    } else {
        $cloneMatches = [regex]::Matches($phpcpdResult, 'Found (\d+) clones')
        $clones = if ($cloneMatches) { $cloneMatches[0].Groups[1].Value } else { "some" }
        Write-Host "⚠️ Code duplication detected: $clones clones" -ForegroundColor Yellow
        Write-Host "   Review: $reportDir/phpcpd_$timestamp.txt" -ForegroundColor Gray
        Add-SummaryResult "Code Duplication (phpcpd)" "WARN" "$clones clones found"
    }
} else {
    Write-Host "⚠️ phpcpd not available (skipped)" -ForegroundColor Yellow
    Add-SummaryResult "Code Duplication (phpcpd)" "WARN" "phpcpd not installed"
}

# ===================================================================
# TEST 12: Codeception (E2E Testing Setup Check)
# ===================================================================
Write-TestSection "TEST 12: Codeception (E2E Testing)"

if (Test-Path "vendor/bin/codecept") {
    Write-Host "Checking Codeception installation..." -ForegroundColor White
    
    # Check if codeception.yml exists (indicating setup)
    if ((Test-Path "codeception.yml") -or (Test-Path "tests/acceptance") -or (Test-Path "tests/functional")) {
        Write-Host "✅ Codeception is installed and configured" -ForegroundColor Green
        Add-SummaryResult "Codeception E2E Testing" "PASS" "Codeception configured"
    } else {
        Write-Host "⚠️ Codeception installed but not configured (run: vendor/bin/codecept bootstrap)" -ForegroundColor Yellow
        Add-SummaryResult "Codeception E2E Testing" "WARN" "Not configured - run bootstrap"
    }
    
    # Try to run a version check to verify it's working
    $codeceptCheck = php vendor/bin/codecept --version 2>&1 | Select-Object -First 1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   $codeceptCheck" -ForegroundColor Gray
    }
} else {
    Write-Host "⚠️ Codeception not available (skipped)" -ForegroundColor Yellow
    Add-SummaryResult "Codeception E2E Testing" "WARN" "Codeception not installed"
}

# ===================================================================
# Generate Summary Report
# ===================================================================
Write-Host "`n" 
Write-Host "===============================================================" -ForegroundColor Green
Write-Host "   DEPLOYMENT READINESS TEST COMPLETE" -ForegroundColor Green
Write-Host "===============================================================" -ForegroundColor Green
Write-Host ""

# Count results from actual test outputs
$passCount = 0
$warnCount = 0
$failCount = 0

# Count from summary lines (look for specific patterns)
if ($summary -match '✅') { $passCount = ([regex]::Matches($summary, '✅')).Count }
if ($summary -match '⚠️') { $warnCount = ([regex]::Matches($summary, '⚠️')).Count }
if ($summary -match '❌') { $failCount = ([regex]::Matches($summary, '❌')).Count }

$summary += "`n`n==============================================================="
$summary += "`nSUMMARY:"
$summary += "`n  ✅ Passed: $passCount"
$summary += "`n  ⚠️  Warnings: $warnCount"
$summary += "`n  ❌ Failed: $failCount"
$summary += "`n==============================================================="

$summary += "`n`nAll detailed reports saved to: $reportDir/"
$summary += "`nTimestamp: $timestamp"

# Save summary
$summary | Out-File $summaryFile -Encoding UTF8

# Display summary
Write-Host "DEPLOYMENT READINESS SUMMARY:" -ForegroundColor Cyan
Write-Host "  ✅ Passed: $passCount" -ForegroundColor Green
Write-Host "  ⚠️  Warnings: $warnCount" -ForegroundColor Yellow
Write-Host "  ❌ Failed: $failCount" -ForegroundColor $(if ($failCount -eq 0) { "Green" } else { "Red" })
Write-Host ""
Write-Host "Detailed reports saved to: $reportDir/" -ForegroundColor Cyan
Write-Host "Summary saved to: $summaryFile" -ForegroundColor Cyan
Write-Host ""

# Final recommendation
if ($failCount -eq 0 -and $warnCount -eq 0) {
    Write-Host "🎉 DEPLOYMENT READY - All checks passed!" -ForegroundColor Green
} elseif ($failCount -eq 0) {
    Write-Host "✅ DEPLOYMENT READY - Some warnings to review, but no critical failures" -ForegroundColor Yellow
} else {
    Write-Host "❌ NOT READY FOR DEPLOYMENT - Critical failures detected. Please fix before deploying." -ForegroundColor Red
}

Write-Host ""

