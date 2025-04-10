<?php

namespace App\Models\BR;

use Illuminate\Database\Eloquent\Model;

class UserCodeDetail extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = null;//has none
    //protected $connection = 'brcbs';
    //protected $table = 't_UserCodeDetail';
    protected $connection = 'sqlsrv';
    protected $table = 'syn_t_UserCodeDetail';

    public static function getPrimaryKey(): string
    {
        return 'BR_UserCodeDetail';
    }
}
