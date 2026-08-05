<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Assign the Spatie 'patient' role to all existing users with role_id=1
     * who don't already have it. This fixes the missing permission that caused
     * 403 errors on the patient portal.
     */
    public function up(): void
    {
        $patientRoleId = DB::table('spatie_roles')->where('name', 'patient')->where('guard_name', 'web')->value('id');
        if (! $patientRoleId) {
            return;
        }

        // Get legacy patient role_id from the custom roles table
        $legacyPatientRoleId = DB::table((new Role)->getTable())
            ->where('role_name', 'patient')
            ->value('role_id');

        if (! $legacyPatientRoleId) {
            return;
        }

        $userIds = DB::table('users')
            ->where('role_id', $legacyPatientRoleId)
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            $exists = DB::table('model_has_roles')
                ->where('role_id', $patientRoleId)
                ->where('model_type', 'App\\Models\\User')
                ->where('user_id', $userId)
                ->exists();

            if (! $exists) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $patientRoleId,
                    'model_type' => 'App\\Models\\User',
                    'user_id' => $userId,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversible safely
    }
};
