<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tot_venues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('name');
        });

        Schema::table('tot_batches', function (Blueprint $table) {
            $table->foreignId('tot_venue_id')
                ->nullable()
                ->after('training_date')
                ->constrained('tot_venues')
                ->nullOnDelete();
        });

        // Backfill existing TOT batches' venues into tot_venues and link tot_venue_id
        $existingBatches = DB::table('tot_batches')->orderBy('id')->get();
        foreach ($existingBatches as $batch) {
            $name = trim((string) $batch->venue_name);
            if ($name === '') {
                continue;
            }
            $address = $batch->venue_address ? trim((string) $batch->venue_address) : null;

            $existingVenue = DB::table('tot_venues')
                ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->whereRaw("LOWER(COALESCE(address, '')) = ?", [strtolower((string) ($address ?? ''))])
                ->first();

            if ($existingVenue) {
                $venueId = $existingVenue->id;
            } else {
                $now = now();
                $venueId = DB::table('tot_venues')->insertGetId([
                    'name' => $name,
                    'address' => $address,
                    'district_id' => $batch->district_id,
                    'is_active' => true,
                    'created_by' => $batch->created_by,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('tot_batches')->where('id', $batch->id)->update(['tot_venue_id' => $venueId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tot_batches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tot_venue_id');
        });

        Schema::dropIfExists('tot_venues');
    }
};
