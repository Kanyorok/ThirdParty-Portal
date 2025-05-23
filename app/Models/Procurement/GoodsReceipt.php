<?php

namespace App\Models\Procurement;

use App\Enums\Core\PostingEnum;
use App\Models\Auth\User;
use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceipt extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    
    protected $table = 't_GoodsReceipts';
    protected $primaryKey = 'id';
    protected $fillable = [
                           'GRNID',
                           'POID',
                           'ReceivedDate',
                           'SupplierId',
                           'StoreID',
                           'ReceivedBy',
                           'InspectionStatus',
                           'TransferStatus',
                           'ItemNo',
                           'POQTY',
                           'ReceivedQTY',
                           'TransferTo',
                           'TagRequired',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                           'DeletedOn',
                          ];
    public static function getPrimaryKey(): string{
        return 'id';
    }
    public function receiver()
    {
        return $this->belongsTo(User::class, 'ReceivedBy', 'Id');
    }
    protected $casts = [

    'InspectionStatus' => PostingEnum::class,

    ];
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierId');
    }

}
