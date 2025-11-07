<?php

namespace App\Console\Commands;

use App\Enums\EmailPriorityEnum;
use App\Models\CRM\Schedule;
use App\Models\CRM\ScheduleUser;
use App\Services\HRM\UserService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScheduledReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scheduled-reminder-command';

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
        $schedules = Schedule::query()->whereBetween('t_Schedule.StartOn', [Carbon::now(), Carbon::now()->addMinutes(90)])->get();
        foreach ($schedules as $schedule) {
            $users = $schedule->users()->wherePivotNull('t_ScheduleUsers.ReminderOn')->get(['t_Users.Email', 't_Users.Name', 't_Users.Id']);
            if ($users->isEmpty()) {
                return;
            }

            $emails = $users->map(function ($user) {
                return [$user->Name => $user->Email];
            });
            $service = new ScheduleService($schedule);

            (new UserService($users->first()))->sendEmail($service->getEmailSubject(true), $service->getEmailContent(true), $emails->toArray(), true, EmailPriorityEnum::Important);

            ScheduleUser::query()->whereIn('t_ScheduleUsers.UserID', $users->pluck('Id')->toArray())
                ->where('ScheduleId', $schedule->ScheduleID)->update(['t_ScheduleUsers.ReminderOn' => Carbon::now()]);
        }
    }
}
