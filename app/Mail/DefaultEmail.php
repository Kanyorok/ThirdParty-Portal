<?php

namespace App\Mail;

use App\Models\Communication\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

//use Illuminate\Contracts\Queue\ShouldQueue;

class DefaultEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    //, InteractsWithQueue;

    /**
     * Create a new message instance.
     */
    public function __construct(public Email $crmEmail)
    {
        $this->priority($this->crmEmail->Priority->intPriority());
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: collect($this->crmEmail->To)->flatten()->toArray(),
            cc: collect($this->crmEmail->CC)->flatten()->toArray(),
            bcc: $this->crmEmail->BCC,
            subject: $this->crmEmail->Subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'crm.emails.template',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $data = collect();
        foreach ($this->crmEmail->attachments()->get() as $attachment) {
            $data->add(Attachment::fromData(static fn() => base64_decode($attachment->Image), $attachment->Name)
                ->withMime($attachment->MIMEType));
        }
        return $data->toArray();
    }
}
