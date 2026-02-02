<?php

namespace App\Models\BR;

use App\Models\Communication\SMS;
use App\Models\Core\Task;
use App\Models\CRM\DebtRecovery\LoanAssignment;
use App\Models\CRM\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DebtProduct extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    protected $connection = 'sqlsrv';
    protected $keyType = 'string';
    protected $table = 't_AdvancesReport';//synnym
    protected $primaryKey = 'AccountID';//null;

    protected $casts = [
                        'MaturityDate' => 'datetime',
                        'processDate' => 'datetime',
                        'OutstandingBalance' => 'decimal:2',
                        'ArrearsAmount' => 'decimal:2',
                        'ArrearsDays' => 'integer',
                       ];

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    public function getRouteKeyName(): string
    {
        return 'LoanID';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }

    public function parameters(): HasMany
    {
        return $this->hasMany(ProductParameter::class, 'ProductID', 'ProductID');
    }

    public function getMorphClass(): string
    {
        return $this->getRouteKeyName();
    }

    public function guarantors(): HasMany
    {
        return $this->hasMany(Guarantor::class, 'AccountID', 'AccountID');
    }

    public function collaterals(): HasMany
    {
        return $this->hasMany(CollateralAccount::class, 'AccountID', 'AccountID');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'OurBranchID', 'OurBranchID');
    }

    public function crmsms(): MorphMany
    {
        return $this->morphMany(SMS::class, 'source', 'Source', 'SourceID', 'AccountID');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'ClientID', 'ClientID');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'source', 'Source', 'SourceID', 'AccountID');
    }

    public function schedules(): MorphMany
    {
        return $this->morphMany(Schedule::class, 'source', 'Source', 'SourceID', 'AccountID');
    }

    public function assignment(): HasMany
    {
        return $this->hasMany(LoanAssignment::class, 'AccountID', 'AccountID');
    }
}
