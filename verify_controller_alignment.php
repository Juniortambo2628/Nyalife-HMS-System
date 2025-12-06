<?php
/**
 * Controller Alignment Verification Script
 * 
 * Checks for mismatched references in controllers:
 * - Model references
 * - View references
 * - Method calls
 * - Property usage
 */

require_once __DIR__ . '/vendor/autoload.php';

$basePath = __DIR__;
$controllersPath = $basePath . '/includes/controllers';
$modelsPath = $basePath . '/includes/models';
$viewsPath = $basePath . '/includes/views';

$issues = [];
$warnings = [];
$stats = [
    'controllers_checked' => 0,
    'models_referenced' => [],
    'views_referenced' => [],
    'methods_called' => [],
    'properties_used' => []
];

echo "=== Controller Alignment Verification ===\n\n";

// Get all controller files
$controllerFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($controllersPath)
);
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $controllerFiles[] = $file->getPathname();
    }
}

// Get all model files
$modelFiles = [];
if (is_dir($modelsPath)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($modelsPath)
    );
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $modelFiles[] = $file->getPathname();
        }
    }
}

// Extract model class names
$modelClasses = [];
foreach ($modelFiles as $file) {
    $content = file_get_contents($file);
    if (preg_match('/class\s+(\w+Model)\s+extends/', $content, $matches)) {
        $modelClasses[] = $matches[1];
    }
}

// Get all view files
$viewFiles = [];
if (is_dir($viewsPath)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewsPath)
    );
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $relativePath = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $viewFiles[] = str_replace('\\', '/', $relativePath);
        }
    }
}

// Check each controller
foreach ($controllerFiles as $controllerFile) {
    $stats['controllers_checked']++;
    $content = file_get_contents($controllerFile);
    $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $controllerFile);
    
    echo "Checking: {$relativePath}\n";
    
    // Check for model references
    if (preg_match_all('/new\s+(\w+Model)\s*\(/i', $content, $matches)) {
        foreach ($matches[1] as $modelName) {
            if (!in_array($modelName, $modelClasses)) {
                $issues[] = [
                    'type' => 'missing_model',
                    'file' => $relativePath,
                    'model' => $modelName,
                    'severity' => 'error'
                ];
            } else {
                $stats['models_referenced'][$modelName] = ($stats['models_referenced'][$modelName] ?? 0) + 1;
            }
        }
    }
    
    // Check for view references
    if (preg_match_all('/renderView\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        foreach ($matches[1] as $viewPath) {
            $viewFile = $viewsPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $viewPath) . '.php';
            if (!file_exists($viewFile)) {
                $issues[] = [
                    'type' => 'missing_view',
                    'file' => $relativePath,
                    'view' => $viewPath,
                    'severity' => 'error'
                ];
            } else {
                $stats['views_referenced'][$viewPath] = ($stats['views_referenced'][$viewPath] ?? 0) + 1;
            }
        }
    }
    
    // Check for method calls on models
    if (preg_match_all('/(\w+Model)->(\w+)\s*\(/i', $content, $matches)) {
        foreach ($matches[0] as $index => $fullMatch) {
            $modelVar = $matches[1][$index];
            $method = $matches[2][$index];
            $stats['methods_called'][] = [
                'model' => $modelVar,
                'method' => $method,
                'file' => $relativePath
            ];
        }
    }
    
    // Check for undefined properties
    if (preg_match_all('/\$this->(\w+)\s*[=;]/', $content, $matches)) {
        foreach ($matches[1] as $property) {
            if (!preg_match('/@var.*\$' . $property . '/', $content) && 
                !preg_match('/private\s+\$' . $property . '/', $content) &&
                !preg_match('/protected\s+\$' . $property . '/', $content) &&
                !preg_match('/public\s+\$' . $property . '/', $content)) {
                $warnings[] = [
                    'type' => 'undefined_property',
                    'file' => $relativePath,
                    'property' => $property,
                    'severity' => 'warning'
                ];
            }
        }
    }
    
    // Check for undefined methods
    if (preg_match_all('/\$this->(\w+)\s*\(/', $content, $matches)) {
        foreach ($matches[1] as $method) {
            if (!preg_match('/function\s+' . $method . '\s*\(/', $content) &&
                !preg_match('/private\s+function\s+' . $method . '/', $content) &&
                !preg_match('/protected\s+function\s+' . $method . '/', $content) &&
                !preg_match('/public\s+function\s+' . $method . '/', $content)) {
                // Check if it's from parent class (common methods)
                $commonMethods = ['renderView', 'redirect', 'getParam', 'processFormData', 'sendJson', 'sendError'];
                if (!in_array($method, $commonMethods)) {
                    $warnings[] = [
                        'type' => 'undefined_method',
                        'file' => $relativePath,
                        'method' => $method,
                        'severity' => 'warning'
                    ];
                }
            }
        }
    }
}

echo "\n=== Verification Results ===\n\n";

echo "Statistics:\n";
echo "  Controllers Checked: {$stats['controllers_checked']}\n";
echo "  Unique Models Referenced: " . count($stats['models_referenced']) . "\n";
echo "  Unique Views Referenced: " . count($stats['views_referenced']) . "\n";
echo "  Method Calls: " . count($stats['methods_called']) . "\n\n";

if (empty($issues) && empty($warnings)) {
    echo "✓ No issues found! All controllers are properly aligned.\n";
} else {
    if (!empty($issues)) {
        echo "Errors Found (" . count($issues) . "):\n";
        foreach ($issues as $issue) {
            echo "  ✗ [{$issue['type']}] {$issue['file']}\n";
            if (isset($issue['model'])) {
                echo "      Missing model: {$issue['model']}\n";
            }
            if (isset($issue['view'])) {
                echo "      Missing view: {$issue['view']}\n";
            }
        }
        echo "\n";
    }
    
    if (!empty($warnings)) {
        echo "Warnings Found (" . count($warnings) . "):\n";
        foreach ($warnings as $warning) {
            echo "  ⚠ [{$warning['type']}] {$warning['file']}\n";
            if (isset($warning['property'])) {
                echo "      Property: {$warning['property']}\n";
            }
            if (isset($warning['method'])) {
                echo "      Method: {$warning['method']}\n";
            }
        }
        echo "\n";
    }
}

// Save report
$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'statistics' => $stats,
    'issues' => $issues,
    'warnings' => $warnings
];

file_put_contents('controller_alignment_report.json', json_encode($report, JSON_PRETTY_PRINT));
echo "Full report saved to: controller_alignment_report.json\n";

exit(empty($issues) ? 0 : 1);

