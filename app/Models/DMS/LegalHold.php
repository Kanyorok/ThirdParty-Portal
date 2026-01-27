<?php

namespace App\Models\DMS;

use App\Enums\DMS\LegalHoldStatusEnum;
use App\Models\Auth\User;
use App\Models\CRM\Notes;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalHold extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const string CREATED_AT = 'CreatedOn';
    public const string UPDATED_AT = 'ModifiedOn';
    public const string DELETED_AT = 'DeletedOn';

    protected $table = 't_DMSLegalHolds';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Name', 'Ref', 'Description', 'Status', 'ReleasedBy', 'ReleasedOn',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'ReleasedOn' => 'datetime',
        'ReleasedBy' => 'integer',
        'Status' => LegalHoldStatusEnum::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'DocumentLegalHoldId';
    }

    public function releasor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ReleasedBy', 'Id')->withTrashed();
    }

    public function getRouteKeyName(): string
    {
        return 'Ref';
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 't_DocumentLegalHolds', 'LegalHoldId', 'DocId', 'Id', 'Id')
            ->withPivot(['CreatedBy', 'ModifiedBy', 'DeletedBy'])->withTimestamps();
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Notes::class, 'party', "Party", "PartyID", 'ClientID');
    }
}
