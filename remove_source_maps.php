<?php
$dir = 'resources/css';
$files = glob("$dir/*.css");

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Remove source map comments
    $newContent = preg_replace('/\/\*# sourceMappingURL=.*?\*\/ /', '', $content);
    $newContent = preg_replace('/\/\*# sourceMappingURL=.*?\*\//', '', $newContent);
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Removed source map from $file\n";
    }
}
