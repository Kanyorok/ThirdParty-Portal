<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 't_Branches';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'Name',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];
}
