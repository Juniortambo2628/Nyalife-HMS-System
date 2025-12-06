<?php
/**
 * Test web routes functionality
 */

echo "=== TESTING WEB ROUTES ===\n\n";

// Test by making HTTP requests to the local server
$baseUrl = "http://localhost/Nyalife-HMS-System";

echo "🔍 TESTING ROUTE ENDPOINTS...\n";

// Test departments route
echo "\n📍 Testing /departments route:\n";
$departmentsUrl = $baseUrl . "/departments";
echo "URL: $departmentsUrl\n";

// Use curl to test the route (if available)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $departmentsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request only
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false) {
    echo "Response Code: $httpCode\n";
    if ($httpCode == 200) {
        echo "✅ Departments route: ACCESSIBLE\n";
    } elseif ($httpCode == 404) {
        echo "❌ Departments route: 404 ERROR\n";
    } elseif ($httpCode == 302) {
        echo "🔄 Departments route: REDIRECT (likely auth required)\n";
    } else {
        echo "⚠️ Departments route: HTTP $httpCode\n";
    }
} else {
    echo "❌ Departments route: CONNECTION FAILED\n";
}

// Test invoices route
echo "\n📍 Testing /invoices route:\n";
$invoicesUrl = $baseUrl . "/invoices";
echo "URL: $invoicesUrl\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $invoicesUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false) {
    echo "Response Code: $httpCode\n";
    if ($httpCode == 200) {
        echo "✅ Invoices route: ACCESSIBLE\n";
    } elseif ($httpCode == 404) {
        echo "❌ Invoices route: 404 ERROR\n";
    } elseif ($httpCode == 302) {
        echo "🔄 Invoices route: REDIRECT (likely auth required)\n";
    } else {
        echo "⚠️ Invoices route: HTTP $httpCode\n";
    }
} else {
    echo "❌ Invoices route: CONNECTION FAILED\n";
}

echo "\n=== ROUTE TEST COMPLETE ===\n";
echo "Note: Redirects (302) are expected for authenticated routes\n";
echo "404 errors indicate routing issues that need fixing\n";
?>
