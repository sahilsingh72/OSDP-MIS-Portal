<?php

namespace App\Console\Commands;

use App\Services\DlcAccountService;
use Illuminate\Console\Command;

class SyncDlcCoordinatorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dlc:sync-coordinators {--password= : Default password for new accounts (defaults to Admin@12345)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all District Coordinators from dlc_mst table into users and map them to their respective districts.';

    /**
     * Execute the console command.
     */
    public function handle(DlcAccountService $service): int
    {
        $passwordOption = $this->option('password') ?: DlcAccountService::DEFAULT_PASSWORD;

        $this->info('Starting District Coordinator account sync from dlc_mst...');

        try {
            $result = $service->syncCoordinators($passwordOption);

            $rows = array_map(function ($acc) {
                return [
                    $acc['id'],
                    $acc['name'],
                    $acc['email'],
                    $acc['phone'] ?: 'N/A',
                    $acc['district_name'] . " (ID: {$acc['district_id']})",
                    strtoupper($acc['status']),
                ];
            }, $result['accounts']);

            $this->table(
                ['User ID', 'Coordinator Name', 'Official Email', 'Phone', 'Assigned District', 'Status'],
                $rows
            );

            $this->newLine();
            $this->info("✓ Successfully processed {$result['total']} District Coordinator accounts.");
            $this->line("  - Created accounts: <fg=green>{$result['created']}</>");
            $this->line("  - Updated accounts: <fg=yellow>{$result['updated']}</>");
            $this->line("  - Default password applied: <fg=cyan>{$passwordOption}</>");
            $this->line('  - All coordinators have been mapped to their respective district scopes.');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to sync DLC coordinators: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
