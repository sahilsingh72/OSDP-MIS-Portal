<?php

namespace Tests\Feature;

use App\Models\TrainingBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test authorized user can view dashboard with training event calendar.
     */
    public function test_user_can_view_dashboard_with_training_event_calendar(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Training Event Calendar');
        $response->assertSee('Live Schedule');
        $response->assertSee('trainingCalendar');
        $response->assertSee('Selected Date Agenda');
        $response->assertViewHas('calendarBatches');

        $calendarBatches = $response->viewData('calendarBatches');
        $this->assertNotEmpty($calendarBatches);

        // Verify structure of mapped calendar batch
        $sample = $calendarBatches->first();
        $this->assertArrayHasKey('id', $sample);
        $this->assertArrayHasKey('batch_code', $sample);
        $this->assertArrayHasKey('date', $sample);
        $this->assertArrayHasKey('time_range', $sample);
        $this->assertArrayHasKey('status', $sample);
        $this->assertArrayHasKey('district_name', $sample);
        $this->assertArrayHasKey('show_url', $sample);
    }
}
