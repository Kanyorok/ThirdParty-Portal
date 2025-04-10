<?php

namespace App\Console\Commands;

use App\Exceptions\ErroredException;
use App\Services\ThirdParty\FacebookService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshFacebookTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-facebook-token-command';

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
        try {
            $service = new FacebookService();
        } catch (ErroredException $e) {
            Log::error('Facebook does not have a valid credentials');
            return;
        }

        $service->updateToken();
    }
}
