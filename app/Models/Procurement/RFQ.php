<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Procurement\Tender;
use App\Models\Procurement\ItemCategory;
use App\Models\Procurement\Supplier;

class RFQ extends Model
{
    use HasFactory;

    protected $table = 't_RFQ';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';

    protected $fillable = [
        'RFQNumber', 'ItemCategoryId', 'Comments', 'RequisitionItems', 'Status', 'SubmissionDeadline',  'CreatedBy', 'ModifiedBy', 'Suppliers'
    ];

    protected $casts = [
        'RequisitionItems' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'ItemCategoryId');
    }

    public function rfqResponses()
    {
        return $this->hasMany(RFQResponse::class, 'RFQId', 'Id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 't_RFQ_Supplier', 'RFQId', 'SupplierId')
                    ->withPivot('Status')
                    ->withTimestamps();
    }
}
