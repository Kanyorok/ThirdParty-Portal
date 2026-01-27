<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class IncidentSeverityLevel extends Model
{
    protected $table = 't_IncidentSeverityLevels';
    protected $primaryKey = 'Id';
    public $timestamps = true;

    protected $fillable = [
        'Name',
        'Description',
        'IsActive',
    ];
}
