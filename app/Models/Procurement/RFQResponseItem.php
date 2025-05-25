<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class RFQResponseItem extends Model
{
    protected $table = 't_ResponseItems';
    protected $primaryKey = 'Id';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'RfqResponseId', 'ItemName', 'UOM', 'Quantity', 'QuotedPrice', 'TotalPayable', 'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    public function rfqResponse()
    {
        return $this->belongsTo(RFQResponse::class);
    }
}
