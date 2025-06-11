<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use App\Models\Auth\User;
use App\Models\Core\Branch;

class TransactionTransferItem extends Model
{
      use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_Transfers';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';
  
  
    protected $fillable = [
           'Item',
           'ApprovedQty',
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

    public function items()
    {
        return $this->hasMany(InterBranchTransferItem::class, 'TransferId', 'Id');
    }

 
}
