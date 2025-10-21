<?php

namespace App\Models\Insurance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MedicalFundContribution extends Model
{
    use SoftDeletes;

    protected $table = 't_MedicalFundContributions';
    protected $primaryKey = 'ID';

    public $timestamps = true;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'FundID','ContributorType','ContributorID','Amount','ContributionDate','Notes',
        'CreatedBy','ModifiedBy','DeletedBy'
    ];

    protected $casts = [
        'Amount'          => 'decimal:2',
        'ContributionDate'=> 'date',
        'CreatedOn'       => 'datetime',
        'ModifiedOn'      => 'datetime',
        'DeletedOn'       => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($m) { $m->CreatedBy = Auth::id(); $m->ModifiedBy = Auth::id(); });
        static::updating(function ($m) { $m->ModifiedBy = Auth::id(); });
        static::deleting(function ($m) { $m->DeletedBy = Auth::id(); $m->save(); });
    }

    public function fund()
    {
        return $this->belongsTo(MedicalFund::class, 'FundID', 'ID');
    }

    public function contributor()
    { 
        return $this->belongsTo(MedicalFundContributor::class, 'ContributorID','ID'); 
    }
}
