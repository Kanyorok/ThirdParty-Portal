<?php

namespace App\Models\Core;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Locality extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_Localities';
    protected $primaryKey = 'ID';
    protected $fillable = [
        'Name', 'LocationType', 'LocalityID', 'IsActive', "CountryId",
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    /*protected $casts = [
        'LocationType' => LocalityTypeEnum::class,
    ];*/

    public static function getPrimaryKey(): string
    {
        return 'LocalitiesId';
    }

    public function in(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'LocalityID', 'ID');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'CountryId', 'Id');
    }
}
