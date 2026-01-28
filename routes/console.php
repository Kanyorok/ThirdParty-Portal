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

Schedule::command('trips:start-pending')->everyMinute()->withoutOverlapping()->runInBackground();


Schedule::command('app:won-lead-processing-command')->dailyAt('01:00')->withoutOverlapping()->runInBackground();

Schedule::command('app:loan-assignment-command')->dailyAt('5:30')->withoutOverlapping()->runInBackground();
Schedule::command('app:loan-re-assignment-command')->dailyAt('6:00')->withoutOverlapping()->runInBackground();

Schedule::command('app:task-due-reminder-command')->dailyAt('08:40')->withoutOverlapping()->runInBackground();

Schedule::command('app:task-due-reminder-command')->dailyAt('08:40')->withoutOverlapping()->runInBackground();


Schedule::command('app:fleet-day-playback-command')->dailyAt('00:30')->withoutOverlapping()->runInBackground();


//add a reminder sent in schedule users, leads and clients && add meeting type to meeting

\Illuminate\Support\Facades\Artisan::command('fix:rfq', function () {
    // Clear cache first
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Ensure permission exists and is assigned to admin
    $permName = 'workflowstage_quotation_final_stage';
    $role = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
    $perm = \App\Models\Core\Approval\Permission::where('name', $permName)->first();

    if ($role && $perm) {
        try {
            $role->givePermissionTo($perm);
            $this->info("Assigned $permName to admin");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    // Submit RFQ if not already pending
    $pending = \Illuminate\Support\Facades\DB::table('t_WorkFlowPending')->where('Source', 't_RFQ')->where('SourceID', 1)->exists();
    if (! $pending) {
        $s = app(\App\Services\Procurement\RFQ\RFQWorkflowService::class);
        $r = \App\Models\Procurement\RFQ::find(1);
        $u = \App\Models\Auth\User::find(4);
        if (! $u) {
            $u = \App\Models\Auth\User::first();
        }
        $s->submit($r, $u, 'Manual Fix Submission');
        $this->info('Submitted RFQ 1');
    } else {
        $this->info('RFQ 1 is already pending');
    }
});
