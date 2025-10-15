<?php

namespace App\Models\Insurance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MedicalFund extends Model
{
    use SoftDeletes;

    protected $table = 't_MedicalFunds';
    protected $primaryKey = 'ID';

    public $timestamps = true;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'FundName','ProviderID','CoverageType','CoverageLimit','Description','IsActive',
        'CreatedBy','ModifiedBy','DeletedBy'
    ];

    protected $casts = [
        'IsActive'      => 'boolean',
        'CoverageLimit' => 'decimal:2',
        'CreatedOn'     => 'datetime',
        'ModifiedOn'    => 'datetime',
        'DeletedOn'     => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            $m->CreatedBy = Auth::id();
            $m->ModifiedBy = Auth::id();
            $m->IsActive = $m->IsActive ?? 1;
        });

        static::updating(function ($m) {
            $m->ModifiedBy = Auth::id();
        });

        static::deleting(function ($m) {
            $m->DeletedBy = Auth::id();
            $m->save();
        });
    }

    // Relationships
    public function provider()
    {
        // Adjust model/keys if your provider model differs
        return $this->belongsTo(InsuranceProvider::class, 'ProviderID', 'ID');
    }

    public function beneficiaries()
    {
        return $this->hasMany(MedicalFundBeneficiary::class, 'FundID', 'ID');
    }

    public function contributions()
    {
        return $this->hasMany(MedicalFundContribution::class, 'FundID', 'ID');
    }

    public function disbursements()
    {
        return $this->hasMany(MedicalFundDisbursement::class, 'FundID', 'ID');
    }

    public function contributors() 
    { 
        return $this->hasMany(MedicalFundContributor::class, 'FundID', 'ID'); 
    }

    public function packages() 
    { 
        return $this->hasMany(MedicalFundPackage::class,'FundID','ID');
    }

    
}
