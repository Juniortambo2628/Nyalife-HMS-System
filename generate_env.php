<?php
// Secure this file by deleting it after use or using .htaccess to restrict access

$envPath = __DIR__ . '/.env';

$envVars = [
    'APP_NAME'     => 'Nyalife HMS',
    'APP_ENV'      => 'production',
    'APP_DEBUG'    => 'false',
    'DB_HOST'      => 'localhost',
    'DB_NAME'      => 'nyalife_hms',
    'DB_USER'      => 'nyalife_user',
    'DB_PASS'      => 'secure_password',
    'APP_TIMEZONE' => 'Africa/Nairobi'
];

$content = "";
foreach ($envVars as $key => $value) {
    $content .= "$key=\"$value\"\n";
}

file_put_contents($envPath, $content);
echo ".env generated successfully. Delete this file after use!";
