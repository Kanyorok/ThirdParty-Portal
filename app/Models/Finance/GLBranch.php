<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GLBranch extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
