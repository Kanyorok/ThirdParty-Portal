<?php

namespace App\Models\Licensing;

use Illuminate\Database\Eloquent\Model;

class LicenseAudit extends Model
{
    protected $table = 't_LicenseAudit';
    public $timestamps = false;

    protected $fillable = [
        'EventAt', 'Event', 'Detail',
    ];
}
