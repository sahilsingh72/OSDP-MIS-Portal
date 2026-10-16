<?php

namespace Database\Seeders;

use App\Services\DlcAccountService;
use Illuminate\Database\Seeder;

class DistrictCoordinatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = app(DlcAccountService::class);
        $result = $service->syncCoordinators();

        $this->command->info("District Coordinators seeded from dlc_mst: {$result['total']} processed ({$result['created']} created, {$result['updated']} updated).");
    }
}
