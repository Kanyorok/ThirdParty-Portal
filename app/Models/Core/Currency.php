<?php

namespace App\Models\Core;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_Currencies';
    protected $primaryKey = 'Id';

    protected $fillable = [
        "Name", "Code", "Symbol", "SymbolNative", "DecimalDigits", "Rounding",
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $hidden = [
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        "DecimalDigits" => "integer",
        "Rounding" => "integer",
    ];

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public static function getPrimaryKey(): string
    {
        return 'CurrencyID';
    }
}
