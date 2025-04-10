<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductDevelopmentFeature extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_ProductDevelopmentFeatures';
    protected $primaryKey = 'Id';


    protected $fillable = [
        'ProductDevelopmentId', 'Feature', 'Description', 'Notes',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductDevelopment::class, 'ProductDevelopmentId', 'Id');
    }
}
