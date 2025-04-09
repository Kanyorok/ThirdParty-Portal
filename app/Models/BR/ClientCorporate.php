<?php

namespace App\Models\BR;

use Illuminate\Database\Eloquent\Model;

class ClientCorporate extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'ClientID';
    //protected $table = 't_ClientCorporate';
    //protected $connection = 'brcbs';
    protected $connection = 'sqlsrv';
    protected $table = 'syn_t_ClientCorporate';
    public static function getPrimaryKey(): string
    {
        return 'BR_ClientCorporate';
    }
}
