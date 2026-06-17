<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = [
    'lab_requests', 'lab_results', 'lab_attachments', 'medical_history',
    'doctor_schedules', 'doctors', 'payment_transactions', 'audit_logs',
    'activity_logs', 'phinxlog', 'referrals', 'services', 'specializations',
    'system_notifications', 'user_tokens', 'remember_tokens',
    'obstetric_history', 'pregnancy_details', 'lab_test_items',
    'lab_test_parameters', 'lab_parameters', 'email_queue',
    'medication_categories',
];

foreach ($tables as $table) {
    try {
        $count = Illuminate\Support\Facades\DB::table($table)->count();
        echo "{$table}:{$count}\n";
    } catch (Throwable $e) {
        echo "{$table}:ERR\n";
    }
}
