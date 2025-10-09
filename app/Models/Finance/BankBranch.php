<?php

namespace App\Models\Finance;

use App\Models\Auth\User;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'CountryID', 'Id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(Locality::class, 'CityID', 'ID');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }
}
