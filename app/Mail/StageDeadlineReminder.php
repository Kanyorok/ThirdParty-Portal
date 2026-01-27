<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StageDeadlineReminder extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $stage;
    public $tender;

    public function __construct($stage, $tender)
    {
        $this->stage = $stage;
        $this->tender = $tender;
    }

    public function build()
    {
        return $this->view('procurement.emails.stage_reminder')
                    ->with([
                        'stage' => $this->stage,
                        'tender' => $this->tender,
                    ]);
    }
}
