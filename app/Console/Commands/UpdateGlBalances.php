<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateGlBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-gl-balances';

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
            DB::transaction(static function () {
                // Execute the stored procedure and capture the result
                if (! DB::statement('EXEC p_updateGLBalances')) {
                    throw new \RuntimeException('Error executing stored procedure');
                }
            });
        } catch (\Throwable $e) {
            Log::error('Error executing stored procedure: ' . $e->getMessage());
        }
    }
}
