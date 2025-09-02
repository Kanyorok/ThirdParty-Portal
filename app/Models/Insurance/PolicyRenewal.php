<?php

namespace App\Models\Insurance;

use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PolicyRenewal extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_BancassurancePolicyRenewals';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
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
