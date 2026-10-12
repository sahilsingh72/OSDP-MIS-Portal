<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\District;
use App\Models\GramPanchayat;
use App\Models\TrainingModule;
use App\Models\TrainingTier;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    /**
     * Display master data catalog.
     */
    public function index(): View
    {
        $this->authorize('viewAny', \App\Models\District::class);

        $districts = District::withCount('blocks', 'venues', 'batches')->orderBy('name')->get();
        $tiers = TrainingTier::withCount('batches')->get();
        $modules = TrainingModule::orderBy('module_number')->get();
        $venues = Venue::with('district', 'block')->latest()->paginate(10);

        return view('master-data.index', compact('districts', 'tiers', 'modules', 'venues'));
    }

    /**
     * API: Get blocks for a specific district (dependent dropdown).
     */
    public function getBlocks(int $districtId): JsonResponse
    {
        $blocks = Block::where('district_id', $districtId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($blocks);
    }

    /**
     * API: Get Gram Panchayats for a specific block (dependent dropdown).
     */
    public function getGramPanchayats(int $blockId): JsonResponse
    {
        $gps = GramPanchayat::where('block_id', $blockId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($gps);
    }
}
