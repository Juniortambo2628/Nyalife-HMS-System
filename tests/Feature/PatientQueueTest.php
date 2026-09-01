<?php
namespace Tests\Feature;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionsSeeder;
use Database\Seeders\SyncSpatieRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class PatientQueueTest extends TestCase { use RefreshDatabase;
 public function test_queue_assigns_order_and_classifies_new_and_revisit_patients(): void { foreach(['admin','patient'] as $r) Role::firstOrCreate(['role_name'=>$r]); $this->seed(SyncSpatieRolesSeeder::class); $this->seed(RolePermissionsSeeder::class); $admin=User::factory()->create(['role_id'=>Role::where('role_name','admin')->value('role_id')]); $admin->assignRole('admin'); $new=Patient::factory()->create(); $revisit=Patient::factory()->create(); Consultation::factory()->completed()->create(['patient_id'=>$revisit->patient_id]); $this->actingAs($admin)->post(route('queue.store'),['patient_id'=>$new->patient_id])->assertRedirect(); $this->actingAs($admin)->post(route('queue.store'),['patient_id'=>$revisit->patient_id])->assertRedirect(); $this->assertDatabaseHas('patient_queues',['patient_id'=>$new->patient_id,'queue_number'=>1,'visit_type'=>'new']); $this->assertDatabaseHas('patient_queues',['patient_id'=>$revisit->patient_id,'queue_number'=>2,'visit_type'=>'revisit']); }
}
