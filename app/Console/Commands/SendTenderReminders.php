<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTenderReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tender:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send stage deadline reminders to procurement officers and tender committee chairs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetDate = \Carbon\Carbon::now()->addDays(2)->toDateString();

        $stages = \App\Models\Procurement\TenderStage::whereDate('EndDate', $targetDate)
            ->with('tender.CreatedBy')
            ->get();

        foreach ($stages as $stage) {
            $tender = $stage->tender;

            $recipients = [];

            if ($tender->procurementOfficer) {
                $recipients[] = $tender->procurementOfficer->email;
            }

            if ($tender->chair) {
                $recipients[] = $tender->chair->email;
            }

            foreach ($recipients as $email) {
                Mail::to($email)->send(new \App\Mail\StageDeadlineReminder($tender, $stage));
            }
        }

        $this->info('Tender stage reminders sent.');
    }
}
