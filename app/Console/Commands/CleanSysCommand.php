<?php

namespace App\Console\Commands;

use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\CRM\MarketingList;
use App\Models\ThirdParies\Competitor;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Storage;
use Throwable;

class CleanSysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-sys-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check processing status for MarketingList and Competitor and clear it if it is older than 2 hours. Clean up temp folder. Every 1 hour.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $actor = SystemHelper::user();

        try {
            $this->_cleanTmp();
        } catch (Exception | Throwable) {
        }

        try {
            $this->_checkMarketingList($actor);
        } catch (Exception | Throwable) {
        }

        try {
            $this->_checkCompetitors($actor);
        } catch (Exception | Throwable) {
        }
    }

    protected function _cleanTmp(): void
    {
        $tempFolder = Storage::disk('temp')->path('');
        $files = scandir($tempFolder);

        foreach ($files as $file) {
            $filePath = $tempFolder . DIRECTORY_SEPARATOR . $file;

            if (is_file($filePath)) {
                $lastModified = filemtime($filePath);
                $twoHoursAgo = now()->subHours(2)->timestamp;

                if ($lastModified < $twoHoursAgo) {
                    unlink($filePath);
                }
            }
        }
    }

    private function _checkMarketingList(User $actor): void
    {
        foreach (MarketingList::query()->whereNotNull('t_MarketingLists.Processing')->where('t_MarketingLists.ModifiedOn', '<', Carbon::now()->subHour())->get() as $list) {
            try {
                DB::transaction(static function () use ($actor, $list) {
                    $list->update(['Processing' => null]);

                    activity()->performedOn($list)->causedBy($actor)->log('System cleared "Processing" status for MarketingList ID: ' . $list->slug);
                });
            } catch (Exception | Throwable $e) {
                Log::error('System cleared "Processing" status for MarketingList ID: ' . $list->slug . ' FAILED');
                Log::error($e);
            }
        }
    }

    private function _checkCompetitors(User $actor): void
    {
        foreach (Competitor::query()->whereNotNull('t_Competitors.Processing')->where('t_Competitors.ModifiedOn', '<', Carbon::now()->subMinutes(90))->get() as $competitor) {
            try {
                DB::transaction(static function () use ($actor, $competitor) {
                    $competitor->update(['Processing' => null]);

                    activity()->performedOn($competitor)->causedBy($actor)->log('System cleared "Processing" status for Competitor ID: ' . $competitor->CompetitorID);
                });
            } catch (Exception | Throwable $e) {
                Log::error('System cleared "Processing" status for Competitor ID: ' . $competitor->CompetitorID . ' FAILED');
                Log::error($e);
            }
        }
    }
}
