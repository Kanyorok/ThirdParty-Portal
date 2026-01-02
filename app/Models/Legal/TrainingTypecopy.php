<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class TrainingTypecopy extends Model
{
    protected $table = 't_TrainingTypes';
    protected $primaryKey = 'Id';
    public $timestamps = true;

    protected $fillable = [
        'Name',
        'Description',
        'IsActive'
    ];
}
