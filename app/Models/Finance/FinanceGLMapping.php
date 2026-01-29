<?php

namespace App\Models\Finance;

use App\Models\Core\Module;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceGLMapping extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    protected $table = 't_FinanceGlTransactionsMapping';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'ModuleID',
        'TransactionTypeID',
        'DebitGLAccountID',
        'CreditGLAccountID',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'FinanceGlMappingId';
    }

    public function transactions()
    {
        return $this->belongsTo(FinanceTransactionTypes::class, 'TransactionTypeID', 'Id');
    }

    public function modules()
    {
        return $this->belongsTo(Module::class, 'ModuleID', 'ModuleID');
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
