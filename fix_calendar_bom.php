<?php
$file = 'includes/views/appointments/calendar.php';
$content = file_get_contents($file);
// Remove UTF-8 BOM
$content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
// Save without BOM
file_put_contents($file, $content);
echo "Fixed BOM in calendar.php\n";

