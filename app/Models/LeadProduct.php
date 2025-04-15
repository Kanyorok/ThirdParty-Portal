<?php

namespace App\Models;

use App\Models\BR\Product;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadProduct extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_LeadProducts';
    protected $primaryKey = 'Id';

    protected $fillable = [
                           'LeadId',
                           'ProductID',
                           'ProductName',
                           'Notes',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];


    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'LeadId', 'LeadID');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }
}
