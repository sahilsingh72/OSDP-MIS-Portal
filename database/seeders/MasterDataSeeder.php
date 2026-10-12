<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\District;
use App\Models\GramPanchayat;
use App\Models\SystemSetting;
use App\Models\TrainingModule;
use App\Models\TrainingTier;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. All 30 Districts of Odisha
        $districtsData = [
            ['name' => 'Angul', 'code' => 'ANG'],
            ['name' => 'Balangir', 'code' => 'BLG'],
            ['name' => 'Balasore', 'code' => 'BLS'],
            ['name' => 'Bargarh', 'code' => 'BRG'],
            ['name' => 'Bhadrak', 'code' => 'BHD'],
            ['name' => 'Boudh', 'code' => 'BOD'],
            ['name' => 'Cuttack', 'code' => 'CTC'],
            ['name' => 'Deogarh', 'code' => 'DEG'],
            ['name' => 'Dhenkanal', 'code' => 'DNK'],
            ['name' => 'Gajapati', 'code' => 'GAJ'],
            ['name' => 'Ganjam', 'code' => 'GNJ'],
            ['name' => 'Jagatsinghpur', 'code' => 'JSP'],
            ['name' => 'Jajpur', 'code' => 'JJP'],
            ['name' => 'Jharsuguda', 'code' => 'JHG'],
            ['name' => 'Kalahandi', 'code' => 'KLH'],
            ['name' => 'Kandhamal', 'code' => 'KND'],
            ['name' => 'Kendrapara', 'code' => 'KNP'],
            ['name' => 'Kendujhar', 'code' => 'KDJ'],
            ['name' => 'Khordha', 'code' => 'KHD'],
            ['name' => 'Koraput', 'code' => 'KRP'],
            ['name' => 'Malkangiri', 'code' => 'MLK'],
            ['name' => 'Mayurbhanj', 'code' => 'MYB'],
            ['name' => 'Nabarangpur', 'code' => 'NBP'],
            ['name' => 'Nayagarh', 'code' => 'NYG'],
            ['name' => 'Nuapada', 'code' => 'NPD'],
            ['name' => 'Puri', 'code' => 'PUR'],
            ['name' => 'Rayagada', 'code' => 'RYG'],
            ['name' => 'Sambalpur', 'code' => 'SBP'],
            ['name' => 'Subarnapur', 'code' => 'SON'],
            ['name' => 'Sundergarh', 'code' => 'SNG'],
        ];

        $districtModels = [];
        foreach ($districtsData as $d) {
            $districtModels[$d['name']] = District::updateOrCreate(['code' => $d['code']], [
                'name' => $d['name'],
                'is_active' => true,
            ]);
        }

        // 2. Blocks & Gram Panchayats for Khordha
        $khordha = $districtModels['Khordha'];
        $khordhaBlocks = [
            'Bhubaneswar' => ['Chandaka', 'Daruthenga', 'Mendhasala', 'Kalyanpur'],
            'Jatni' => ['Jatani Rural', 'Kudiary', 'Jatni Cluster A', 'Jatni Cluster B'],
            'Khordha' => ['Kaipadar', 'Bajapur', 'Golabai', 'Palla'],
            'Balianta' => ['Balianta', 'Bhingarpur', 'Benupur', 'Satyabhamapur'],
            'Balipatna' => ['Balipatna', 'Bamanal', 'Rajas', 'Majhihara'],
            'Banapur' => ['Banapur', 'Balugaon Rural', 'Gambharimunda', 'Nachuni'],
            'Begunia' => ['Begunia', 'Bolagarh Border', 'Pichukoli', 'Siko'],
            'Bolagarh' => ['Bolagarh', 'Arikama', 'Dabardhuapatna', 'Sanapadar'],
            'Chilika' => ['Chilika North', 'Kuhudi', 'Singeswar', 'Sorana'],
            'Tangi' => ['Tangi', 'Bhushandapur', 'Khurdha Coast', 'Uparsahi'],
        ];

        foreach ($khordhaBlocks as $blockName => $gps) {
            $block = Block::updateOrCreate(
                ['district_id' => $khordha->id, 'name' => $blockName],
                ['code' => strtoupper(substr($blockName, 0, 3)), 'is_active' => true]
            );
            foreach ($gps as $gpName) {
                GramPanchayat::updateOrCreate(
                    ['block_id' => $block->id, 'name' => $gpName],
                    ['code' => strtoupper(substr($blockName, 0, 3)) . '-' . strtoupper(substr($gpName, 0, 3)), 'is_active' => true]
                );
            }
        }

        // 3. Blocks & Gram Panchayats for Cuttack
        $cuttack = $districtModels['Cuttack'];
        $cuttackBlocks = [
            'Cuttack Sadar' => ['Bentkar', 'Paramahansa', 'Kandarpur', '42 Mouza'],
            'Baranga' => ['Baranga', 'Belgachia', 'Naranpur', 'Ramdaspur'],
            'Kantapada' => ['Kantapada', 'Brahmanasailo', 'Utikan', 'Dimiri'],
            'Niali' => ['Niali', 'Madhab', 'Alapua', 'Bilashuni'],
            'Salepur' => ['Salepur', 'Bahugram', 'Choudwar Border', 'Machhagaon'],
            'Athagarh' => ['Athagarh', 'Khuntuni', 'Radhagobindapur', 'Subarnapur GP'],
            'Banki' => ['Banki', 'Kalapathar', 'Charchika Rural', 'Baidyeswar'],
            'Tangi-Choudwar' => ['Tangi Rural', 'Kotsahi', 'Mangrajpur', 'Choudwar North'],
        ];

        foreach ($cuttackBlocks as $blockName => $gps) {
            $block = Block::updateOrCreate(
                ['district_id' => $cuttack->id, 'name' => $blockName],
                ['code' => strtoupper(substr($blockName, 0, 3)), 'is_active' => true]
            );
            foreach ($gps as $gpName) {
                GramPanchayat::updateOrCreate(
                    ['block_id' => $block->id, 'name' => $gpName],
                    ['code' => strtoupper(substr($blockName, 0, 3)) . '-' . strtoupper(substr($gpName, 0, 3)), 'is_active' => true]
                );
            }
        }

        // Add blocks for other 28 districts to ensure full coverage
        foreach ($districtModels as $name => $dModel) {
            if (in_array($name, ['Khordha', 'Cuttack'])) {
                continue;
            }
            $sampleBlocks = [$name . ' Sadar', $name . ' North', $name . ' South'];
            foreach ($sampleBlocks as $sbName) {
                $block = Block::updateOrCreate(
                    ['district_id' => $dModel->id, 'name' => $sbName],
                    ['code' => strtoupper(substr($dModel->code, 0, 2)) . '-' . strtoupper(substr($sbName, -5, 2)), 'is_active' => true]
                );
                GramPanchayat::updateOrCreate(
                    ['block_id' => $block->id, 'name' => $sbName . ' GP 1'],
                    ['code' => 'GP-' . $block->id . '-1', 'is_active' => true]
                );
                GramPanchayat::updateOrCreate(
                    ['block_id' => $block->id, 'name' => $sbName . ' GP 2'],
                    ['code' => 'GP-' . $block->id . '-2', 'is_active' => true]
                );
            }
        }

        // 4. Assign District Coordinators
        $dcKhordha = User::where('email', 'dc.khordha@osdp.gov.in')->first();
        if ($dcKhordha) {
            $dcKhordha->assignedDistricts()->syncWithoutDetaching([$khordha->id]);
        }

        $dcCuttack = User::where('email', 'dc.cuttack@osdp.gov.in')->first();
        if ($dcCuttack) {
            $dcCuttack->assignedDistricts()->syncWithoutDetaching([$cuttack->id]);
        }

        $monitor = User::where('email', 'monitor.das@osdp.gov.in')->first();
        if ($monitor) {
            $monitor->assignedDistricts()->syncWithoutDetaching([$khordha->id, $cuttack->id]);
        }

        // 5. Training Tiers
        $tiers = [
            [
                'code' => 'directorate',
                'name' => 'Directorate Level',
                'description' => 'Orientation and capacity building for Directorate Governance Officials.',
                'target_duration_hours' => 7.5,
                'target_batch_size' => 30,
            ],
            [
                'code' => 'district',
                'name' => 'District Level',
                'description' => 'Training for District Collectors, ADMs, DPOs, District Data Coordinators, IT Officers.',
                'target_duration_hours' => 7.5,
                'target_batch_size' => 40,
            ],
            [
                'code' => 'block',
                'name' => 'Block Level',
                'description' => 'Training for BDOs, Block IT Staff, Extension Officers, DEOs and field supervisors.',
                'target_duration_hours' => 7.5,
                'target_batch_size' => 30,
            ],
            [
                'code' => 'gram_panchayat',
                'name' => 'Gram Panchayat Level',
                'description' => 'Grassroots training for PEOs, Welfare Assistants, and frontline data handlers (Cluster mode supported).',
                'target_duration_hours' => 4.0,
                'target_batch_size' => 29,
            ],
            [
                'code' => 'citizen_awareness',
                'name' => 'Citizen Awareness Campaign',
                'description' => 'Community sensitization sessions for SHGs, youth, students, and citizens on digital privacy.',
                'target_duration_hours' => 2.0,
                'target_batch_size' => 50,
            ],
        ];

        foreach ($tiers as $tier) {
            TrainingTier::updateOrCreate(['code' => $tier['code']], $tier);
        }

        // 6. Training Modules (Modules 1 to 8 from RFP)
        $modules = [
            [
                'module_number' => 1,
                'code' => 'MOD-1',
                'title' => 'Introduction to Odisha State Data Policy (OSDP) 2.0',
                'description' => 'Purpose, background, citizen rights in Government data, data governance principles, and administrative roles.',
                'topics' => ['Purpose & background', 'Citizen rights', 'Data governance principles', 'Administrative roles'],
            ],
            [
                'module_number' => 2,
                'code' => 'MOD-2',
                'title' => 'Data Classification & Categorization',
                'description' => 'Personal data, non-personal data, sensitive data, metadata, and department-specific taxonomy.',
                'topics' => ['Personal vs Non-personal data', 'Sensitive personal data', 'Metadata standards', 'Department taxonomy'],
            ],
            [
                'module_number' => 3,
                'code' => 'MOD-3',
                'title' => 'Data Lifecycle Management',
                'description' => 'Stages of data: collection, processing, storage, usage, sharing, retention, archiving, and disposal.',
                'topics' => ['Collection & Processing', 'Storage & Usage', 'Data Retention Rules', 'Archiving & Secure Disposal'],
            ],
            [
                'module_number' => 4,
                'code' => 'MOD-4',
                'title' => 'Data Privacy & Security Practices',
                'description' => 'Access control, credential rules, secure file handling, DPDP Act 2023 compliance, device security, and breach incident reporting.',
                'topics' => ['Access control & passwords', 'DPDP Act compliance', 'Physical & digital file protection', 'Incident reporting'],
            ],
            [
                'module_number' => 5,
                'code' => 'MOD-5',
                'title' => 'Data Quality Management',
                'description' => 'Accuracy, completeness, consistency, validity, error reduction techniques, and GP/Block verification.',
                'topics' => ['Accuracy & Completeness', 'Consistency & Validity', 'Error rectification', 'Frontline validation methods'],
            ],
            [
                'module_number' => 6,
                'code' => 'MOD-6',
                'title' => 'Inter-Departmental Data Sharing Process',
                'description' => 'Protocols for data requests, approvals, data custodian roles, request templates, and consent safeguards.',
                'topics' => ['Permissible sharing criteria', 'Approval documentation', 'Custodian responsibilities', 'Standard request formats'],
            ],
            [
                'module_number' => 7,
                'code' => 'MOD-7',
                'title' => 'Documentation, Registers & Reporting',
                'description' => 'Maintenance of data registers, daily documentation SOPs, approval records, and standardized periodic reporting.',
                'topics' => ['Standard data registers', 'Frontline documentation SOPs', 'Approval logs', 'Prescribed MIS formats'],
            ],
            [
                'module_number' => 8,
                'code' => 'MOD-8',
                'title' => 'Case Studies & Practical Exercises',
                'description' => 'Simulations using synthetic non-personal datasets: scheme eligibility, duplicate resolution, and incident responses.',
                'topics' => ['Scheme eligibility cleaning', 'Duplicate record resolution', 'Sensitive doc handling', 'Practical simulations'],
            ],
        ];

        foreach ($modules as $m) {
            TrainingModule::updateOrCreate(['module_number' => $m['module_number']], $m);
        }

        // 7. Training Venues
        $venues = [
            [
                'district_id' => $khordha->id,
                'block_id' => Block::where('district_id', $khordha->id)->where('name', 'Bhubaneswar')->value('id'),
                'name' => 'OCAC Conference Hall, Bhubaneswar',
                'address' => 'OCAC Building, Acharya Vihar, Bhubaneswar - 751013',
                'capacity' => 60,
                'facilities' => ['projector' => true, 'ac' => true, 'internet' => true, 'audio_system' => true],
                'contact_person' => 'Ramesh Sahoo',
                'contact_phone' => '9437111222',
            ],
            [
                'district_id' => $khordha->id,
                'block_id' => Block::where('district_id', $khordha->id)->where('name', 'Khordha')->value('id'),
                'name' => 'DRDA Meeting Hall, Khordha',
                'address' => 'Collectorate Campus, Khordha',
                'capacity' => 50,
                'facilities' => ['projector' => true, 'ac' => true, 'internet' => true, 'audio_system' => true],
                'contact_person' => 'Pradeep Jena',
                'contact_phone' => '9437111333',
            ],
            [
                'district_id' => $cuttack->id,
                'block_id' => Block::where('district_id', $cuttack->id)->where('name', 'Cuttack Sadar')->value('id'),
                'name' => 'Collectorate Conference Hall, Cuttack',
                'address' => 'Collectorate Compound, Chandni Chowk, Cuttack',
                'capacity' => 55,
                'facilities' => ['projector' => true, 'ac' => true, 'internet' => true, 'audio_system' => true],
                'contact_person' => 'Alok Mohapatra',
                'contact_phone' => '9437111444',
            ],
            [
                'district_id' => $cuttack->id,
                'block_id' => Block::where('district_id', $cuttack->id)->where('name', 'Kantapada')->value('id'),
                'name' => 'Block Office Training Hall, Kantapada',
                'address' => 'Block Office Campus, Kantapada, Cuttack',
                'capacity' => 45,
                'facilities' => ['projector' => true, 'ac' => false, 'internet' => true, 'audio_system' => true],
                'contact_person' => 'Santosh Behera',
                'contact_phone' => '9437111555',
            ],
        ];

        foreach ($venues as $v) {
            Venue::updateOrCreate(
                ['district_id' => $v['district_id'], 'name' => $v['name']],
                $v
            );
        }

        // 8. System Settings
        $settings = [
            ['key' => 'submission_deadline_hours', 'value' => '48', 'description' => 'Submission SLA hours post training session completion', 'group' => 'operational'],
            ['key' => 'max_photo_size_mb', 'value' => '10', 'description' => 'Maximum allowed file size in MB for training photos', 'group' => 'upload'],
            ['key' => 'max_video_size_mb', 'value' => '100', 'description' => 'Maximum allowed file size in MB for short video clip (MP4)', 'group' => 'upload'],
            ['key' => 'max_pdf_size_mb', 'value' => '25', 'description' => 'Maximum allowed file size in MB for scanned PDFs (Form 1, 2, 3)', 'group' => 'upload'],
            ['key' => 'allowed_video_extensions', 'value' => 'mp4', 'description' => 'Allowed extensions for session video proof', 'group' => 'upload'],
            ['key' => 'allowed_image_extensions', 'value' => 'jpg,jpeg,png', 'description' => 'Allowed extensions for training session photos', 'group' => 'upload'],
            ['key' => 'allowed_doc_extensions', 'value' => 'pdf', 'description' => 'Allowed extensions for attendance, feedback and summary reports', 'group' => 'upload'],
            ['key' => 'contact_helpline_email', 'value' => 'support.osdp@ocac.in', 'description' => 'Support helpdesk contact email', 'group' => 'general'],
        ];

        foreach ($settings as $s) {
            SystemSetting::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}
