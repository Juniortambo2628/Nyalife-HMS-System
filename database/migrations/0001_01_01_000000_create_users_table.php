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
        // 1. Users Table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Add columns required by Laravel Auth if they don't exist
                if (!Schema::hasColumn('users', 'email_verified_at')) {
                    $table->timestamp('email_verified_at')->nullable();
                }
                if (!Schema::hasColumn('users', 'remember_token')) {
                    $table->rememberToken();
                }
                if (!Schema::hasColumn('users', 'created_at')) {
                    $table->timestamps();
                }
            });
        } else {
            // Fallback: Create new if not exists (should not happen based on plan)
            Schema::create('users', function (Blueprint $table) {
                $table->id('user_id'); // Legacy uses user_id
                $table->string('username')->unique();
                $table->string('email')->unique();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('password');
                $table->integer('role_id')->default(0); // Legacy link
                $table->boolean('is_active')->default(true);
                $table->string('phone')->nullable();
                $table->string('profile_image')->nullable();
                $table->timestamp('last_login')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // 2. Password Reset Tokens
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // 3. Sessions
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index(); // This maps to user_id (integer) usually, check compatibility?
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        // Do not drop users table to be safe? Or stick to default behavior.
        // Schema::dropIfExists('users'); 
    }
};
