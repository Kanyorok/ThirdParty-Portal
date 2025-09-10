<?php

namespace App\Models\DMS;

use App\Enums\DMS\DocumentValidationTypeEnum;
use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentValidation extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_DocumentValidation';
    protected $primaryKey = 'Id';

    protected $fillable = [
        "Name", "ValidationId", "Type", "DocumentId", "ApprovedBy", "ApprovedOn",
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'Type' => DocumentValidationTypeEnum::class,
        'ApprovedOn' => 'datetime',
    ];

    public static function getPrimaryKey(): string
    {
        return 'DocumentValidationId';
    }

    public function getRouteKeyName(): string
    {
        return 'ValidationId';
    }


    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'DocumentId');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(DocumentValidationAttributes::class, 'DocumentValidationId');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ApprovedBy', 'Id')->withTrashed();
    }
}
