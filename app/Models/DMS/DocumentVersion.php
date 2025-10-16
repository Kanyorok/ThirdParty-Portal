<?php

namespace App\Models\DMS;

use App\Enums\DMS\DisksEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentVersion extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_DocumentVersions';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'DocumentId', "Name", "Version", "Path", "Disk", "Checksum", "Size", "Description", "Blob",
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'Size' => 'integer',
        'Disk' => DisksEnum::class,
    ];


    public static function getPrimaryKey(): string
    {
        return 'DocumentVersionId';
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'DocumentId');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(DocumentAttribute::class, 'VersionId');
    }

    /**
     * Get the content of this document version
     * Blob field contains encrypted content that needs to be decrypted
     */
    public function getContent(): string
    {
        // First try to get encrypted content from Blob field (database storage)
        if (!empty($this->Blob)) {
            try {
                // Decrypt the content using EncryptionService
                return (new \App\Services\DMS\EncryptionService())->decrypt($this->Blob);
            } catch (\Exception $e) {
                \Log::error("Failed to decrypt document content from Blob field", [
                    'error' => $e->getMessage(),
                    'version_id' => $this->Id
                ]);
                // Fall through to file system approach
            }
        }

        // Fallback to file system storage using Path (legacy approach)
        if (!empty($this->Path)) {
            try {
                // Try to read encrypted content from storage and decrypt it
                $encryptedContent = \Storage::disk($this->Disk->value)->get($this->Path);
                return (new \App\Services\DMS\EncryptionService())->decrypt($encryptedContent);
            } catch (\Exception $e) {
                \Log::error("Failed to read and decrypt document content from path: {$this->Path}", [
                    'error' => $e->getMessage(),
                    'version_id' => $this->Id
                ]);
            }
        }

        // If both methods fail, return empty string
        return '';
    }

    /**
     * Check if this document version has content available
     */
    public function hasContent(): bool
    {
        // Check if Blob has content
        if (!empty($this->Blob)) {
            return true;
        }

        // Check if file exists at Path
        if (!empty($this->Path)) {
            $fullPath = storage_path('app/' . $this->Path);
            return file_exists($fullPath);
        }

        return false;
    }
}
