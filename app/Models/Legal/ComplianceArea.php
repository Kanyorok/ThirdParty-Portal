<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceArea extends Model
{
    protected $table = 't_ComplianceAreas';
    protected $primaryKey = 'Id';
    public $timestamps = true;

    protected $fillable = [
        'Name',
        'Description',
        'IsActive'
    ];
}
