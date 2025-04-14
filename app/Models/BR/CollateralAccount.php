<?php

namespace App\Models\BR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollateralAccount extends Model
{
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    public $incrementing = false;
    public $timestamps = false;
    //protected $connection = 'brcbs';
    protected $connection = 'sqlsrv';
    protected $keyType = 'string';
    protected $table = 'syn_t_AccountCollateral';
    protected $primaryKey = null;

    protected $casts = [
                        'AssignedDate'       => 'datetime',
                        'CreatedOn'          => 'datetime',
                        'ApportionedRatio'   => 'decimal:2',
                        'NetCollateralValue' => 'decimal:2',
                       ];

    public static function primaryKey(): string
    {
        return 'AccountCollateral';
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(DebtProduct::class, 'AccountID', 'AccountID');
    }

    public function collateral(): BelongsTo
    {
        return $this->belongsTo(Collateral::class, 'CollateralID', 'CollateralID');
    }
}
