<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceGLAccounts extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_FinanceGLAccounts';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'GLCode',
        'MappedGLCode',
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
        'IsInterbranchGL',
        'InterbranchRole',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $cast = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public static function getPrimaryKey(): string
    {
        return 'FinanceGLAccountsId';
    }

    public function parent()
    {
        return $this->belongsTo(FinanceGLAccounts::class, 'ParentGLID');
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
