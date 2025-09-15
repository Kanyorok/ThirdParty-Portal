<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class CashbookLine extends Model
{
    protected $table = 't_CashbookLines';
    protected $primaryKey = 'LineID';

    public $timestamps = true;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';

    protected $fillable = [
        'CashbookID','GLAccountID','Description','AmountDr','AmountCr'
    ];

    protected $casts = [
        'AmountDr' => 'decimal:2',
        'AmountCr' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($m) { $m->CreatedBy  = auth()->id(); });
        static::updating(function ($m) { $m->ModifiedBy = auth()->id(); });
    }

    public function header()
    {
        return $this->belongsTo(Cashbook::class, 'CashbookID', 'CashbookID');
    }
}
