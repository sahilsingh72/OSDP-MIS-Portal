<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\District;
use App\Models\TrainingBatch;
use App\Models\User;
use App\Models\Venue;
use Tests\TestCase;

class VenueManagementTest extends TestCase
{
    /**
     * Unauthenticated guest users must be redirected to login.
     */
    public function test_guest_cannot_access_venue_crud(): void
    {
        $venue = Venue::first();
        $this->assertNotNull($venue);

        $this->get('/venues/create')->assertRedirect('/login');
        $this->post('/venues', [])->assertRedirect('/login');
        $this->get("/venues/{$venue->id}/edit")->assertRedirect('/login');
        $this->put("/venues/{$venue->id}", [])->assertRedirect('/login');
        $this->delete("/venues/{$venue->id}")->assertRedirect('/login');
    }

    /**
     * Field Trainers cannot create, edit, or delete venues (403 Forbidden).
     */
    public function test_field_trainer_cannot_create_or_modify_venues(): void
    {
        $trainer = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();
        $this->assertNotNull($trainer);

        $venue = Venue::first();
        $this->assertNotNull($venue);

        $this->actingAs($trainer)->get('/venues/create')->assertStatus(403);
        $this->actingAs($trainer)->post('/venues', [
            'name' => 'Unauthorized Venue',
            'district_id' => $venue->district_id,
            'capacity' => 40,
        ])->assertStatus(403);
        $this->actingAs($trainer)->get("/venues/{$venue->id}/edit")->assertStatus(403);
        $this->actingAs($trainer)->put("/venues/{$venue->id}", [
            'name' => 'Tampered Venue',
            'district_id' => $venue->district_id,
            'capacity' => 40,
        ])->assertStatus(403);
        $this->actingAs($trainer)->delete("/venues/{$venue->id}")->assertStatus(403);
    }

    /**
     * District Coordinators cannot create, edit, or delete venues (403 Forbidden).
     */
    public function test_district_coordinator_cannot_create_or_modify_venues(): void
    {
        $dc = User::where('email', 'dc.khordha@osdp.gov.in')->first();
        $this->assertNotNull($dc);

        $venue = Venue::first();
        $this->assertNotNull($venue);

        $this->actingAs($dc)->get('/venues/create')->assertStatus(403);
        $this->actingAs($dc)->post('/venues', [
            'name' => 'DC Unauthorized Venue',
            'district_id' => $venue->district_id,
            'capacity' => 50,
        ])->assertStatus(403);
        $this->actingAs($dc)->get("/venues/{$venue->id}/edit")->assertStatus(403);
        $this->actingAs($dc)->put("/venues/{$venue->id}", [
            'name' => 'DC Tampered Venue',
            'district_id' => $venue->district_id,
            'capacity' => 50,
        ])->assertStatus(403);
        $this->actingAs($dc)->delete("/venues/{$venue->id}")->assertStatus(403);
    }

    /**
     * Super Admin can view create venue page.
     */
    public function test_super_admin_can_view_create_venue_page(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/venues/create');
        $response->assertStatus(200);
        $response->assertSee('Register Training Venue');
        $response->assertSee('Venue Facility Name');
        $response->assertSee('Seating Capacity');
    }

