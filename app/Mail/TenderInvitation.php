<?php

namespace App\Mail;

use App\Models\Procurement\Tender;
use App\Models\ThirdParies\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenderInvitation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $tender;
    public $supplier;
    public $supplierName;
    public $submissionDeadline;
    public $portalUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Tender $tender, Supplier $supplier)
    {
        $this->tender = $tender;
        $this->supplier = $supplier;
        $this->supplierName = $supplier->thirdParty->ThirdPartyName ?? 'Valued Supplier';
        $this->submissionDeadline = $tender->SubmissionDeadline;
        $this->portalUrl = config('app.url') . '/supplier/tenders/' . $tender->Id;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tender Invitation - ' . $this->tender->TenderNo . ': ' . $this->tender->Title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tender-invitation',
            with: [
                'tender' => $this->tender,
                'supplier' => $this->supplier,
                'supplierName' => $this->supplierName,
                'submissionDeadline' => $this->submissionDeadline,
                'portalUrl' => $this->portalUrl,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
