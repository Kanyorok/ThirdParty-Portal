<?php

namespace App\Models\ThirdParty;

use App\Models\Finance\FinanceRole;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThirdPartyType extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_ThirdPartyTypes';
    protected $primaryKey = 'TypeId';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'FinanceRole',
        'Code',
        'Description',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'TypeId' => 'integer',
        'FinanceRole' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'Code';
    }

    public function parties(): HasManyThrough
    {
        return $this->hasManyThrough(
            ThirdParties::class,
            ThirdPartyTypeTypes::class,
            'TypeId',
            'Id',
            'TypeId',
            'ThirdPartyId'
        );
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(FinanceRole::class, 'FinanceRole', 'FinanceRoleID');
    }

    public static function getPrimaryKey(): string
    {
        return 'TypeId';
    }
}
