<?php

namespace App\Models\BR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    protected $primaryKey = 'AccountID';
    protected $table = 'syn_t_AccountCustomer';
    protected $connection = 'sqlsrv';
    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
                'ClearBalance' => 'decimal:4',
                'LastCreditTrxDate' => 'datetime',
                'LastDebitTrxDate' => 'datetime',
               ];
    }

    public static function getPrimaryKey(): string
    {
        return (new self())->primaryKey;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'OurBranchID', 'OurBranchID');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(SystemCodeDetail::class, 'AccountStatusID', 'SubCodeID')->where('ID', 'AccountStatusID');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'ClientID', 'ClientID');
    }

    public function transactions(): BelongsTo
    {
        return $this->belongsTo(AccountTrx::class, 'AccountID', 'AccountID');
    }
}
