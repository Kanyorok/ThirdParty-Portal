<?php

namespace App\Models\BR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollateralType extends Model
{
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';
    protected $primaryKey = 'CollateralTypeID';
    protected $connection = 'sqlsrv';
    protected $table = 'syn_t_CollateralType';

    public function category(): BelongsTo
    {
        return $this->belongsTo(SystemCodeDetail::class, 'CollateralCategoryID', 'SubCodeID')->where('ID', 'CollateralCategoryID');
    }
}
