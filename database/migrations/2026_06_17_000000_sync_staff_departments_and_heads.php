<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Department;
use App\Models\Staff;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure columns exist in departments table
        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'code')) {
                $table->string('code', 10)->nullable()->after('department_name');
            }
            if (!Schema::hasColumn('departments', 'type')) {
                $table->string('type', 30)->default('clinical')->after('code');
            }
            if (!Schema::hasColumn('departments', 'head_name')) {
                $table->string('head_name', 100)->nullable()->after('type');
            }
            if (!Schema::hasColumn('departments', 'head_position')) {
                $table->string('head_position', 100)->nullable()->after('head_name');
            }
            if (!Schema::hasColumn('departments', 'head_image')) {
                $table->string('head_image')->nullable()->after('head_position');
            }
        });

        // 2. Map existing staff to departments based on the department string field
        $staffMembers = Staff::all();

        foreach ($staffMembers as $staff) {
            if (!empty($staff->department)) {
                // Try to find matching department by name
                $dept = Department::where('department_name', 'like', trim($staff->department))
                    ->first();
                
                if ($dept) {
                    $staff->update([
                        'department_id' => $dept->department_id
                    ]);
                }
            }
        }

        // 3. For any department without a head, assign the first staff member as head if available
        $departments = Department::all();
        foreach ($departments as $dept) {
            if (empty($dept->head_name)) {
                // Find first staff member
                $headStaff = Staff::where('department_id', $dept->department_id)
                    ->with('user')
                    ->first();

                if ($headStaff && $headStaff->user) {
                    $fullName = trim($headStaff->user->first_name . ' ' . $headStaff->user->last_name);
                    $dept->update([
                        'head_name' => $fullName,
                        'head_position' => $headStaff->position ?? 'Department Head',
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // No rollback needed for data sync.
    }
};
