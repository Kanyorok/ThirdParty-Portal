<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InterBranchRequisitionItem extends Model
{
    protected $table = 't_InterBranchRequisitionItems';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'RequisitionId', 'Category', 'Subcategory', 'Item', 'UOM', 'RequestedQty', 'Remarks'
    ];

    public function requisition()
    {
        return $this->belongsTo(InterBranchRequisition::class, 'RequisitionId', 'Id');
    }

    public function uom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UOM', 'Id');
    }
   

public function item()
{
    return $this->belongsTo(ItemMasterList::class, 'Item', 'Id');
}

}
