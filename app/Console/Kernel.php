<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
    $schedule->command('trips:start-pending')->everyMinute();

    }

    

    // ✅ Register commands from the app/Console/Commands directory
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
