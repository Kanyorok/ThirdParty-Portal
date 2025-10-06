<?php

namespace App\Models\Finance;

use App\Models\Core\Currency;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCashVoucher extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_PettyCashVouchers';
    protected $primaryKey = 'VoucherID';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
    'FloatID','VoucherType','DocNo','DocDate',
    'CurrencyID','ExchangeRate','Amount','Status',
    'Reference','Narration','BankAccountID','CashbookID',
    'ApprovalStatus','SubmittedOn','SubmittedBy','ApprovedOn','ApprovedBy',  // <— add
    'ReplenishmentBatchID',                                                   // <— add
    'PostedOn','PostedBy','VoidedOn','VoidedBy',
    'CreatedBy','ModifiedBy','DeletedBy'
    ];

    protected $casts = [
        'ExchangeRate' => 'decimal:6',
        'Amount' => 'decimal:2',
    ];

    public function float()     { return $this->belongsTo(PettyCashFloat::class, 'FloatID','FloatID'); }
    public function currency()  { return $this->belongsTo(Currency::class, 'CurrencyID','Id'); }
    public function lines()     { return $this->hasMany(PettyCashLine::class, 'VoucherID','VoucherID'); }
    public function batch()     { return $this->belongsTo(PettyCashReplenishmentBatch::class, 'ReplenishmentBatchID','BatchID');  }

    public static function getPrimaryKey(): string { return 'VoucherID'; }
}
