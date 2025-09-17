<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 't_Banks';
    protected $primaryKey = 'BankID';

    // Tell Eloquent which columns to use for timestamps
    public $timestamps = true;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';

    protected $fillable = [
        'BankName','ShortName','BankCode','SwiftCode','ClearingCode',
        'CountryID','EmailID','Phone','Website','IsActive',
        // include these only if you intend to mass-assign them; otherwise omit
        // 'CreatedBy','ModifiedBy','DeletedBy','DeletedOn',
    ];

    protected $casts = [
        'IsActive'  => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn'=> 'datetime',
        'DeletedOn' => 'datetime',
    ];

    // Optional: auto-set CreatedBy / ModifiedBy
    protected static function booted()
    {
        static::creating(function ($m) { $m->CreatedBy  = auth()->id(); });
        static::updating(function ($m) { $m->ModifiedBy = auth()->id(); });
    }

    public function branches()
    {
        return $this->hasMany(BankBranch::class, 'BankID', 'BankID');
    }
}
