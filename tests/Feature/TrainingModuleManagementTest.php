<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\TrainingBatch;
use App\Models\TrainingModule;
use App\Models\User;
use Tests\TestCase;

class TrainingModuleManagementTest extends TestCase
{
    public function test_guest_cannot_access_module_crud(): void
    {
        $this->get('/modules/create')->assertRedirect('/login');
        $this->post('/modules', [])->assertRedirect('/login');
        $this->get('/modules/1/edit')->assertRedirect('/login');
        $this->put('/modules/1', [])->assertRedirect('/login');
        $this->delete('/modules/1')->assertRedirect('/login');
    }

    public function test_super_admin_can_view_create_module_page(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/modules/create');
        $response->assertStatus(200);
        $response->assertSee('Create Curriculum Module');
        $response->assertSee('Module Number');
        $response->assertSee('Module Code');
    }

    public function test_pmu_can_create_module(): void
    {
        $pmu = User::where('email', 'pmu@osdp.gov.in')->first();
        $this->assertNotNull($pmu);

        // Cleanup test module if previously existing
        TrainingModule::where('code', 'MOD-99')->delete();

        $response = $this->actingAs($pmu)->post('/modules', [
            'module_number' => 99,
            'code' => 'MOD-99',
            'title' => 'Advanced Cyber Governance & Threat Intelligence',
            'description' => 'Comprehensive framework on threat intelligence and frontline cyber incident response.',
            'topics' => "Topic A: Incident triage\nTopic B: Evidence preservation\nTopic C: Statutory reporting",
            'is_active' => '1',
        ]);

        $response->assertRedirect('/master-data');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('training_modules', [
            'module_number' => 99,
            'code' => 'MOD-99',
            'title' => 'Advanced Cyber Governance & Threat Intelligence',
            'is_active' => true,
        ]);

        $module = TrainingModule::where('code', 'MOD-99')->first();
        $this->assertCount(3, $module->topics);
        $this->assertEquals('Topic A: Incident triage', $module->topics[0]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'module_created',
            'auditable_type' => TrainingModule::class,
            'auditable_id' => $module->id,
        ]);
    }

    public function test_module_creation_validates_uniqueness(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();

        // Existing module 1 has module_number 1 and code MOD-1
        $response = $this->actingAs($admin)->post('/modules', [
            'module_number' => 1,
            'code' => 'MOD-1',
            'title' => 'Duplicate test',
        ]);

        $response->assertSessionHasErrors(['module_number', 'code']);
    }

    public function test_admin_can_edit_and_update_module(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();

        $module = TrainingModule::where('code', 'MOD-99')->first();
        if (!$module) {
            $module = TrainingModule::create([
                'module_number' => 99,
                'code' => 'MOD-99',
                'title' => 'Temp Module',
                'description' => 'Temp Desc',
                'is_active' => true,
            ]);
        }

        $editPage = $this->actingAs($admin)->get("/modules/{$module->id}/edit");
        $editPage->assertStatus(200);
        $editPage->assertSee('Edit Module 99');

        $updateResponse = $this->actingAs($admin)->put("/modules/{$module->id}", [
            'module_number' => 99,
            'code' => 'MOD-99',
            'title' => 'Updated Cyber Governance Title',
            'description' => 'Updated description content.',
            'topics' => "Updated Topic 1\nUpdated Topic 2",
            'is_active' => '0', // Deactivated
        ]);

        $updateResponse->assertRedirect('/master-data');
        $updateResponse->assertSessionHas('status');

        $module->refresh();
        $this->assertEquals('Updated Cyber Governance Title', $module->title);
        $this->assertFalse($module->is_active);
        $this->assertCount(2, $module->topics);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'module_updated',
            'auditable_type' => TrainingModule::class,
            'auditable_id' => $module->id,
        ]);
    }

    public function test_module_cannot_be_deleted_if_linked_to_batches(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();

        // Module 1 is linked to seeded batches
        $module1 = TrainingModule::where('module_number', 1)->first();
        $this->assertNotNull($module1);
        $this->assertGreaterThan(0, $module1->batches()->count());

        $response = $this->actingAs($admin)->delete("/modules/{$module1->id}");

        $response->assertRedirect('/master-data');
        $response->assertSessionHasErrors(['module']);
        $this->assertDatabaseHas('training_modules', ['id' => $module1->id]);
    }

    public function test_module_can_be_deleted_if_not_linked_to_batches(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();

        $standaloneModule = TrainingModule::create([
            'module_number' => 999,
            'code' => 'MOD-999',
            'title' => 'Standalone Module to Delete',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete("/modules/{$standaloneModule->id}");

        $response->assertRedirect('/master-data');
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('training_modules', ['id' => $standaloneModule->id]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'module_deleted',
        ]);

        // Cleanup
        TrainingModule::where('code', 'MOD-99')->delete();
    }
}
