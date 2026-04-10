<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'setting_id')) {
                $table->renameColumn('setting_id', 'id');
            }
            if (Schema::hasColumn('settings', 'setting_key')) {
                $table->renameColumn('setting_key', 'key');
            }
            if (Schema::hasColumn('settings', 'setting_value')) {
                $table->renameColumn('setting_value', 'value');
            }
            
            if (!Schema::hasColumn('settings', 'type')) {
                $table->string('type')->default('text');
            }
            if (!Schema::hasColumn('settings', 'group')) {
                $table->string('group')->default('general');
            }
            if (!Schema::hasColumn('settings', 'label')) {
                $table->string('label')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            //
        });
    }
};
