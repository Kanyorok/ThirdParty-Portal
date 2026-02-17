<?php

namespace App\Jobs;

use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\DMS\DMSSignature;
use App\Models\DMS\Document;
use App\Services\DMS\Verification\SignatureService;
use App\Services\HRM\UserService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Log;
use Throwable;

class SignDocumentJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $timeout = 43200;

    public int $tries = 1;

    public bool $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(public Document $document, public DMSSignature $signature, public User $actor, public int $Pages)
    {
    }

    public function uniqueId(): string
    {
        return $this->document->DocumentId;
    }

    /**
     * Execute the job.
     * @throws ErroredException
     */
    public function handle(): void
    {
        (new SignatureService($this->signature))->sign($this->document, $this->actor, $this->Pages, false);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $message = $exception?->getMessage() ?? 'Unknown error occurred';
        Log::error('Document signing failed: ' . $message, [
            'e' => $exception,
        ]);
        (new UserService($this->actor))->sendEmail(
            subject: 'Document Signing Failed',
            body: '<div><p>Dear ' . $this->actor->Name . ',</p>
                <p>The document signing process for <strong>' . $this->document->Name . '</strong> has failed.</p>
                <p>The signature attempt has been canceled.</p>
                <p>Failure reason: ' . $message . '</p>
                <p>Please try signing the document again or contact system support if the issue persists.</p>
                <p>Document Details:</p>
                <ul>
                    <li>Document: ' . $this->document->Name . '</li>
                    <li>Signature: ' . $this->signature->Name . '</li>
                    <li>Attempted on: ' . now()->format('Y-m-d H:i:s') . '</li>
                </ul></div>',
        )?->send(true);
    }
}
