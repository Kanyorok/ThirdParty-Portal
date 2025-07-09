<?php

namespace App\Models\Core;

use App\Enums\LocalityTypeEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Locality extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_Localities';
    protected $primaryKey = 'ID';

    public static function getPrimaryKey(): string
    {
        return 'LocalitiesId';
    }

    protected $fillable = [
                           'Name',
                           'LocationType',
                           'LocalityID',
                           'IsActive',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = [
                        'LocationType' => LocalityTypeEnum::class,
                       ];

    public function in(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'LocalityID', 'ID');
    }
}
