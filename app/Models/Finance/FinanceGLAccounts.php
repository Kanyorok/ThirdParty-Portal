<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceGLAccounts extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_FinanceGLAccounts';
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
        'Description',
        'IsActive',

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
