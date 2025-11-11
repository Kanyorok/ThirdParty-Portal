<?php

namespace App\Models\Licensing;

use Illuminate\Database\Eloquent\Model;

class LicenseRecord extends Model
{
    protected $table = 't_Licenses';
    public $timestamps = false;

    protected $fillable = [
        'LicenseId', 'PayloadJson', 'SignatureBase64', 'PublicKeyId', 'Status', 'CreatedOn', 'LastValidatedOn'
    ];
}

