<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ControlType extends Model
{
    protected $table = 't_ControlTypes';
    protected $primaryKey = 'Id';
    public $timestamps = true;

    protected $fillable = [
        'Name',
        'Description',
        'IsActive'
    ];
}
