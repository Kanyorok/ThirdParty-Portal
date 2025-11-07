<?php

namespace App\Console\Commands;

use App\Enums\EmailStatusEnum;
use App\Models\Communication\SMS;
use App\Services\SMSService;
use Illuminate\Console\Command;

class ProcessQueuedSMSCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-queued-s-m-s-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $messages = SMS::query()->where('Status', EmailStatusEnum::Queued)->get();
        foreach ($messages as $message) {
            (new SMSService($message))->send(true);
        }
    }
}
