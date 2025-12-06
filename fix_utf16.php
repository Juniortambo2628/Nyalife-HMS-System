<?php
$file = 'includes/views/appointments/calendar.php';

// Read with UTF-16 encoding
$content = file_get_contents($file);
$content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');

// Save as UTF-8 without BOM
file_put_contents($file, $content);
echo "Fixed UTF-16 encoding\n";

