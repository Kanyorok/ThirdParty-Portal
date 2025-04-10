<?php

namespace App\Models\BR;

use Illuminate\Database\Eloquent\Model;

class ClientIndividual extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'ClientID';
    // protected $table = 't_ClientIndividual';
    //protected $connection = 'brcbs';
    protected $table = 'syn_t_ClientIndividual';
    protected $connection = 'sqlsrv';

    public static function getPrimaryKey(): string
    {
        return 'BR_ClientIndividual';
    }
}
