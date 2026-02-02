<?php

namespace App\Console\Commands;

use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\Core\GPSCoordinate;
use App\Models\Fleet\FleetVehicle;
use App\Services\ThirdParty\iTrackService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FleetDayPlaybackCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fleet-day-playback-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run at 04:00 am at the beginning of the day from the previous day, check if complete';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            $service = new iTrackService();
        } catch (\Exception $e) {
            return;
        }
        $actor = SystemHelper::user();
        $start = now()->subDay()->startOfDay();
        $end = now()->subDay()->endOfDay();
        $vehicles = FleetVehicle::query()->whereNotNull('TrackerNo')
            ->whereDoesntHave('coordinates', function ($query) use ($start, $end) {
                $query->whereBetween('CreatedOn', [$start, $end]);
            })->get();
        foreach ($vehicles as $vehicle) {
            $this->updateVehicleCoordinates($vehicle, $service, $start, $end, $actor);
            sleep(1);
        }
    }

    private function updateVehicleCoordinates(FleetVehicle $vehicle, iTrackService $service, Carbon $start, Carbon $end, User $actor): void
    {
        try {
            $coordinates = $service->getPlayback($vehicle->TrackerNo, $start, $end)[0];
        } catch (ErroredException $e) {
            return;
        }

        $records = collect(explode(';', $coordinates))
            ->filter()
            ->map(function ($coordinate) use ($actor, $vehicle) {
                [$longitude, $latitude, $gpstime, $speed, $course] = explode(',', $coordinate);
                $date = now()->timestamp($gpstime);

                // longitude,latitude,gpstime,speed,course
                return [
                    'Source' => $vehicle->getMorphClass(),
                    'SourceID' => $vehicle->Id,
                    'Latitude' => (float)$latitude,
                    'Longitude' => (float)$longitude,
                    'Extra' => json_encode(['speed' => (float)$speed, 'course' => (float)$course, 'gpstime' => $gpstime], JSON_THROW_ON_ERROR),
                    'CreatedOn' => $date,
                    'CreatedBy' => $actor->Id,
                    'ModifiedOn' => $date,
                    'ModifiedBy' => $actor->Id,
                ];
            });

        if ($records->isEmpty()) {
            return;
        }

        // Insert records in chunks of 210 to stay well below the 2100 parameter limit 2100/10
        foreach ($records->chunk(210) as $chunk) {
            GPSCoordinate::query()->insert($chunk->toArray());
        }
    }
}
