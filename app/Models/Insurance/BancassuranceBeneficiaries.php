<?php

namespace App\Models\Insurance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancassurancePolicies;

class BancassuranceBeneficiaries extends Model
{
    use SoftDeletes, UserActorTrait;
    //
    protected $table = 't_BancassuranceBeneficiaries';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
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
        'DeletedBy'
    ];
        public static function getPrimaryKey(): string
    {
        return 'BancassuranceBeneficiariesId';
    }

    public function relationships()
    {
        return $this->belongsTo(CodeDetail::class, 'Relationship', 'ID');
    }

    public function policys()
    {
        return $this->belongsTo(BancassurancePolicies::class, 'PolicyID', 'ID');
    }
}


