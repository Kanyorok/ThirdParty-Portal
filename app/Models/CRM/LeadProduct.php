<?php

namespace App\Models\CRM;

use App\Models\BR\Product;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadProduct extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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

    public static function getPrimaryKey(): string
    {
        return "LeadProductID";
    }
}
