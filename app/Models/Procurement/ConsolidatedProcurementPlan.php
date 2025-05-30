<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use App\Models\Auth\User;
use App\Enums\Core\PostingEnum;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsolidatedProcurementPlan extends Model
{
    use SoftDeletes, UserActorTrait;
 
    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_ConsolidatedProcurementPlan';
    protected $primaryKey = 'PlanID'; 
    protected $fillable = [
        'Title',
        'ReferenceNumber',
        'FiscalYear',
        'Status',
        'CreatedBy',
        'CreatedDate',
        'SubmittedBy',
        'SubmittedDate',
        'CurrentApprLevel',
        'Remarks',
        'ModifiedBy',
        'DeletedBy'
    ];

    protected $dates = [
        'CreatedDate',
        'SubmittedDate'
    ];

    // Relationship with User for CreatedBy
    protected $casts = [
    'Status' => PostingEnum::class,
];
   public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function submittedBy()
{
    return $this->belongsTo(User::class, 'SubmittedBy', 'Id'); 
}
    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }


    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }
      public function item()
    {
        return $this->belongsTo(Item::class, 'ItemID', 'Id');
    }
    public function lineItems()
{
    return $this->hasMany(PlanLineItems::class, 'PlanID');
}

}
