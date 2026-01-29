<?php

namespace App\Models\Insurance;

use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassuranceCommissionEarned extends Model
{
    use SoftDeletes;
    use UserActorTrait;


    protected $table = 't_BancassuranceCommissionRules';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
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
        'DeletedBy',
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
