<?php
namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Core\Branch;
use App\Models\Inventory\Store;
use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
use App\Models\Inventory\ItemMasterList;
use App\Models\Auth\User;
use App\Models\Inventory\UnitOfMeasure;



class InventoryHoldReview extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_Defects';
    protected $primaryKey = 'Id';
    protected $connection = 'sqlsrv';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'InventoryHoldID',
        'ItemID',
        'FromBranch',
        'Store',
        'Quantity',
        'Defect',
        'Condition',
        'Status',
        'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

  public static function getPrimaryKey(): string
    {
        return 'InventoryHoldReviewID';
    }
        public function defectDetail()
        {
            return $this->belongsTo(\App\Models\Core\CodeDetail::class, 'Defect', 'ID');
        }

        public function conditionDetail()
        {
            return $this->belongsTo(\App\Models\Core\CodeDetail::class, 'Condition', 'ID');
        }
        public function inventoryHold()
{
    return $this->belongsTo(InventoryHold::class, 'InventoryHoldID', 'Id')->withTrashed();
}


    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemID');
    }


    public function fromBranch()
{
    return $this->belongsTo(\App\Models\Core\Branch::class, 'FromBranch');
}


    public function store()
    {
        return $this->belongsTo(Store::class, 'Store');
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

}
