<?php

namespace App\Models\Finance;

use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceTransaction extends Model
{
    use UserActorTrait,SoftDeletes;
    protected $table = 't_FinancialTransactions';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'FinanceTransactionId';
    }
    protected $fillable = [
        'TransactionDate',
        'ThirdPartyID',
        'PostingDate',
        'ReferenceNumber',
        'TransactionType',
        'ModuleID',
        'SourceTable',
        'IdempotencyKey',
        'TransactionTypeID',
        'Status',

        'GLAccountID',
        'BranchID',
        'DepartmentID',

        'DRCR',
        'Amount',
        'CurrencyID',
        'CurrencyCode',
        'ExchangeRate',

        'Narration',
        'BatchNumber',

        'IsTaxable',

        'IsReversal',
        'ReversedTransactionID',

        'IsPosted',
        'Status',

        'SystemDescription',

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

    public function glAccounts()
    {
        return $this->belongsTo(FinanceGLAccounts::class, 'GLAccountID', 'Id');
    }

    public function branches()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'Id');
    }

    public function departments()
    {
        return $this->belongsTo(Department::class, 'DepartmentID', 'Id');
    }

}
