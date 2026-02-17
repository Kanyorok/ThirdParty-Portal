<?php

namespace App\Console\Commands;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Fleet\FleetTripLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class StartPendingTrips extends Command
{
    protected $signature = 'trips:start-pending';
    protected $description = 'Start trips automatically when start time is reached';

    public function handle()
    {
        $now = Carbon::now();

        // ✅ Using Description field for lookup
        $approvedId = CodeDetail::where('CodeID', 'TripStatus')->where('Description', 'Approved')->value('ID');
        $ongoingId = CodeDetail::where('CodeID', 'TripStatus')->where('Description', 'Ongoing')->value('ID');

        if (! $approvedId || ! $ongoingId) {
            $this->error('Trip status IDs not found!');
            \Log::error('Trip status IDs not found for scheduled command');

            return;
        }

        $this->info("Looking for trips to start...");
        $this->info("Approved Status ID: {$approvedId}");
        $this->info("Ongoing Status ID: {$ongoingId}");
        $this->info("Current time: {$now}");

        // Find trips that should be started
        $trips = FleetTripLog::where('Status', $approvedId)
            ->where(function ($query) use ($now) {
                $query->where(function ($q) use ($now) {
                    // If both date and time are set
                    $q->whereNotNull('TripStartDate')
                        ->whereNotNull('StartTime')
                        ->whereRaw("CONCAT(TripStartDate, ' ', StartTime) <= ?", [$now->format('Y-m-d H:i:s')]);
                })->orWhere(function ($q) use ($now) {
                    // If only date is set (start at beginning of day)
                    $q->whereNotNull('TripStartDate')
                        ->whereNull('StartTime')
                        ->whereDate('TripStartDate', '<=', $now->format('Y-m-d'));
                });
            })
            ->get();

        $this->info("Found {$trips->count()} trip(s) to process");

        foreach ($trips as $trip) {
            try {
                $oldStatus = $trip->TripStatus;
                $trip->update(['TripStatus' => $ongoingId]);

                activity()
                    ->performedOn($trip)
                    ->event('trip-started')
                    ->log("Trip #{$trip->TripNo} started automatically at {$now}");

                $this->info("Trip #{$trip->TripNo} started successfully (Status: Approved → Ongoing)");
            } catch (\Exception $e) {
                $this->error("Failed to start trip #{$trip->TripNo}: " . $e->getMessage());
                \Log::error("Failed to start trip #{$trip->TripNo}: " . $e->getMessage());
            }
        }

        $this->info(count($trips) . ' trip(s) started successfully.');
    }
}
