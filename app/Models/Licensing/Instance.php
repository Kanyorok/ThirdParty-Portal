<?php

namespace App\Models\Licensing;

use Illuminate\Database\Eloquent\Model;

class Instance extends Model
{
    protected $table = 't_Instance';
    public $timestamps = false;

    protected $fillable = [
        'DbGuid', 'HostFingerprint', 'MaxSeenNonce', 'CreatedOn',
    ];
}
