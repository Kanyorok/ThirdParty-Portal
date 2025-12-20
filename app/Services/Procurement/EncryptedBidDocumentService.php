<?php

namespace App\Services\Procurement;

use App\Models\DMS\Document;
use App\Models\DMS\Repository;
use App\Models\Procurement\BidSubmission;
use App\Models\Auth\User;
use App\Services\DMS\DocumentService;
use App\Enums\Core\VisibilityEnum;
use App\Enums\Core\ExtensionsEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class EncryptedBidDocumentService
{
    /**
     * Store encrypted bid documents in DMS with ceremony-based access control
     */
    public static function storeEncryptedBidDocuments(
        BidSubmission $bidSubmission,
        array         $documents,
        User          $actor
    ): array {
        try {
            $storedDocuments = [];
            $bidRepository = self::getBidRepository();

            foreach ($documents as $document) {
                if ($document instanceof UploadedFile) {
                    // Generate unique encryption key for this bid
                    $encryptionKey = self::generateBidEncryptionKey($bidSubmission);

                    // Read and encrypt the document content
                    $originalContent = file_get_contents($document->getRealPath());
                    $encryptedContent = Crypt::encryptString($originalContent);

                    // Get the original file extension
                    $originalExtension = ExtensionsEnum::fromMimeType($document->getMimeType());

                    // Store encrypted content in DMS directly
                    $dmsDocument = DocumentService::createContent(
                        repository: $bidRepository,
                        extension: $originalExtension,
                        fileName: self::generateSecureBidFileName($bidSubmission, $document),
                        content: $encryptedContent,
                        actor: $actor,
                        copyRepoPermissions: true
                    );

                    $storedDocuments[] = [
                        'document_id' => $dmsDocument->document->DocumentId,
                        'original_name' => $document->getClientOriginalName(),
                        'encryption_key' => $encryptionKey,
                        'mime_type' => $document->getMimeType(),
                        'extension' => $originalExtension->value,
                    ];
                }
            }

            return $storedDocuments;
        } catch (\Exception $e) {
            Log::error('Error storing encrypted bid documents: ' . $e->getMessage());
            throw new \Exception('Failed to store encrypted bid documents: ' . $e->getMessage());
        }
    }

    /**
     * Decrypt and retrieve bid documents (only during/after ceremony)
     */
    public static function decryptBidDocuments(BidSubmission $bidSubmission, User $actor): array
    {
        if (!$bidSubmission->canAccessDocuments()) {
            throw new \Exception('Bid documents are sealed until the opening ceremony.');
        }

        try {
            $decryptedDocuments = [];
            $encryptedDocs = json_decode($bidSubmission->EncryptedDocuments, true) ?? [];

            foreach ($encryptedDocs as $docInfo) {
                $dmsDocument = Document::where('DocumentId', $docInfo['document_id'])->first();

                if ($dmsDocument && $dmsDocument->current) {
                    try {
                        // Get encrypted content from DMS
                        $encryptedContent = $dmsDocument->current->getContent();

                        // Decrypt using stored key (we're using Laravel's default encryption)
                        $decryptedContent = Crypt::decryptString($encryptedContent);

                        $decryptedDocuments[] = [
                            'name' => $docInfo['original_name'],
                            'content' => $decryptedContent,
                            'mime_type' => $docInfo['mime_type'],
                            'size' => strlen($decryptedContent),
                            'extension' => $docInfo['extension'] ?? 'bin',
                        ];
                    } catch (\Exception $e) {
                        Log::error("Error decrypting document {$docInfo['document_id']}: " . $e->getMessage());
                        // Continue with other documents
                    }
                }
            }

            // Log access for audit trail


            return $decryptedDocuments;
        } catch (\Exception $e) {
            Log::error('Error decrypting bid documents: ' . $e->getMessage());
            throw new \Exception('Failed to decrypt bid documents: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique encryption key for bid submission
     */
    private static function generateBidEncryptionKey(BidSubmission $bidSubmission): string
    {
        return hash('sha256', $bidSubmission->TenderRef . $bidSubmission->SupplierId . now()->timestamp);
    }

    /**
     * Generate secure filename for bid documents
     */
    private static function generateSecureBidFileName(BidSubmission $bidSubmission, UploadedFile $document): string
    {
        $extension = $document->getClientOriginalExtension();
        return "BID_{$bidSubmission->TenderRef}_{$bidSubmission->SupplierId}_" . Str::uuid() . ".{$extension}";
    }

    /**
     * Get or create the bid documents repository in DMS
     */
    private static function getBidRepository(): Repository
    {
        $repository = Repository::where('Name', 'Encrypted Bid Documents')->first();

        if (!$repository) {
            $repository = Repository::create([
                'Name' => 'Encrypted Bid Documents',
                'Description' => 'Securely encrypted bid documents for tender submissions',
                'RepositoryId' => Uuid::uuid4()->toString(),
                'Visibility' => VisibilityEnum::Private->value,
                'CreatedBy' => 1, // System user
                'ModifiedBy' => 1,
            ]);
        }

        return $repository;
    }

    /**
     * Start bid opening ceremony - makes documents accessible
     */
    public static function startBidOpeningCeremony(string $tenderRef, User $ceremonyOfficer): int
    {
        $submissions = BidSubmission::where('TenderRef', $tenderRef)->get();
        $count = 0;

        foreach ($submissions as $submission) {
            $submission->update([
                'DocumentsAccessible' => true,
                'BidOpeningDate' => now(),
                'ModifiedBy' => $ceremonyOfficer->Id,
            ]);
            $count++;
        }



        return $count;
    }

    /**
     * Check if bid opening ceremony has started for a tender
     */
    public static function isCeremonyStarted(string $tenderRef): bool
    {
        return BidSubmission::where('TenderRef', $tenderRef)
            ->where('DocumentsAccessible', true)
            ->exists();
    }
}
