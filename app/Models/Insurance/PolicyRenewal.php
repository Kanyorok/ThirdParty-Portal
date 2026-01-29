<?php

namespace App\Models\Insurance;

use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PolicyRenewal extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    protected $table = 't_BancassurancePolicyRenewals';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'PolicyID',
        'RenewalDate',
        'NewStartDate',
        'NewEndDate',
        'Status',
        'Notes',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'PolicyRenewalId';
    }

    public function policy()
    {
        return $this->belongsTo(BancassurancePolicy::class, 'PolicyID', 'Id');
    }

    public function status()
    {
        return $this->belongsTo(CodeDetail::class, 'Status', 'ID');
    }
}
