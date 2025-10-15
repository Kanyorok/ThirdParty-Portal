<?php

namespace App\Models\Insurance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MedicalFundContributor extends Model
{
    use SoftDeletes;

    protected $table = 't_MedicalFundContributors';
    protected $primaryKey = 'ID';

    public $timestamps = true;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'FundID','PartyID','ContributorNo','FullName','Email','Phone',
        'EffectiveFrom','EffectiveTo','Status',
        'CreatedBy','ModifiedBy','DeletedBy'
    ];

    protected $casts = [
        'EffectiveFrom' => 'date',
        'EffectiveTo'   => 'date',
        'CreatedOn'     => 'datetime',
        'ModifiedOn'    => 'datetime',
        'DeletedOn'     => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($m) { $m->CreatedBy = Auth::id(); $m->ModifiedBy = Auth::id(); $m->Status = $m->Status ?: 'Active'; });
        static::updating(function ($m) { $m->ModifiedBy = Auth::id(); });
        static::deleting(function ($m) { $m->DeletedBy = Auth::id(); $m->save(); });
    }

    public function fund()          
    { 
        return $this->belongsTo(MedicalFund::class, 'FundID','ID'); 
    }
    public function beneficiaries() 
    { 
        return $this->hasMany(MedicalFundBeneficiary::class, 'ContributorID','ID'); 
    }
    public function contributions() 
    { 
        return $this->hasMany(MedicalFundContribution::class, 'ContributorID','ID'); 
    }
    public function disbursements() 
    { 
        return $this->hasMany(MedicalFundDisbursement::class, 'ContributorID','ID'); 
    }
// (Optional helper to compute expected monthly premium)
    public function getPackagePremiumTotalAttribute()
    {
        return (float) $this->packages()->sum('Premium');
    }

    public function packages() {
        return $this->belongsToMany(MedicalFundPackage::class, 't_MedicalFundContributorPackages', 'ContributorID', 'PackageID')
        ->withPivot(['IsActive','SubscribedOn','IsPrimary']);
  
    }
    public function primaryPackage() {
    return $this->packages()->wherePivot('IsActive',1)->wherePivot('IsPrimary',1)->first();
    }
}
