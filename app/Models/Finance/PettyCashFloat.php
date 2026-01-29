<?php

namespace App\Models\Finance;

use App\Models\Core\Currency;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCashFloat extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    protected $table = 't_PettyCashFloats';
    protected $primaryKey = 'FloatID';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'Code', 'Name', 'CurrencyID', 'CustodianUserID',
        'FloatLimit', 'ReorderLevel', 'OpeningBalance', 'IsActive',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'FloatLimit' => 'decimal:2',
        'ReorderLevel' => 'decimal:2',
        'OpeningBalance' => 'decimal:2',
        'IsActive' => 'boolean',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'Id');
    }

    public function vouchers()
    {
        return $this->hasMany(PettyCashVoucher::class, 'FloatID', 'FloatID');
    }

    public static function getPrimaryKey(): string
    {
        return 'FloatID';
    }
}
