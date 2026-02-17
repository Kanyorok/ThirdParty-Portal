<?php

namespace App\Models\Inventory;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Branch;
use App\Models\HR\Employee;
use App\Models\HRM\Department;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockConsumption extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_StockConsumptions';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ConsumptionNo',
        'ItemID',
        'UOM',
        'Quantity',
        'StoreID',
        'BranchID',
        'IssuedToType',
        'IssuedToID',
        'IssuedBy',
        'IssuedOn',
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public function getItemNameAttribute()
    {
        return $this->item?->ItemName ?? 'N/A';
    }

    public static function getPrimaryKey(): string
    {
        return 'ConsId';
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

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'IssuedBy', 'Id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'Id');
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemID', 'Id');
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class, 'StockItemID', 'Id');
    }

    public function masterItem()
    {
        return $this->hasOneThrough(
            ItemMasterList::class,
            StockItem::class,
            'Id',
            'Id',
            'ItemID',
            'ItemID'
        );
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'StoreID', 'Id');
    }

    public function uom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UOM', 'Id');
    }

    public function getIssuedToNameAttribute()
    {
        $type = CodeDetail::find($this->IssuedToType)?->Description;

        switch (strtoupper($type)) {
            case 'EMPLOYEE':
                return Employee::find($this->IssuedToID)?->FirstName ?? 'N/A';
            case 'DEPARTMENT':
                return Department::find($this->IssuedToID)?->Name ?? 'N/A';
            default:
                return 'N/A';
        }
    }

    public function issuedToType()
    {
        return $this->belongsTo(CodeDetail::class, 'IssuedToType', 'CodeValue');
    }
}
