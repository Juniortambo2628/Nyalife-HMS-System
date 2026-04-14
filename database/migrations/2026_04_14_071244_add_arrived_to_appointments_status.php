<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE appointments MODIFY status enum('scheduled','confirmed','completed','cancelled','no_show','pending','arrived') DEFAULT 'scheduled'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE appointments MODIFY status enum('scheduled','confirmed','completed','cancelled','no_show','pending') DEFAULT 'scheduled'");
    }
};
