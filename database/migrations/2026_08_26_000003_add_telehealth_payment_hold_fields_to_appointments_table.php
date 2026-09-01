<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->decimal('telehealth_payment_amount', 12, 2)->nullable()->after('notes');
            $table->string('telehealth_payment_reference', 100)->nullable()->after('telehealth_payment_amount');
            $table->string('telehealth_payment_receipt_path')->nullable()->after('telehealth_payment_reference');
            $table->dateTime('telehealth_payment_submitted_at')->nullable()->after('telehealth_payment_receipt_path');
            $table->dateTime('telehealth_payment_expires_at')->nullable()->index()->after('telehealth_payment_submitted_at');
            $table->dateTime('telehealth_payment_approved_at')->nullable()->after('telehealth_payment_expires_at');
            $table->string('telehealth_payment_token', 64)->nullable()->unique()->after('telehealth_payment_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['telehealth_payment_amount', 'telehealth_payment_reference', 'telehealth_payment_receipt_path', 'telehealth_payment_submitted_at', 'telehealth_payment_expires_at', 'telehealth_payment_approved_at', 'telehealth_payment_token']);
        });
    }
};
