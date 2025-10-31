<?php

use Illuminate\Support\Facades\Schedule;

/*Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();*/

Schedule::command('app:scheduled-reminder-command')->runInBackground()->everyThirtyMinutes()->withoutOverlapping();
Schedule::command('app:clean-sys-command')->runInBackground()->hourly()->withoutOverlapping();

Schedule::command('app:check-email-command')->everyMinute()->withoutOverlapping()->runInBackground();

Schedule::command('app:process-queued-email-command')->everyMinute()->withoutOverlapping()->runInBackground();
Schedule::command('app:process-queued-s-m-s-command')->everyMinute()->withoutOverlapping()->runInBackground();

Schedule::command('app:refresh-facebook-token-command')->dailyAt('01:00')->withoutOverlapping()->runInBackground();
Schedule::command('app:get-facebook-posts-command')->everySixHours()->withoutOverlapping()->runInBackground();

Schedule::command('app:get-twitter-posts-command')->hourlyAt(0)->withoutOverlapping()->runInBackground();

Schedule::command('auth:clear-resets')->everySixHours()->withoutOverlapping()->runInBackground();

Schedule::command('app:won-lead-processing-command')->dailyAt('01:00')->withoutOverlapping()->runInBackground();

Schedule::command('app:loan-assignment-command')->dailyAt('5:30')->withoutOverlapping()->runInBackground();
Schedule::command('app:loan-re-assignment-command')->dailyAt('6:00')->withoutOverlapping()->runInBackground();

Schedule::command('app:task-due-reminder-command')->dailyAt('08:40')->withoutOverlapping()->runInBackground();

Schedule::command('app:update-gl-balances')->dailyAt('11:45')->withoutOverlapping()->runInBackground();

Schedule::command('app:fleet-day-playback-command')->dailyAt('00:30')->withoutOverlapping()->runInBackground();

//$schedule->command('')->everyFifteenMinutes();

//add a reminder sent in schedule users, leads and clients && add meeting type to meeting
