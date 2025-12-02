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
    use UserActorTrait, SoftDeletes;

    protected $table = 't_ThirdPartyTypes';
    protected $primaryKey = 'TypeId';
    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';


    protected $fillable = [
        'FinanceRole', 'Code', 'Description',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'TypeId' => 'integer',
        'Type' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'Code';
    }

    public function parties(): HasManyThrough
    {
        return $this->hasManyThrough(ThirdParties::class, ThirdPartyTypeTypes::class, 'TypeId', 'Id', 'TypeId', 'ThirdPartyId');
    }


    /* public function thirdParties(): BelongsToMany
     {
         return $this->belongsToMany(ThirdParties::class, 't_ThirdPartyType_ThirdParties', $this->primaryKey, 'Id')
           ->using(ThirdPartyTypeTypes::class);
             ->withTimestamps()
             ->withPivot('Id', 'PartyType', 'PartyID', 'CreatedBy', 'ModifiedBy', 'DeletedBy', 'CreatedOn', 'ModifiedOn', 'DeletedOn');
     }*/

    public function role(): BelongsTo
    {
        return $this->belongsTo(FinanceRole::class, 'FinanceRole', 'FinanceRoleID');
    }

    public static function getPrimaryKey(): string
    {
        return 'ThirdPartyTypeID';
    }
}
