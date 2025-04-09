<?php

namespace App\Models\BR;

use Illuminate\Database\Eloquent\Model;

class BranchDetails extends Model
{
    public $incrementing = false;
    // protected $connection = 'brcbs';
    protected $table = 'v_SystemBranchActive';
    protected $primaryKey = 'BranchID';
    protected $keyType = 'string';
    protected $connection = 'sqlsrv';
}
