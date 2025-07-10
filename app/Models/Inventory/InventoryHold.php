<?php

namespace App\Models\Inventory;

use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryHold extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_InventoryHold';
    protected $primaryKey = 'Id';
    protected $connection = 'sqlsrv';


    protected $fillable = [
        'InventoryHoldID', 'ItemID', 'BranchID', 'Store', 'Quantity', 'Reason', 'Source', 'SourceID',
        'Status', 'Remarks', 'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'DeletedBy'
    ];

    public function defect()
    {
        return $this->belongsTo(CodeDetail::class, 'Reason', 'ID')
            ->where('CodeID', 'Adjustment Reason');
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemID');
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

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'Store');
    }

    public function sourceDetail()
    {
        return $this->belongsTo(CodeDetail::class, 'Source');
    }

    public static function getPrimaryKey(): string
    {
        return 'Id';
    }

    protected static function booted()
    {
        static::created(function ($hold) {
            if (!$hold->InventoryHoldID) {
                $year = now()->format('Y');
                $hold->newQueryWithoutScopes()
                    ->where('Id', $hold->Id)
                    ->update([
                        'InventoryHoldID' => 'HLD-' . $year . '-' . str_pad($hold->Id, 4, '0', STR_PAD_LEFT)
                    ]);
                $hold->InventoryHoldID = 'HLD-' . $year . '-' . str_pad($hold->Id, 4, '0', STR_PAD_LEFT);
            }
        });
    }


}
