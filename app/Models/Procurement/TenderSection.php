<?php

namespace App\Models\procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderSection extends Model
{
    use UserActorTrait,SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_Sections';

    protected $fillable = [
        'SectionName',
        'Description',
        'IsActive',
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
        return $this->belongsTo(Tender::class, 'TenderRef', 'TenderRef');
    }
    public function criteria()
    {
        return $this->hasMany(TenderCriteria::class, 'SectionID', 'TenderSectionID');
    }
    public function bids()
    {
        return $this->hasMany(BidSubmission::class, 'TenderRef', 'TenderRef');
    }
}
