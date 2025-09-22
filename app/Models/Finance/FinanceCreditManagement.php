<?php

namespace App\Models\Finance;

use App\Models\PropertyManagement\PropertyNewTenant;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceCreditManagement extends Model
{
    use SoftDeletes, UserActorTrait;


    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_FinanceCreditManagement';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'CustomerID',
        'CreditLimit',
        'PaymentTerms',
        'EffectiveFrom',
        'ExpiryDate',
        'Colleteral',
        'Remarks',
        'Status',
        'ApprovalStatus',
        'ApprovalReason',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'FinanceCreditManagementId';
    }

    public function customer()
    {
        return $this->belongsTo(PropertyNewTenant::class, 'CustomerID', 'Id');
    }

}
