<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class FilingType extends Model
{
    protected $table = 't_FilingTypes';
    protected $primaryKey = 'Id';
    public $timestamps = true;

    protected $fillable = [
        'Name',
        'Description',
        'IsActive'
    ];
}
