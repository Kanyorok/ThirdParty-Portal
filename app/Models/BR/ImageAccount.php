<?php

namespace App\Models\BR;

use Illuminate\Database\Eloquent\Model;

class ImageAccount extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'syn_t_ImageAccount';//'t_ImageAccount';
    protected $primaryKey = 'ImageID';
    public $incrementing = false;
}
