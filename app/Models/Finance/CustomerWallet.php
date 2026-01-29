<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerWallet extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_FinanceCustomerWallet';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'CustomerID',
        'Balance',
        'TotalDeposits',
        'TotalWithdrawals',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'Balance' => 'decimal:2',
        'TotalDeposits' => 'decimal:2',
        'TotalWithdrawals' => 'decimal:2',
        'IsActive' => 'boolean',
    ];

    public static function getPrimaryKey(): string
    {
        return 'CustomerWalletId';
    }

    // Relationships
    public function customer()
    {
        return $this->belongsTo(\App\Models\ThirdParty\ThirdParties::class, 'CustomerID', 'Id');
    }

    public function transactions()
    {
        return $this->hasMany(CustomerWalletTransaction::class, 'WalletID', 'Id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }

    // Methods
    public function addFunds(float $amount, string $description, string $referenceType = 'manual', int $referenceId = null): CustomerWalletTransaction
    {
        $newBalance = $this->Balance + $amount;

        $transaction = $this->transactions()->create([
            'CustomerID' => $this->CustomerID,
            'TransactionType' => 'deposit',
            'Amount' => $amount,
            'RunningBalance' => $newBalance,
            'ReferenceType' => $referenceType,
            'ReferenceID' => $referenceId,
            'Description' => $description,
            'TransactionDate' => now()->toDateString(),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        $this->update([
            'Balance' => $newBalance,
            'TotalDeposits' => $this->TotalDeposits + $amount,
        ]);

        return $transaction;
    }

    public function deductFunds(float $amount, string $description, string $referenceType = 'manual', int $referenceId = null): ?CustomerWalletTransaction
    {
        if ($this->Balance < $amount) {
            return null; // Insufficient funds
        }

        $newBalance = $this->Balance - $amount;

        $transaction = $this->transactions()->create([
            'CustomerID' => $this->CustomerID,
            'TransactionType' => 'withdrawal',
            'Amount' => $amount,
            'RunningBalance' => $newBalance,
            'ReferenceType' => $referenceType,
            'ReferenceID' => $referenceId,
            'Description' => $description,
            'TransactionDate' => now()->toDateString(),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        $this->update([
            'Balance' => $newBalance,
            'TotalWithdrawals' => $this->TotalWithdrawals + $amount,
        ]);

        return $transaction;
    }

    public static function getOrCreateWallet(int $customerId): self
    {
        return self::firstOrCreate(
            ['CustomerID' => $customerId],
            [
                'Balance' => 0,
                'TotalDeposits' => 0,
                'TotalWithdrawals' => 0,
                'IsActive' => true,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]
        );
    }
}
