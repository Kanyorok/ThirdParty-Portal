<?php

namespace App\Models\CRM;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductDevelopmentFeature extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_ProductDevelopmentFeatures';

    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'ProductDevelopmentFeaturesId';
    }

    protected $fillable = [
                           'ProductDevelopmentId',
                           'Feature',
                           'Description',
                           'Notes',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductDevelopment::class, 'ProductDevelopmentId', 'Id');
    }
}
