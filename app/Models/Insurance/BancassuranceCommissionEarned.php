<?php

namespace App\Models\Insurance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;

class BancassuranceCommissionEarned extends Model
{
    use SoftDeletes, UserActorTrait;
    //
    protected $table = 't_BancassuranceCommissionRules';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PolicyId',
        'ReferralId',
        'CommissionRuleId',    
        'EarnedByType',
        'EarnedById',
        'EarnedAmount',
        'Status',
        'EarnedDate',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];
    
    public static function getPrimaryKey(): string
    {
        return 'BancassuranceCommissionEarnedId';
    }

    public function policytypes()
    {
        return $this->belongsTo(CodeDetail::class, 'EarnedByType', 'ID');
    }
}
