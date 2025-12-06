#!/usr/bin/env pwsh
# Nyalife HMS - Quality Checks Script
# Runs all code quality tools and saves results

Write-Host "=== Nyalife HMS Code Quality Checks ===" -ForegroundColor Green
Write-Host ""

$reportDir = "quality-reports"
if (-not (Test-Path $reportDir)) {
    New-Item -ItemType Directory -Path $reportDir | Out-Null
}

# 1. PHPStan Static Analysis
Write-Host "[1/6] Running PHPStan static analysis..." -ForegroundColor Cyan
php vendor/bin/phpstan analyze includes/ --level=5 --no-progress --error-format=table > "$reportDir/phpstan-report.txt" 2>&1
Write-Host "    Results saved to $reportDir/phpstan-report.txt" -ForegroundColor Gray

# 2. PHP_CodeSniffer
Write-Host "[2/6] Running PHPCS code style check..." -ForegroundColor Cyan
php vendor/bin/phpcs --standard=PSR12 includes/controllers includes/models includes/core --extensions=php --report=summary > "$reportDir/phpcs-report.txt" 2>&1
Write-Host "    Results saved to $reportDir/phpcs-report.txt" -ForegroundColor Gray

# 3. PHPMD Code Smells
Write-Host "[3/6] Running PHPMD code smells check..." -ForegroundColor Cyan
php vendor/bin/phpmd includes/controllers text cleancode,codesize,design,unusedcode > "$reportDir/phpmd-report.txt" 2>&1
Write-Host "    Results saved to $reportDir/phpmd-report.txt" -ForegroundColor Gray

# 4. PHPMD Duplication
Write-Host "[4/6] Checking for code duplication..." -ForegroundColor Cyan
php vendor/bin/phpmd includes/ text duplicatedcode > "$reportDir/phpmd-duplication.txt" 2>&1
Write-Host "    Results saved to $reportDir/phpmd-duplication.txt" -ForegroundColor Gray

# 5. Composer Security Audit
Write-Host "[5/6] Running security audit..." -ForegroundColor Cyan
composer audit > "$reportDir/security-audit.txt" 2>&1
Write-Host "    Results saved to $reportDir/security-audit.txt" -ForegroundColor Gray

# 6. PHPMetrics (if available)
Write-Host "[6/6] Generating code quality metrics..." -ForegroundColor Cyan
if (Test-Path "vendor/bin/phpmetrics.bat") {
    php vendor/bin/phpmetrics --report-html="$reportDir/phpmetrics-html" includes/ > "$reportDir/phpmetrics-report.txt" 2>&1
    Write-Host "    Results saved to $reportDir/phpmetrics-report.txt and HTML report in $reportDir/phpmetrics-html/" -ForegroundColor Gray
} else {
    Write-Host "    PHPMetrics not available (skipped)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Quality Checks Complete ===" -ForegroundColor Green
Write-Host "All reports saved to: $reportDir/" -ForegroundColor Cyan
Write-Host ""
Write-Host "Quick Summary:" -ForegroundColor Yellow
Get-Content "$reportDir/phpstan-report.txt" -TotalCount 5 -ErrorAction SilentlyContinue
Write-Host ""
Get-Content "$reportDir/phpcs-report.txt" -TotalCount 3 -ErrorAction SilentlyContinue

