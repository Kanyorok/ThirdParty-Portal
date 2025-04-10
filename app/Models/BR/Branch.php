<?php

namespace App\Models\BR;

use App\Models\CrmBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Branch extends Model
{
    protected $table = 'syn_t_SystemBranchSetting';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'OurBranchID';
    public $incrementing = false;
    protected $keyType = 'string';
    //protected $connection = 'brcbs';
    // //protected $table = 't_SystemBranchSetting';

    public static function getPrimaryKey(): string
    {
        return (new self)->primaryKey;
    }

    public function local(): HasOne
    {
        return $this->hasOne(CrmBranch::class, 'BranchID', 'OurBranchID')->latest('Id');
    }
}
