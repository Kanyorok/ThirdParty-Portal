<?php

namespace App\Models\Insurance;

use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancassurancePolicy;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassuranceClaim extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_BancassuranceClaims';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PolicyId','ClaimType','ClaimReason','ClaimAmount','ClaimDate','Status',
        'CreatedBy','ModifiedBy','DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'bancassuranceclaimId';
    }

    // Relationships
    public function policy()
    {
        return $this->belongsTo(BancassurancePolicy::class, 'PolicyId', 'Id');
    }

    public function claimtype()
    {
        return $this->belongsTo(CodeDetail::class, 'ClaimType', 'ID');
    }
    
    public function status()
    {
        return $this->belongsTo(CodeDetail::class, 'Status', 'ID');
    }

}
