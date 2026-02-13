<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceSyncGLAccount extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_FinanceSyncGLAccounts';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'GLCode',
        'GLName',
        'GLAccountTypeID',
        'GLTypeGroupID',
        'GLSubAccountTypeID',
        'ParentGLID',
        'NormalBalance',
        'IsControlAccount',
        'IsPostingAccount',
        'CBSAccountCode',
        'BranchID',
        'GLAccountTypeValue',
        'GLTypeGroupIDValue',
        'GLTypeGroupValue',
        'GLSubAccountTypeIDValue',
        'GLDigits',
        'Description',
        'IsActive',
        'CurrencyID',
        'IsSynced',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public static function getPrimaryKey(): string
    {
        return 'FinanceSyncGLAccountsId';
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'ParentGLID');
    }

    public function subAccount()
    {
        return $this->belongsTo(FinanceGLSubAccountTypes::class, 'GLSubAccountTypeID');
    }

    public function typeGroup()
    {
        return $this->belongsTo(FinanceGLTypeGroup::class, 'GLTypeGroupID');
    }
}
