<?php

namespace App\Console\Commands\Procurement;

use App\Enums\Procurement\PrequalificationRoundEnum;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpirePrequalificationRounds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prequalification:expire-rounds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire prequalification rounds that have passed their end date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting check for expired prequalification rounds...');

        // Find rounds that are NOT Closed/Expired and have EndDate < Today (or Yesterday?)
        // "if the current date is past the End date"
        // So if EndDate is yesterday, it is expired.
        // If EndDate is today, it is still valid until end of day? Usually yes.
        // So strict comparison: EndDate < Today (start of day)

        $today = Carbon::today();

        $rounds = PrequalificationRound::query()
            ->whereDate('EndDate', '<', $today)
            ->whereNotIn('Status', [PrequalificationRoundEnum::Closed, PrequalificationRoundEnum::Expired])
            ->get();

        $count = $rounds->count();

        if ($count === 0) {
            $this->info('No expired rounds found.');

            return;
        }

        $this->info("Found {$count} rounds to expire.");

        foreach ($rounds as $round) {
            try {
                $round->update([
                    'Status' => PrequalificationRoundEnum::Expired,
                ]);
                $this->info("Expired Round ID: {$round->RoundID} - {$round->Title}");
            } catch (\Exception $e) {
                $this->error("Failed to expire Round ID {$round->RoundID}: " . $e->getMessage());
                Log::error("Failed to expire prequalification round {$round->RoundID}: " . $e->getMessage());
            }
        }

        $this->info('Finished expiring rounds.');
    }
}
