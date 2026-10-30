<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Block;
use App\Models\District;
use App\Models\FieldTrainer;
use App\Models\GramPanchayat;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class FieldTrainerController extends Controller
{
    /**
     * Display listing of Field Trainers.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $this->authorizeAccess($user);

        $query = FieldTrainer::with(['user', 'district', 'block', 'gramPanchayat', 'creator'])
            ->with(['user.trainerBatches']);

        // District Scoping for DLC (District Coordinator)
        if ($user->hasRole('district-coordinator')) {
            $assignedDistrictIds = $user->assignedDistricts->pluck('id')->toArray();
            $query->whereIn('district_id', $assignedDistrictIds);
        } elseif ($request->filled('district_id')) {
            $districtId = $request->input('district_id');
            $query->where('district_id', $districtId);
        }

        if ($request->filled('block_id')) {
            $query->where('block_id', $request->input('block_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $cleanNumeric = preg_replace('/[^0-9]/', '', $search);
            $query->where(function ($q) use ($search, $cleanNumeric) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
                if (!empty($cleanNumeric)) {
                    $q->orWhere('phone', 'like', "%{$cleanNumeric}%");
                }
            });
        }

        $trainers = $query->orderBy('name')->paginate(15)->withQueryString();

        // Calculate summary counters
        $baseCountQuery = FieldTrainer::query();
        if ($user->hasRole('district-coordinator')) {
            $assignedDistrictIds = $user->assignedDistricts->pluck('id')->toArray();
            $baseCountQuery->whereIn('district_id', $assignedDistrictIds);
        }
        $totalTrainers = (clone $baseCountQuery)->count();
        $activeTrainers = (clone $baseCountQuery)->where('is_active', true)->count();
        $inactiveTrainers = $totalTrainers - $activeTrainers;

        // Districts for dropdown filter
        $filterDistricts = $user->hasRole('district-coordinator')
            ? $user->assignedDistricts
            : District::where('is_active', true)->orderBy('name')->get();

        return view('trainers.index', compact(
            'trainers',
            'filterDistricts',
            'totalTrainers',
            'activeTrainers',
            'inactiveTrainers'
        ));
    }

    /**
     * Show form for creating a new Field Trainer.
     */
    public function create(): View
    {
        $user = Auth::user();
        $this->authorizeCreate($user);

        // Scoped districts for dropdown
        $districts = $user->hasRole('district-coordinator')
            ? $user->assignedDistricts
            : District::where('is_active', true)->orderBy('name')->get();

        $defaultDistrictId = ($user->hasRole('district-coordinator') && $districts->count() === 1)
            ? $districts->first()->id
            : null;

        return view('trainers.create', compact('districts', 'defaultDistrictId'));
    }

    /**
     * Store a newly created Field Trainer in database.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $this->authorizeCreate($user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email', 'unique:field_trainers,email'],
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'designation' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'district_id' => ['required', 'exists:districts,id'],
            'block_id' => ['required', 'exists:blocks,id'],
            'gram_panchayat_id' => ['required', 'exists:gram_panchayats,id'],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Trainer name is mandatory.',
            'email.required' => 'Email address is mandatory.',
            'email.unique' => 'A user account with this email address already exists.',
            'phone.required' => 'Contact phone number is mandatory.',
            'phone.regex' => 'Contact phone number must be a valid 10-digit mobile number starting with 6, 7, 8, or 9.',
            'designation.required' => 'Designation is mandatory.',
            'department.required' => 'Department is mandatory.',
            'district_id.required' => 'District selection is mandatory.',
            'block_id.required' => 'Block selection is mandatory.',
            'gram_panchayat_id.required' => 'Gram Panchayat selection is mandatory.',
        ]);

        // DLC district-scope authorization
        if ($user->hasRole('district-coordinator')) {
            $allowedDistrictIds = $user->assignedDistricts->pluck('id')->toArray();
            if (!in_array((int) $validated['district_id'], $allowedDistrictIds, true)) {
                return back()->withInput()->withErrors([
                    'district_id' => 'You are only authorized to provision field trainers within your assigned district.',
                ]);
            }
        }

        // Validate geographic hierarchy: Block must belong to District
        $block = Block::where('id', $validated['block_id'])
            ->where('district_id', $validated['district_id'])
            ->first();
        if (!$block) {
            return back()->withInput()->withErrors([
                'block_id' => 'The selected block does not belong to the chosen district.',
            ]);
        }

        // Validate geographic hierarchy: GP must belong to Block
        $gp = GramPanchayat::where('id', $validated['gram_panchayat_id'])
            ->where('block_id', $validated['block_id'])
            ->first();
        if (!$gp) {
            return back()->withInput()->withErrors([
                'gram_panchayat_id' => 'The selected Gram Panchayat does not belong to the chosen block.',
            ]);
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);

        // Check phone uniqueness in users and field_trainers
        if (User::where('phone', $cleanPhone)->exists() || FieldTrainer::where('phone', $cleanPhone)->exists()) {
            return back()->withInput()->withErrors([
                'phone' => 'A user account with this phone number already exists.',
            ]);
        }

        $password = !empty($validated['password']) ? $validated['password'] : 'Trainer@12345';

        $fieldTrainer = DB::transaction(function () use ($validated, $cleanPhone, $password, $request) {
            $isActive = $request->has('is_active') ? $request->boolean('is_active') : true;

            // 1. Create User login account (strictly login & credentials)
            $userAccount = User::create([
                'name' => trim($validated['name']),
                'email' => strtolower(trim($validated['email'])),
                'phone' => $cleanPhone,
                'password' => Hash::make($password),
                'is_active' => $isActive,
            ]);

            $userAccount->assignRole('field-trainer');
            $userAccount->assignedDistricts()->sync([$validated['district_id']]);

            // 2. Create Field Trainer profile in dedicated field_trainers table
            $trainer = FieldTrainer::create([
                'user_id' => $userAccount->id,
                'name' => trim($validated['name']),
                'email' => strtolower(trim($validated['email'])),
                'phone' => $cleanPhone,
                'designation' => trim($validated['designation']),
                'department' => trim($validated['department']),
                'district_id' => $validated['district_id'],
                'block_id' => $validated['block_id'],
                'gram_panchayat_id' => $validated['gram_panchayat_id'],
                'is_active' => $isActive,
                'created_by' => Auth::id(),
            ]);

            AuditLog::record('field_trainer_created', $trainer, null, [
                'trainer_id' => $trainer->id,
                'user_id' => $userAccount->id,
                'name' => $trainer->name,
                'email' => $trainer->email,
                'phone' => $trainer->phone,
                'designation' => $trainer->designation,
                'department' => $trainer->department,
                'district_id' => $trainer->district_id,
                'block_id' => $trainer->block_id,
                'gram_panchayat_id' => $trainer->gram_panchayat_id,
                'created_by' => Auth::id(),
            ]);

            return $trainer;
        });

        return redirect()->route('trainers.index')
            ->with('status', "Field Trainer '{$fieldTrainer->name}' provisioned successfully with user account ({$fieldTrainer->email}).");
    }

    /**
     * Show form for editing an existing Field Trainer.
     */
    public function edit(FieldTrainer $trainer): View
    {
        $user = Auth::user();
        $this->authorizeManage($user, $trainer);

        $districts = $user->hasRole('district-coordinator')
            ? $user->assignedDistricts
            : District::where('is_active', true)->orderBy('name')->get();

        $blocks = $trainer->district_id
            ? Block::where('district_id', $trainer->district_id)->where('is_active', true)->orderBy('name')->get()
            : collect();

        $gps = $trainer->block_id
            ? GramPanchayat::where('block_id', $trainer->block_id)->where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('trainers.edit', compact('trainer', 'districts', 'blocks', 'gps'));
    }

    /**
     * Update Field Trainer profile and sync login account.
     */
    public function update(Request $request, FieldTrainer $trainer): RedirectResponse
    {
        $user = Auth::user();
        $this->authorizeManage($user, $trainer);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email,' . $trainer->user_id,
                'unique:field_trainers,email,' . $trainer->id,
            ],
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'designation' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'district_id' => ['required', 'exists:districts,id'],
            'block_id' => ['required', 'exists:blocks,id'],
            'gram_panchayat_id' => ['required', 'exists:gram_panchayats,id'],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'Trainer name is mandatory.',
            'email.required' => 'Email address is mandatory.',
            'phone.required' => 'Contact phone number is mandatory.',
            'phone.regex' => 'Contact phone number must be a valid 10-digit mobile number starting with 6, 7, 8, or 9.',
            'designation.required' => 'Designation is mandatory.',
            'department.required' => 'Department is mandatory.',
            'district_id.required' => 'District selection is mandatory.',
            'block_id.required' => 'Block selection is mandatory.',
            'gram_panchayat_id.required' => 'Gram Panchayat selection is mandatory.',
        ]);

        if ($user->hasRole('district-coordinator')) {
            $allowedDistrictIds = $user->assignedDistricts->pluck('id')->toArray();
            if (!in_array((int) $validated['district_id'], $allowedDistrictIds, true)) {
                return back()->withInput()->withErrors([
                    'district_id' => 'You are only authorized to manage field trainers within your assigned district.',
                ]);
            }
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);
        $phoneExistsInUsers = User::where('id', '!=', $trainer->user_id)->where('phone', $cleanPhone)->exists();
        $phoneExistsInTrainers = FieldTrainer::where('id', '!=', $trainer->id)->where('phone', $cleanPhone)->exists();

        if ($phoneExistsInUsers || $phoneExistsInTrainers) {
            return back()->withInput()->withErrors([
                'phone' => 'Another user account with this phone number already exists.',
            ]);
        }

        // Validate geographic hierarchy: Block must belong to District
        $block = Block::where('id', $validated['block_id'])
            ->where('district_id', $validated['district_id'])
            ->first();
        if (!$block) {
            return back()->withInput()->withErrors([
                'block_id' => 'The selected block does not belong to the chosen district.',
            ]);
        }

        // Validate geographic hierarchy: GP must belong to Block
        $gp = GramPanchayat::where('id', $validated['gram_panchayat_id'])
            ->where('block_id', $validated['block_id'])
            ->first();
        if (!$gp) {
            return back()->withInput()->withErrors([
                'gram_panchayat_id' => 'The selected Gram Panchayat does not belong to the chosen block.',
            ]);
        }

        $oldData = $trainer->toArray();

        DB::transaction(function () use ($trainer, $validated, $cleanPhone, $oldData) {
            $isActive = (bool) $validated['is_active'];

            // 1. Update user login account
            $userUpdate = [
                'name' => trim($validated['name']),
                'email' => strtolower(trim($validated['email'])),
                'phone' => $cleanPhone,
                'is_active' => $isActive,
            ];

            if (!empty($validated['password'])) {
                $userUpdate['password'] = Hash::make($validated['password']);
            }

            if ($trainer->user) {
                $trainer->user->update($userUpdate);
                $trainer->user->assignedDistricts()->sync([$validated['district_id']]);
            }

            // 2. Update dedicated field_trainers profile
            $trainer->update([
                'name' => trim($validated['name']),
                'email' => strtolower(trim($validated['email'])),
                'phone' => $cleanPhone,
                'designation' => trim($validated['designation']),
                'department' => trim($validated['department']),
                'district_id' => $validated['district_id'],
                'block_id' => $validated['block_id'],
                'gram_panchayat_id' => $validated['gram_panchayat_id'],
                'is_active' => $isActive,
            ]);

            AuditLog::record('field_trainer_updated', $trainer, $oldData, $trainer->toArray());
        });

        return redirect()->route('trainers.index')
            ->with('status', "Field Trainer '{$trainer->name}' profile updated successfully.");
    }

    /**
     * Toggle trainer active / suspended status across profile and login account.
     */
    public function toggleStatus(FieldTrainer $trainer): RedirectResponse
    {
        $user = Auth::user();
        $this->authorizeManage($user, $trainer);

        $newStatus = !$trainer->is_active;

        DB::transaction(function () use ($trainer, $newStatus) {
            $trainer->update(['is_active' => $newStatus]);

            if ($trainer->user) {
                $trainer->user->update(['is_active' => $newStatus]);
            }

            AuditLog::record('field_trainer_status_toggled', $trainer, null, ['is_active' => $newStatus]);
        });

        $statusStr = $newStatus ? 'activated' : 'deactivated';

        return back()->with('status', "Field Trainer '{$trainer->name}' has been {$statusStr}.");
    }

    /**
     * Authorization check for viewing trainers.
     */
    protected function authorizeAccess(User $user): void
    {
        if (!$user->can('trainers.view') && !$user->hasRole(['super-admin', 'pmu', 'district-coordinator', 'ocac'])) {
            abort(403, 'Unauthorized. You do not have permission to access the Field Trainers directory.');
        }
    }

    /**
     * Authorization check for creating trainers.
     */
    protected function authorizeCreate(User $user): void
    {
        if (!$user->can('trainers.create') && !$user->hasRole(['super-admin', 'pmu', 'district-coordinator'])) {
            abort(403, 'Unauthorized. Only District Coordinators (DLC), PMU, and Super Admin can create Field Trainers.');
        }
    }

    /**
     * Authorization check for managing a specific trainer.
     */
    protected function authorizeManage(User $user, FieldTrainer $trainer): void
    {
        if ($user->hasRole(['super-admin', 'pmu'])) {
            return;
        }

        if ($user->hasRole('district-coordinator')) {
            $allowedDistrictIds = $user->assignedDistricts->pluck('id')->toArray();
            if ($trainer->district_id && in_array((int) $trainer->district_id, $allowedDistrictIds, true)) {
                return;
            }
        }

        abort(403, 'Unauthorized to modify this field trainer.');
    }
}
