<?php

namespace App\Models\Inventory;

use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Core\CodeDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Inventory\StockItem;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\UnitOfMeasure;
use App\Traits\Model\UserActorTrait;
use App\Models\HRM\Department;
use App\Models\HRM\Employee;


class StockConsumption extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
        'DeletedOn'
    ];

    // Add this accessor to get the item name
    public function getItemNameAttribute()
    {
        // First try to get from stockItem (ItemMasterList)
        if ($this->stockItem) {
            return $this->stockItem->ItemName;
        }

        // If not found, try to get through the item (StockItem) -> item (ItemMasterList)
        if ($this->item && $this->item->item) {
            return $this->item->item->ItemName;
        }

        return 'N/A';
    }

    public static function getPrimaryKey(): string
    {
        return 'Id'; // Fixed this - should return 'Id' not 'stockconsumptionId'
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

    // Relationship to StockItem (inventory stock)
    public function item()
    {
        return $this->belongsTo(StockItem::class, 'ItemID', 'Id');
    }

    // Relationship to ItemMasterList (master items)
    public function stockItem()
    {
        // If ItemID directly references ItemMasterList
        return $this->belongsTo(ItemMasterList::class, 'ItemID', 'Id');
    }

    // Alternative: If you need to get ItemMasterList through StockItem
    public function masterItem()
    {
        return $this->hasOneThrough(
            ItemMasterList::class,
            StockItem::class,
            'Id', // Foreign key on StockItem table
            'Id', // Foreign key on ItemMasterList table
            'ItemID', // Local key on StockConsumption table
            'ItemID' // Local key on StockItem table
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
