<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderSection extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_TenderSection';

    protected $fillable = [
        'TenderID',
        'SectionID',
        'Weight',
        'IsActive',
        'Comments',
        'CreatedBy',
        'ModifiedBy',
    ];
    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    public function getRouteKeyName(): string
    {
        return 'TenderSectionID';
    }

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'TenderID', 'Id');
    }

    public function criteria()
    {
        return $this->hasMany(Criteria::class, 'SectionID', 'id');
    }

    public function TenderCriteria()
    {
        return $this->hasMany(TenderCriteria::class, 'TenderID', 'id');
    }

    public function sections()
    {
        return $this->belongsTo(Section::class, 'SectionID', 'Id');
    }

    public function bids()
    {
        return $this->hasMany(BidSubmission::class, 'TenderRef', 'TenderRef');
    }

    /**
     * Helper method to validate if section weights sum to 100% for a tender
     */
    public static function validateWeightsForTender($tenderId)
    {
        $totalWeight = self::where('TenderID', $tenderId)
            ->where('IsActive', true)
            ->sum('Weight');

        return [
            'is_valid' => abs($totalWeight - 100) < 0.01,
            'total_weight' => $totalWeight
        ];
    }

    /**
     * Get sections with criteria for a specific tender
     */
    public static function getTenderSectionsWithCriteria($tenderId)
    {
        return self::with(['sections.criteria'])
            ->where('TenderID', $tenderId)
            ->where('IsActive', true)
            ->orderBy('Weight', 'desc')
            ->get();
    }
}
