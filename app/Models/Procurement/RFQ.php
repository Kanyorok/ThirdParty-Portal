<?php

namespace App\Models\Procurement;

<<<<<<< HEAD
=======
use App\Traits\Model\UserActorTrait;
>>>>>>> dev
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Procurement\Tender;
use App\Models\Procurement\ItemCategory;
use App\Models\Procurement\Supplier;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQ extends Model
{
<<<<<<< HEAD
    use HasFactory;
=======
    use UserActorTrait, SoftDeletes;
>>>>>>> dev

    protected $table = 't_RFQ';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'RFQNumber', 'ItemCategoryId', 'Comments', 'RequisitionItems', 'Status', 'SubmissionDeadline',  'CreatedBy', 'ModifiedBy', 'Suppliers'
    ];

    protected $casts = [
        'RequisitionItems' => 'array',
    ];

    public static function getPrimaryKey(): string
    {
        return 'RFQId';
    }

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'ItemCategoryId');
    }

    public function rfqResponses()
    {
        return $this->hasMany(RFQResponse::class, 'RFQId', 'Id');
    }


    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 't_RFQ_Supplier', 'RFQId', 'SupplierId')
                    ->withPivot('Status')
                    ->withTimestamps();
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 't_RFQ_Supplier', 'RFQId', 'SupplierId')
                    ->withPivot('Status')
                    ->withTimestamps();
    }
}
