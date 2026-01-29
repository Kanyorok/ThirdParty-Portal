<?php

namespace App\Models\Inventory;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionTransferItem extends Model
{
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_TransferItems';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';


    protected $fillable = [
        'TransferId',
        'Item',
        'ApprovedQty',
        'DispatchedQty',
        'UnitCost',
        'UOM',
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',

    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }

    public function requisition()
    {
        return $this->belongsTo(InterBranchRequisition::class, 'RequisitionId', 'Id');
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'Item', 'Id');
    }

    public function uom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UOM', 'Id');
    }

    public function transfer()
    {
        return $this->belongsTo(TransactionTransfer::class, 'TransferId', 'Id');
    }
}
