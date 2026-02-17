<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class PolicyCategorycopy extends Model
{
    protected $table = 't_PolicyCategories';
    protected $primaryKey = 'Id';
    public $timestamps = true;

    protected $fillable = [
        'Name',
        'Description',
        'IsActive',
    ];
}
