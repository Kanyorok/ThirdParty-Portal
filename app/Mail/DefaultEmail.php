<?php

namespace App\Mail;

use App\Models\Communication\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


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
        $to = collect($this->crmEmail->To)->flatten()->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))->values()->toArray();
        $cc = collect($this->crmEmail->CC)->flatten()->toArray();
        $bcc = collect($this->crmEmail->BCC)->flatten()->toArray();

        // NEVER expose CC as a visible header. Merge any CC entries into BCC so they're hidden.
        $mergedBcc = array_values(array_filter(array_merge($bcc, $cc)));

        return new Envelope(
            to: $to,
            cc: [],
            bcc: $mergedBcc,
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
            $data->add(Attachment::fromData(static fn () => base64_decode($attachment->Image), $attachment->Name)
                ->withMime($attachment->MIMEType));
        }

        return $data->toArray();
    }
}
