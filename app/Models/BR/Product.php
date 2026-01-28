<?php

namespace App\Models\BR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $primaryKey = 'ProductID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $connection = 'sqlsrv';
    protected $table = 'syn_t_Product';

    public function parameters(): HasMany
    {
        return $this->hasMany(ProductParameter::class, 'ProductID', 'ProductID');
    }
}
