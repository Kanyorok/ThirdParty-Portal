<?php

namespace App\Models\BR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTrx extends Model
{
    //protected $connection = 'brcbs';
    protected $connection = 'sqlsrv';
    public $incrementing = false;
    protected $table = 'v_AccountTrx';
    protected $primaryKey = 'TrxRowID';

    protected function casts(): array
    {
        return ['ValueDate' => 'datetime'];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(SystemCodeDetail::class, 'TrxTypeID', 'SubCodeID')->where('ID', 'TrxTypeID');
    }

    /*public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ProductID','ProductID');
    }*/
}
