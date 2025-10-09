<?php

namespace App\Models\Finance;

use App\Models\Core\Currency;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cheque extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_Cheques';
    protected $primaryKey = 'ChequeID';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'Direction','BankAccountID','ChequeBookID','ChequeNumber',
        'ChequeDate','DueDate','IsPostDated','CurrencyID','Amount',
        'PartyType','PartyID','PartyName',
        'Status','ReceivedDate','DepositDate','ClearDate','BounceDate',
        'CashbookID_Deposit','CashbookID_Clear',
        'Reference','Narration','IsActive',
        'CreatedBy','ModifiedBy','DeletedBy'
    ];

    protected $casts = [
        'IsPostDated' => 'boolean',
        'Amount' => 'decimal:2',
        'ChequeDate' => 'date',
        'DueDate' => 'date',
        'ReceivedDate' => 'date',
        'DepositDate' => 'date',
        'ClearDate' => 'date',
        'BounceDate' => 'date',
        'IsActive' => 'boolean',
    ];

    public static function getPrimaryKey(): string { return 'ChequeID'; }

    public function bankAccount() { return $this->belongsTo(BankAccount::class, 'BankAccountID', 'AccountID'); }
    public function chequeBook()  { return $this->belongsTo(ChequeBook::class, 'ChequeBookID', 'ChequeBookID'); }
    public function currency()    { return $this->belongsTo(Currency::class, 'CurrencyID', 'Id'); }
}
