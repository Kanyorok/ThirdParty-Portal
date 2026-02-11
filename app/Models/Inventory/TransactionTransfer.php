<?php

namespace App\Models\Inventory;

use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Workflow\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionTransfer extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_Transfers';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';


    protected $fillable = [
        'TransferId',
        'TransferID',
        'TransferDate',
        'RequisitionId',
        'RequisitionType',
        'TransferredBy',
        'DispatchedQty',
        'UOM',
        'Status',
        'FromBranch',
        'ToBranch',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',

    ];

    public function receipt()
    {
        return $this->hasOne(TransactionReceipt::class, 'TransferId', 'Id');
    }

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

    public function transferredBy()
    {
        return $this->belongsTo(User::class, 'TransferredBy', 'Id');
    }

    public function items()
    {
        return $this->hasMany(TransactionTransferItem::class, 'TransferId', 'Id')->whereNull('DeletedOn');
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'FromBranch', 'Id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'ToBranch', 'Id');
    }

    public function transferStatus()
    {
        return $this->belongsTo(CodeDetail::class, 'Status', 'ID')
            ->where('CodeID', 'TransferStatus');
    }

    public static function getPrimaryKey(): string
    {
        return 'TransferId';
    }
}