    /**
     * PMU can create a new venue.
     */
    public function test_pmu_can_create_venue(): void
    {
        $pmu = User::where('email', 'pmu@osdp.gov.in')->first();
        $this->assertNotNull($pmu);

        $district = District::where('code', 'KHD')->first();
        $this->assertNotNull($district);

        // Clean up any previous test venue
        Venue::where('name', 'PMU High-Tech Training Hall')->delete();

        $response = $this->actingAs($pmu)->post('/venues', [
            'name' => 'PMU High-Tech Training Hall',
            'district_id' => $district->id,
            'address' => 'Plot 4, Chandaka Industrial Estate, Bhubaneswar',
            'capacity' => 65,
            'facilities' => [
                'projector' => '1',
                'ac' => '1',
                'internet' => '1',
                'audio_system' => '1',
            ],
            'contact_person' => 'Ramesh Chandra Patra',
            'contact_phone' => '9437099881',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/master-data');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('venues', [
            'name' => 'PMU High-Tech Training Hall',
            'district_id' => $district->id,
            'capacity' => 65,
            'contact_person' => 'Ramesh Chandra Patra',
            'is_active' => true,
        ]);

        $venue = Venue::where('name', 'PMU High-Tech Training Hall')->first();
        $this->assertNotNull($venue);
        $this->assertTrue($venue->facilities['projector']);
        $this->assertTrue($venue->facilities['ac']);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'venue_created',
            'auditable_type' => Venue::class,
            'auditable_id' => $venue->id,
        ]);
    }

    /**
     * Super Admin can edit and update a venue.
     */
    public function test_super_admin_can_edit_and_update_venue(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $district = District::where('code', 'KHD')->first();

        $venue = Venue::where('name', 'PMU High-Tech Training Hall')->first();
        if (!$venue) {
            $venue = Venue::create([
                'district_id' => $district->id,
                'name' => 'PMU High-Tech Training Hall',
                'capacity' => 65,
                'is_active' => true,
            ]);
        }

        $editPage = $this->actingAs($admin)->get("/venues/{$venue->id}/edit");
        $editPage->assertStatus(200);
        $editPage->assertSee('Edit Venue: PMU High-Tech Training Hall');

        $updateResponse = $this->actingAs($admin)->put("/venues/{$venue->id}", [
            'name' => 'PMU Advanced IT Training Complex',
            'district_id' => $district->id,
            'address' => 'Updated Address, Infocity, Bhubaneswar',
            'capacity' => 80,
            'facilities' => [
                'projector' => '1',
                'ac' => '1',
            ],
            'contact_person' => 'Ashok Mohapatra',
            'contact_phone' => '9861000001',
            'is_active' => '0', // Deactivated
        ]);

        $updateResponse->assertRedirect('/master-data');
        $updateResponse->assertSessionHas('status');

        $venue->refresh();
        $this->assertEquals('PMU Advanced IT Training Complex', $venue->name);
        $this->assertEquals(80, $venue->capacity);
        $this->assertFalse($venue->is_active);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'venue_updated',
            'auditable_type' => Venue::class,
            'auditable_id' => $venue->id,
        ]);
    }

    /**
     * A venue linked to batches cannot be deleted and returns an error.
     */
    public function test_venue_cannot_be_deleted_if_linked_to_batches(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();

        // Find venue with batches
        $venueWithBatches = Venue::whereHas('batches')->first();
        $this->assertNotNull($venueWithBatches);
        $this->assertGreaterThan(0, $venueWithBatches->batches()->count());

        $response = $this->actingAs($admin)->delete("/venues/{$venueWithBatches->id}");

        $response->assertRedirect('/master-data');
        $response->assertSessionHasErrors(['venue']);
        $this->assertDatabaseHas('venues', ['id' => $venueWithBatches->id]);
    }

    /**
     * A venue not linked to any batches can be deleted successfully by PMU or Super Admin.
     */
    public function test_venue_can_be_deleted_if_not_linked_to_batches(): void
    {
        $pmu = User::where('email', 'pmu@osdp.gov.in')->first();
        $district = District::first();

        $standaloneVenue = Venue::create([
            'district_id' => $district->id,
            'name' => 'Temporary Standalone Venue To Delete',
            'capacity' => 30,
            'is_active' => true,
        ]);

        $response = $this->actingAs($pmu)->delete("/venues/{$standaloneVenue->id}");

        $response->assertRedirect('/master-data');
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('venues', ['id' => $standaloneVenue->id]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'venue_deleted',
        ]);

        // Cleanup
        Venue::where('name', 'PMU Advanced IT Training Complex')->delete();
    }
}
