<?php
/**
 * Views Implementation Verification Script
 * 
 * Verifies that all view files referenced by controllers actually exist
 */

$viewsDirectory = __DIR__ . '/includes/views';
$controllersDirectory = __DIR__ . '/includes/controllers';

$results = [
    'views_found' => [],
    'views_missing' => [],
    'controllers_checked' => [],
    'errors' => []
];

echo "\n=== Views Implementation Verification ===\n\n";

// Get all view files
function getAllViewFiles($dir, $basePath = '') {
    $views = [];
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        $relativePath = $basePath ? $basePath . '/' . $item : $item;
        
        if (is_dir($path)) {
            $views = array_merge($views, getAllViewFiles($path, $relativePath));
        } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'php') {
            $viewName = str_replace('.php', '', $relativePath);
            $views[] = $viewName;
        }
    }
    
    return $views;
}

$allViews = getAllViewFiles($viewsDirectory);
echo "Total view files found: " . count($allViews) . "\n\n";

// Extract view references from controllers
function extractViewReferences($filePath) {
    $content = file_get_contents($filePath);
    $views = [];
    
    // Match renderView('view/path') or renderView("view/path")
    preg_match_all("/renderView\(['\"]([^'\"]+)['\"]/", $content, $matches);
    if (!empty($matches[1])) {
        $views = array_merge($views, $matches[1]);
    }
    
    // Match render('view/path') or render("view/path")
    preg_match_all("/render\(['\"]([^'\"]+)['\"]/", $content, $matches);
    if (!empty($matches[1])) {
        $views = array_merge($views, $matches[1]);
    }
    
    return array_unique($views);
}

// Check all controllers
$controllerFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($controllersDirectory)
);

$allReferencedViews = [];

foreach ($controllerFiles as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $relativePath = str_replace($controllersDirectory . '\\', '', $file->getPathname());
        $relativePath = str_replace('\\', '/', $relativePath);
        
        $views = extractViewReferences($file->getPathname());
        if (!empty($views)) {
            $results['controllers_checked'][] = $relativePath;
            foreach ($views as $view) {
                $allReferencedViews[] = $view;
            }
        }
    }
}

$allReferencedViews = array_unique($allReferencedViews);

echo "Controllers checked: " . count($results['controllers_checked']) . "\n";
echo "Unique views referenced: " . count($allReferencedViews) . "\n\n";

// Verify each referenced view exists
echo "Checking View References:\n";
echo str_repeat("-", 60) . "\n";

foreach ($allReferencedViews as $view) {
    // Check if view file exists (with .php extension)
    $viewFile = $viewsDirectory . '/' . $view . '.php';
    
    if (file_exists($viewFile)) {
        $results['views_found'][] = $view;
        echo "✓ $view\n";
    } else {
        $results['views_missing'][] = $view;
        $results['errors'][] = "View '$view' is referenced but file does not exist";
        echo "✗ $view (MISSING)\n";
    }
}

// Check for orphaned views (views that exist but aren't referenced)
$orphanedViews = array_diff($allViews, $allReferencedViews);

if (!empty($orphanedViews)) {
    echo "\nOrphaned Views (exist but not referenced by controllers):\n";
    echo str_repeat("-", 60) . "\n";
    foreach ($orphanedViews as $view) {
        echo "  - $view\n";
    }
}

// Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "VERIFICATION SUMMARY\n";
echo str_repeat("=", 60) . "\n";
echo "Controllers Checked: " . count($results['controllers_checked']) . "\n";
echo "Views Referenced: " . count($allReferencedViews) . "\n";
echo "Views Found: " . count($results['views_found']) . "\n";
echo "Views Missing: " . count($results['views_missing']) . "\n";
echo "Orphaned Views: " . count($orphanedViews) . "\n";
echo "Errors: " . count($results['errors']) . "\n";

if (!empty($results['errors'])) {
    echo "\nERRORS DETECTED:\n";
    foreach ($results['errors'] as $error) {
        echo "  ✗ $error\n";
    }
} else {
    echo "\n✓ All views are properly implemented!\n";
}

// Save results
file_put_contents(
    __DIR__ . '/views_implementation_report.json',
    json_encode([
        'views_found' => $results['views_found'],
        'views_missing' => $results['views_missing'],
        'controllers_checked' => $results['controllers_checked'],
        'orphaned_views' => array_values($orphanedViews),
        'errors' => $results['errors']
    ], JSON_PRETTY_PRINT)
);

echo "\nFull report saved to: views_implementation_report.json\n\n";

