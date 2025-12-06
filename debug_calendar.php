<?php
$file = 'includes/views/appointments/calendar.php';
$content = file_get_contents($file);

echo "File size: " . filesize($file) . "\n";
echo "First 50 chars: " . substr($content, 0, 50) . "\n";
echo "Has <?php: " . (strpos($content, '<?php') !== false ? 'YES' : 'NO') . "\n";
echo "Position of <?php: " . strpos($content, '<?php') . "\n";
echo "Has <?: " . (strpos($content, '<?') !== false ? 'YES' : 'NO') . "\n";
echo "Has FullCalendar: " . (strpos($content, 'FullCalendar') !== false ? 'YES' : 'NO') . "\n";

// Check for BOM
$bom = substr($content, 0, 3);
echo "First 3 bytes (hex): " . bin2hex($bom) . "\n";

