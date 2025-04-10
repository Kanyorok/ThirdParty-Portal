<?php

namespace App\Console\Commands;

use App\Enums\EmailStatusEnum;
use App\Models\CrmEmail;
use App\Services\CRMEmailService;
use Illuminate\Console\Command;

class ProcessQueuedEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-queued-email-command';

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
        $emails = CrmEmail::query()->where('Status', EmailStatusEnum::Queued)->get();
        foreach ($emails as $email) {
            (new CRMEmailService($email))->send(true);
        }
    }
}
