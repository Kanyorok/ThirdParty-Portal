<?php

namespace App\Models\BR;

use Illuminate\Database\Eloquent\Model;

class SystemCodeDetail extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = null;//has none
    protected $connection = 'sqlsrv';
    protected $table = 'syn_t_SystemCodeDetail';

    public static function getPrimaryKey(): string
    {
        return 'BR_SystemCodeDetail';
    }
}
