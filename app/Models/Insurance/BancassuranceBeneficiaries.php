<?php

namespace App\Models\Insurance;

use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassuranceBeneficiaries extends Model
{
    use SoftDeletes;
    use UserActorTrait;


    protected $table = 't_BancassuranceBeneficiaries';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'CustomerID',
        'PolicyID',
        'FullName',
        'Relationship',
        'IDNumber',
        'Phone',
        'Email',
        'PercentageShare',
        'IsPrimary',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'BancassuranceBeneficiariesId';
    }

    public function relationships()
    {
        return $this->belongsTo(CodeDetail::class, 'Relationship', 'ID');
    }

    public function policies()
    {
        return $this->belongsTo(BancassurancePolicy::class, 'PolicyID', 'ID');
    }
}
