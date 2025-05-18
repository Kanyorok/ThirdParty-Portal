<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;

class RFQLine extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_RFQLine';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'RFQId', 'ItemId', 'Quantity', 'UOM', 'Description', 'CreatedBy', 'ModifiedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'RFQLineId';
    }
}
