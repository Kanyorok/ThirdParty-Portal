<?php

namespace App\Models\BR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Collateral extends Model
{
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    public $incrementing = false;
    public $timestamps = false;
    //protected $connection = 'brcbs';
    protected $connection = 'sqlsrv';
    protected $keyType = 'string';
    protected $table = 'syn_t_Collateral';
    protected $primaryKey = 'CollateralID';

    protected $casts = [
        'LodgedDate' => 'datetime',
        'CollateralValue' => 'decimal:2',
    ];

    public static function primaryKey(): string
    {
        return 'CollateralID';
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(CollateralType::class, 'CollateralTypeID', 'CollateralTypeID');
    }

}
