<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerWalletTransaction extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_FinanceCustomerWalletTransactions';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'WalletID',
        'CustomerID',
        'TransactionType',
        'Amount',
        'RunningBalance',
        'ReferenceType',
        'ReferenceID',
        'Description',
        'TransactionDate',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn'
    ];

    protected $casts = [
        'Amount' => 'decimal:2',
        'RunningBalance' => 'decimal:2',
        'TransactionDate' => 'date',
    ];

    public static function getPrimaryKey(): string
    {
        return 'CustomerWalletTransactionId';
    }

    // Relationships
    public function wallet()
    {
        return $this->belongsTo(CustomerWallet::class, 'WalletID', 'Id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\ThirdParty\ThirdParties::class, 'CustomerID', 'Id');
    }

    // Scopes
    public function scopeDeposits($query)
    {
        return $query->where('TransactionType', 'deposit');
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('TransactionType', 'withdrawal');
    }

    public function scopeByReferenceType($query, string $type)
    {
        return $query->where('ReferenceType', $type);
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        $prefix = in_array($this->TransactionType, ['deposit', 'refund']) ? '+' : '-';
        return $prefix . 'KSh ' . number_format($this->Amount, 2);
    }

    public function getTransactionTypeColorAttribute()
    {
        return match ($this->TransactionType) {
            'deposit', 'refund' => 'text-success',
            'withdrawal' => 'text-danger',
            'adjustment' => 'text-warning',
            default => 'text-muted'
        };
    }
}
