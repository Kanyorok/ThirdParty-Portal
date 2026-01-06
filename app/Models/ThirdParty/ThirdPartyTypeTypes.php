<?php

namespace App\Models\ThirdParty;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThirdPartyTypeTypes extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_ThirdPartyType_ThirdParties';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'TypeId',
        'ThirdPartyId',
        'PartyType',
        'PartyID',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'ThirdPartyTypeTypes';
    }

    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'PartyType', 'PartyID');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ThirdPartyType::class, 'TypeId', 'TypeId');
    }

    public function thirdparty(): BelongsTo
    {
        return $this->belongsTo(ThirdParties::class, 'ThirdPartyId', 'Id');
    }
}
