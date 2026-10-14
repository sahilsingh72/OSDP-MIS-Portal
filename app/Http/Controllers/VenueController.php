<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Block;
use App\Models\District;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VenueController extends Controller
{
    /**
     * Display a listing of training venues.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('master-data.index');
    }

    /**
     * Show the form for creating a new training venue.
     */
    public function create(): View
    {
        $this->authorize('create', Venue::class);

        $districts = District::where('is_active', true)->orderBy('name')->get();

        return view('venues.create', compact('districts'));
    }

    /**
     * Store a newly created training venue in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Venue::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'district_id' => ['required', 'exists:districts,id'],
            'block_id' => ['nullable', 'exists:blocks,id'],
            'address' => ['nullable', 'string', 'max:500'],
            'capacity' => ['required', 'integer', 'min:5', 'max:1000'],
            'facilities' => ['nullable', 'array'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $facilities = [
            'projector' => (bool)$request->input('facilities.projector', false),
            'ac' => (bool)$request->input('facilities.ac', false),
            'internet' => (bool)$request->input('facilities.internet', false),
            'audio_system' => (bool)$request->input('facilities.audio_system', false),
        ];

        $venue = Venue::create([
            'district_id' => $validated['district_id'],
            'block_id' => $validated['block_id'] ?? null,
            'name' => trim($validated['name']),
            'address' => $validated['address'] ?? null,
            'capacity' => $validated['capacity'],
            'facilities' => $facilities,
            'contact_person' => $validated['contact_person'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::record('venue_created', $venue, null, $venue->toArray());

        return redirect()->route('master-data.index')
            ->with('status', "Training Venue '{$venue->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified training venue.
     */
    public function edit(Venue $venue): View
    {
        $this->authorize('update', $venue);

        $districts = District::where('is_active', true)->orderBy('name')->get();
        $blocks = Block::where('district_id', $venue->district_id)->where('is_active', true)->orderBy('name')->get();
        $batchesCount = $venue->batches()->count();

        return view('venues.edit', compact('venue', 'districts', 'blocks', 'batchesCount'));
    }

    /**
     * Update the specified training venue in storage.
     */
    public function update(Request $request, Venue $venue): RedirectResponse
    {
        $this->authorize('update', $venue);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'district_id' => ['required', 'exists:districts,id'],
            'block_id' => ['nullable', 'exists:blocks,id'],
            'address' => ['nullable', 'string', 'max:500'],
            'capacity' => ['required', 'integer', 'min:5', 'max:1000'],
            'facilities' => ['nullable', 'array'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $facilities = [
            'projector' => (bool)$request->input('facilities.projector', false),
            'ac' => (bool)$request->input('facilities.ac', false),
            'internet' => (bool)$request->input('facilities.internet', false),
            'audio_system' => (bool)$request->input('facilities.audio_system', false),
        ];

        $oldValues = $venue->toArray();

        $venue->update([
            'district_id' => $validated['district_id'],
            'block_id' => $validated['block_id'] ?? null,
            'name' => trim($validated['name']),
            'address' => $validated['address'] ?? null,
            'capacity' => $validated['capacity'],
            'facilities' => $facilities,
            'contact_person' => $validated['contact_person'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLog::record('venue_updated', $venue, $oldValues, $venue->toArray());

        return redirect()->route('master-data.index')
            ->with('status', "Training Venue '{$venue->name}' updated successfully.");
    }

    /**
     * Remove the specified training venue from storage.
     */
    public function destroy(Venue $venue): RedirectResponse
    {
        $this->authorize('delete', $venue);

        $batchesCount = $venue->batches()->count();

        if ($batchesCount > 0) {
            return redirect()->route('master-data.index')->withErrors([
                'venue' => "Cannot delete venue '{$venue->name}' because it has {$batchesCount} scheduled or completed training batch(es). To retire this venue from future selection without impacting past batch records, edit it and set its status to Inactive.",
            ]);
        }

        $oldValues = $venue->toArray();
        $name = $venue->name;

        $venue->delete();

        AuditLog::record('venue_deleted', null, $oldValues, null);

        return redirect()->route('master-data.index')
            ->with('status', "Training Venue '{$name}' was deleted successfully.");
    }
}
