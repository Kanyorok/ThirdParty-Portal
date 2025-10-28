<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQAward extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_RFQAward';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'RFQId',
        'SupplierId',
        'Comments',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'RFQAwardId';
    }

    public function rfq()
    {
        return $this->belongsTo(RFQ::class, 'RFQId', 'Id');
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Models\ThirdParies\Supplier::class, 'SupplierId', 'Id');
    }
}


