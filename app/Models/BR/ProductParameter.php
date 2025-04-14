<?php

namespace App\Models\BR;

use Illuminate\Database\Eloquent\Model;

class ProductParameter extends Model
{
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    public $incrementing = false;
    public $timestamps = false;
    protected $connection = 'sqlsrv';
    protected $keyType = 'string';
    protected $table = 'syn_t_ProductParameter';
    protected $primaryKey = null;
}
