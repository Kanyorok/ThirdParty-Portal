<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PartyService;
use App\Services\UserService;
use Illuminate\Console\Command;

class TaskDueReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:task-due-reminder-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::query()->whereHas('tasks', function ($query) {
            $query->whereBetween('Dated', [now()->startOfDay(), now()->endOfDay()])->whereNull('CompletedOn');
        })->with('tasks', function ($query) {
            $query->whereBetween('Dated', [now()->startOfDay(), now()->endOfDay()])->whereNull('CompletedOn');
        })->get();

        foreach ($users as $user) {

            $body = '<p>Dear ' . $user->UserID . ',</p>';
            $body .= '<p>This is a courteous reminder that you have the following task(s) due today:</p>';
            $body .= '<ul>';

            foreach ($user->tasks as $task) {
                $body .= '<li>Task: ' . $task->Notes . ' - Related to: ' . (new PartyService($task->party))->simplified() . '</li>';
            }
            $body .= '</ul>';
            $body .= '<p>Please ensure these tasks are completed on time.</p>';
            $body .= '<p>Thank you and have a productive day!</p>';


            (new UserService($user))->sendEmail('Friendly Reminder: Task(s) Due Today ',
                body: $body,
                immediate: true);
        }
    }
}
