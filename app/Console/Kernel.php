<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    // ✅ Register your custom Artisan commands
    protected $commands = [
        \App\Console\Commands\SendTenderReminders::class,
    ];

    // ✅ Define your task scheduling here
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('tender:send-reminders')->dailyAt('15:00');
    // $schedule->command('tender:send-reminders')->daily();
     $schedule->job(new \App\Jobs\CleanExpiredTokens())->daily();

      // Run p_ProcessWorkflowStages every minute to check and advance stages
        $schedule->call(function () {
            DB::statement('EXEC p_ProcessWorkflowStages');
        })->everyMinute();
        // Run p_EscalateOverdueWorkflows daily (or as needed)
        $schedule->call(function () {
            DB::statement('EXEC p_EscalateOverdueWorkflows @RunBy = 1');  // Use a system user ID, e.g., 1 for ERPSYS
        })->daily();
    }

    // ✅ Register commands from the app/Console/Commands directory
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
