<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GLBranch extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = "t_FinanceGLBranch";
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'GLBranchId';
    }

    protected $fillable = [
        'LastTransactionId',
        'GLAccountID',
        'BranchID',
        'GLCode',
        'Balance',
        'LocalBalance',
        'ForeignBalance',
        'GLAccountType',
        'IsActive',
        'BankID',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];
}
