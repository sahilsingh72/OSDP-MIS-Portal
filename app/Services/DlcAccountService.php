<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\District;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class DlcAccountService
{
    /**
     * Default initial password for synced DLC coordinator accounts.
     */
    public const DEFAULT_PASSWORD = 'Admin@12345';

    /**
     * Sync all District Coordinators from dlc_mst table into users and map them to their districts.
     *
     * @param string|null $defaultPassword
     * @return array{total: int, created: int, updated: int, accounts: array}
     */
    public function syncCoordinators(?string $defaultPassword = null): array
    {
        $password = $defaultPassword ?: self::DEFAULT_PASSWORD;

        if (!Schema::hasTable('dlc_mst')) {
            throw new \RuntimeException("Table 'dlc_mst' does not exist in the database.");
        }

        // Ensure the district-coordinator role exists in Spatie
        Role::findOrCreate('district-coordinator');

        $dlcRecords = DB::table('dlc_mst')->orderBy('district_id')->get();

        $created = 0;
        $updated = 0;
        $accounts = [];

        foreach ($dlcRecords as $dlc) {
            $email = strtolower(trim($dlc->email));
            $name = trim($dlc->name);
            $phone = trim($dlc->mobile_no ?? '');

            // Match District by ID, or fallback by name
            $district = null;
            if (!empty($dlc->district_id)) {
                $district = District::find($dlc->district_id);
            }
            if (!$district && !empty($dlc->district)) {
                $district = District::where('name', trim($dlc->district))->first();
            }

            $districtName = $district ? $district->name : ($dlc->district ?? 'District');
            $designation = "District Coordinator - {$districtName}";
            $department = "District Administration ({$districtName}) / OKCL";

            $user = User::where('email', $email)->first();
            $isNew = false;

            if (!$user) {
                $user = new User();
                $user->email = $email;
                $user->password = Hash::make($password);
                $isNew = true;
            }

            $user->name = $name;
            $user->phone = $phone ?: $user->phone;
            $user->designation = $designation;
            $user->department = $department;
            $user->is_active = true;
            $user->save();

            // Assign district-coordinator role
            if (!$user->hasRole('district-coordinator')) {
                $user->assignRole('district-coordinator');
            }

            // Sync assigned district in user_districts pivot
            $districtIdToSync = $district ? $district->id : $dlc->district_id;
            if ($districtIdToSync) {
                $user->assignedDistricts()->sync([$districtIdToSync]);
            }

            if ($isNew) {
                $created++;
            } else {
                $updated++;
            }

            $accounts[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'district_id' => $districtIdToSync,
                'district_name' => $districtName,
                'status' => $isNew ? 'created' : 'updated',
            ];
        }

        AuditLog::record('dlc_coordinators_synced', null, null, [
            'total' => count($accounts),
            'created' => $created,
            'updated' => $updated,
            'default_password' => $password,
        ]);

        return [
            'total' => count($accounts),
            'created' => $created,
            'updated' => $updated,
            'accounts' => $accounts,
        ];
    }
}
