<?php

namespace App\Models\Finance;

use App\Models\Core\Module;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceModuleTransactions extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_FinanceModuleTransactions';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'ModuleID',
        'TransactionTypeID',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
         return 'ModuleTransactionsId';
    }

    // Relationships (example stubs - update as needed)
    public function module()
    {
        return $this->belongsTo(Module::class, 'ModuleID', 'ModuleID');
    }
    public function transactions()
    {
        return $this->belongsTo(FinanceTransactionTypes::class, 'TransactionTypeID', 'Id');
    }

    public function debitAccount()
    {
        return $this->belongsTo(FinanceGLAccounts::class, 'DebitGLAccountID', 'Id');
    }

    public function creditAccount()
    {
        return $this->belongsTo(FinanceGLAccounts::class, 'CreditGLAccountID', 'Id');
    }
}
