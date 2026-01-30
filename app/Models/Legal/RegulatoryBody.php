<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class RegulatoryBody extends Model
{
    protected $table = 't_RegulatoryBodies';
    protected $primaryKey = 'Id';
    public $timestamps = true;

    protected $fillable = [
        'Name',
        'Jurisdiction',
        'ContactPerson',
        'ContactEmail',
        'ContactPhone',
        'IsActive',
    ];
}
