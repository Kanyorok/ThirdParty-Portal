<?php

namespace App\Models\DMS;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\VisibilityEnum;
use App\Exceptions\ErroredException;
use App\Interfaces\SpecialPermissionContract;
use App\Models\Core\CategoryMaster;
use App\Traits\Model\SpecialPermissionTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Document extends Model implements SpecialPermissionContract
{
    use SoftDeletes;
    use UserActorTrait;
    use SpecialPermissionTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_Documents';
    protected $primaryKey = 'Id';

    protected $fillable = [
        "DocumentId", "Name", "MimeType", "CategoryId", "RepositoryId", "Visibility",
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'RepositoryId' => 'integer',
        'Visibility' => VisibilityEnum::class,
    ];

    public function ext(): ?ExtensionsEnum
    {
        try {
            return ExtensionsEnum::fromMimeType($this->MimeType);
        } catch (ErroredException) {
            return null;
        }
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryMaster::class, 'CategoryId', 'Id')->withAttributes(['Type' => self::getPrimaryKey()]);
    }

    public static function getPrimaryKey(): string
    {
        return 'DocumentId';
    }

    public function getRouteKeyName(): string
    {
        return self::getPrimaryKey();
    }

    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class, 'RepositoryId', 'Id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(DMSTags::class, 't_DocumentTags', 'DocId', 'TagId', $this->primaryKey, 'Id')
            ->withPivot(['CreatedBy', 'ModifiedBy', 'DeletedBy'])->withTimestamps()->whereNull('t_DocumentTags.DeletedOn')
            ->using(DocumentTags::class);
    }

    public function holds(): BelongsToMany
    {
        return $this->belongsToMany(LegalHold::class, 't_DocumentLegalHolds', 'DocId', 'LegalHoldId', $this->primaryKey, 'Id')
            ->withPivot(['CreatedBy', 'ModifiedBy', 'DeletedBy'])->withTimestamps()->whereNull('t_DocumentLegalHolds.DeletedOn')
            ->using(DocumentLegalHold::class);
    }

    public function relations(): MorphMany
    {
        return $this->morphMany(DocumentRelation::class, 'related', "Related", "RelatedID", 'Id');
    }

    public function current(): HasOne
    {
        return $this->hasOne(DocumentVersion::class, 'DocumentId', 'Id')->latest('t_DocumentVersions.Id');
    }

    public function checkouts(): HasMany
    {
        return $this->hasMany(DocumentCheckOut::class, 'DocumentId', 'Id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class, 'DocumentId', 'Id');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(DocumentAttribute::class, 'DocumentId', 'Id');
    }

    public function getShareEmailSubject(): string
    {
        return 'Notification: #permission permission to ' . $this->Name;
    }

    public function getFileIcon(): string
    {
        $extension = $this->ext();

        if (! $extension) {
            return 'alt';
        }

        return match ($extension->value) {
            'pdf' => 'pdf',
            'doc', 'docx' => 'word',
            'xls', 'xlsx' => 'excel',
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg' => 'image',
            'zip', 'rar', '7z', 'tar', 'gz' => 'archive',
            'mp4', 'avi', 'mov', 'wmv' => 'video',
            'mp3', 'wav', 'ogg' => 'audio',
            'txt' => 'alt',
            'html', 'htm' => 'code',
            'ppt', 'pptx' => 'powerpoint',
            default => 'alt'
        };
    }
    // In App\Models\DMS\Document

    /**
     * Get document view URL
     */
    public function getViewUrl(): string
    {

        return route('file.preview', ['document' => $this->Id]);
    }

    /**
     * Get document download URL
     */
    public function getDownloadUrl(): string
    {
        return route('file-download.store', ['document' => $this->Id]);
    }

    /**
     * Check if user can view this document
     */
    public function canView(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // Admin can view all
        if ($user->hasRole('Super Admin') || $user->hasRole('Administrator')) {
            return true;
        }

        // Public documents
        if ($this->Visibility === VisibilityEnum::Public) {
            return true;
        }

        // Check if user created the related entity (tender)
        $documentRelation = $this->relations()->first();
        if ($documentRelation && $documentRelation->Related === 'tender') {
            $tender = \App\Models\Procurement\Tender::find($documentRelation->RelatedID);
            if ($tender && $tender->CreatedBy === $user->Id) {
                return true;
            }
        }

        // Check permissions
        try {
            return $user->can('view', $this);
        } catch (\Exception $e) {
            return true; // Fallback to allow viewing
        }
    }

    /**
     * Get file size formatted for humans
     */
    // In App\Models\DMS\Document model

    /**
     * Get the original filename from current version
     */
    public function getFileNameAttribute(): ?string
    {
        return $this->current?->FileName ?? $this->Name ?? 'Document';
    }

    /**
     * Get file extension from current version
     */
    public function getFileExtensionAttribute(): ?string
    {
        $currentVersion = $this->current;
        if (! $currentVersion) {
            return null;
        }

        // Try to get from FileName first
        if ($currentVersion->FileName) {
            return strtolower(pathinfo($currentVersion->FileName, PATHINFO_EXTENSION));
        }

        // Fallback to MimeType
        try {
            $ext = ExtensionsEnum::fromMimeType($this->MimeType);

            return $ext ? $ext->value : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get file size formatted for humans
     */
    public function getFormattedSize(): string
    {
        $currentVersion = $this->current;
        $bytes = $currentVersion->FileSize ?? 0;

        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $exp = floor(log($bytes) / log(1024));
        $exp = min($exp, count($units) - 1);

        return round($bytes / pow(1024, $exp), 2) . ' ' . $units[$exp];
    }
}
