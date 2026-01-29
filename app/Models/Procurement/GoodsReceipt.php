<?php

namespace App\Models\Procurement;

use App\Enums\Core\PostingEnum;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\StockGRNLedger;
use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceipt extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
                           'UnitPrice',
                          ];

    public static function getPrimaryKey(): string
    {
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

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemNo', 'Id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'TransferTo', 'Id');
    }

    public function transfer()
    {
        return $this->hasOne(\App\Models\Inventory\TransactionTransfer::class, 'RequisitionId', 'id')
            ->where('RequisitionType', 'procurement');
    }

    public function stockLedger()
    {
        return $this->hasOne(StockGRNLedger::class, 'GoodsReceiptId');
    }
}
