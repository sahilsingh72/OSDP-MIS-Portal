<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\TrainingBatch;
use App\Models\User;
use App\Services\DlcAccountService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DlcCoordinatorManagementTest extends TestCase
{
    /**
     * Test syncing coordinators creates accounts with proper role and district scope.
     */
    public function test_dlc_account_service_syncs_all_coordinators(): void
    {
        $service = app(DlcAccountService::class);
        $result = $service->syncCoordinators('Admin@12345');

        $this->assertEquals(30, $result['total']);

        // Pick Anugola coordinator (district_id 1101)
        $anugulUser = User::where('email', 'dlcangul@okcl.org')->first();
        $this->assertNotNull($anugulUser);
        $this->assertEquals('Mr. Dibyajyoti Dash', $anugulUser->name);
        $this->assertEquals('9938847663', $anugulUser->phone);
        $this->assertTrue($anugulUser->hasRole('district-coordinator'));
        $this->assertTrue($anugulUser->isAssignedToDistrict(1101));
        $this->assertFalse($anugulUser->isAssignedToDistrict(1119)); // Not Khordha

        // Pick Puri coordinator (district_id 1126)
        $puriUser = User::where('email', 'dlcpuri@okcl.org')->first();
        $this->assertNotNull($puriUser);
        $this->assertEquals('Mr. Niranjan Mohapatra', $puriUser->name);
        $this->assertEquals('9090604390', $puriUser->phone);
        $this->assertTrue($puriUser->hasRole('district-coordinator'));
        $this->assertTrue($puriUser->isAssignedToDistrict(1126));
    }

    /**
     * Test coordinator can login and is scoped to their own district.
     */
    public function test_dlc_coordinator_can_login_and_access_scoped_dashboard(): void
    {
        // Ensure coordinators are synced
        app(DlcAccountService::class)->syncCoordinators('Admin@12345');

        // Login as Puri coordinator
        $response = $this->post('/login', [
            'email' => 'dlcpuri@okcl.org',
            'password' => 'Admin@12345',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        // Access dashboard
        $puriUser = User::where('email', 'dlcpuri@okcl.org')->first();
        $dashResponse = $this->actingAs($puriUser)->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Puri');
    }

    /**
     * Test coordinator cannot view or update batches from unassigned districts.
     */
    public function test_dlc_coordinator_cannot_manage_other_district_batches(): void
    {
        app(DlcAccountService::class)->syncCoordinators('Admin@12345');

        $balasoreUser = User::where('email', 'dlcbalasore@okcl.org')->first(); // Baleshwar ID 1103
        $this->assertNotNull($balasoreUser);

        // Find a batch in Cuttack (ID 1107)
        $cuttackBatch = TrainingBatch::where('district_id', 1107)->first();
        $this->assertNotNull($cuttackBatch);

        // Coordinator from Balasore attempts to view Cuttack batch details
        $response = $this->actingAs($balasoreUser)->get("/batches/{$cuttackBatch->id}");
        $response->assertStatus(403);

        // Coordinator from Balasore attempts to edit Cuttack batch
        $response = $this->actingAs($balasoreUser)->get("/batches/{$cuttackBatch->id}/edit");
        $response->assertStatus(403);
    }

    /**
     * Test super admin can trigger sync via web route.
     */
    public function test_super_admin_can_trigger_sync_dlc_via_web(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();

        $response = $this->actingAs($admin)->post('/users/sync-dlc');

        $response->assertRedirect('/users');
        $response->assertSessionHas('status');
    }

    /**
     * Test unauthorized users cannot trigger dlc sync.
     */
    public function test_unauthorized_user_cannot_trigger_dlc_sync(): void
    {
        $trainer = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();

        $response = $this->actingAs($trainer)->post('/users/sync-dlc');

        $response->assertStatus(403);
    }

    /**
     * Test artisan command dlc:sync-coordinators works properly.
     */
    public function test_artisan_command_dlc_sync_coordinators(): void
    {
        $this->artisan('dlc:sync-coordinators', ['--password' => 'Admin@12345'])
            ->expectsOutputToContain('Successfully processed 30 District Coordinator accounts')
            ->assertSuccessful();
    }
}
