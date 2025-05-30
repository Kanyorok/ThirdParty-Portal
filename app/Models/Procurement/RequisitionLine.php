<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemMasterList;

class RequisitionLine extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_RequisitionLines';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'RequisitionID', 'Module', 'Type', 'Item', 'Description', 'UOM', 'Quantity', 'ExpectedPrice',
        'Urgency','NeededBy', 'CreatedBy', 'ModifiedBy', 'DeletedBy', 'CategoryId'
    ];

    public static function getPrimaryKey(): string
    {
        return 'RequisitionLineID';
    }

    protected $casts = [
        // 'Status' => CampaignStatusEnum::class,
        // 'Type' => CampaignTypeEnum::class,
                        'CreatedBy'  => 'integer',
                        'ModifiedBy' => 'integer',//,
        // 'Processing' => 'boolean'
                       ];


    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'Item', 'Id');
    }


}
