<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class BankBranch extends Model
{
    protected $table = 't_BankBranches';
    protected $primaryKey = 'BranchID';

    public $timestamps = true;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';

    protected $fillable = [
        'BankID','BranchCode','BranchName','Address1','Address2',
        'CityID','CountryID','ZipCode','Phone','EmailID','IsActive',
        // 'CreatedBy','ModifiedBy','DeletedBy','DeletedOn',
    ];

    protected $casts = [
        'IsActive'  => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn'=> 'datetime',
        'DeletedOn' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($m) { $m->CreatedBy  = auth()->id(); });
        static::updating(function ($m) { $m->ModifiedBy = auth()->id(); });
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'BankID', 'BankID');
    }
}
