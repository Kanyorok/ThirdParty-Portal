<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class GoodsReceipt extends Model
{
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    protected $table = 't_GoodsReceipts';
    protected $fillable = [
                           'GRNID',
                           'POID',
                           'ReceivedDate',
                           'Supplier',
                           //'StoreID',
                           'ReceivedBy',
                           'InspectionStatus',
                           'TransferStatus',
                           'ItemNo',
                           'POQTY',
                           'ReceivedQTY',
                           'TransferTo',
                           'TagRequest',
                           'CreatedBy',
                           'ModifiedBy',
                          ];
    
}
