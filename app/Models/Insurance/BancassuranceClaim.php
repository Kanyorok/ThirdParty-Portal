<?php

namespace App\Models\Insurance;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Currency;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassuranceClaim extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use DocumentsTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $table = 't_BancassuranceClaims';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PolicyId', 'ClaimType', 'ClaimReason', 'ClaimAmount', 'CurrencyId', 'ClaimDate', 'Status',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
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

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyId', 'Id');
    }
}
