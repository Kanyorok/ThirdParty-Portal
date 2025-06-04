<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderCriteria extends Model
{
    use UserActorTrait,SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_TenderCriteria';

    protected $fillable = [
        'TenderID',
        'CriteriaID',
        'SectionID',
        'MaxScore',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
    ];
    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'IsActive' => 'boolean',
    ];
    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }
    public function getRouteKeyName(): string
    {
        return 'TenderCriteriaID';
    }
    public function criteria()
    {
        return $this->belongsTo(Criteria::class, 'CriteriaID', 'Id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class, 'SectionID', 'Id');
    }
    public function tender()
    {
        return $this->belongsTo(Tender::class, 'TenderRef', 'TenderRef');
    }
    public function tenderSection()
    {
        return $this->belongsTo(TenderSection::class, 'SectionID', 'TenderSectionID');
    }
    public function tenderCriteria()
    {
        return $this->hasMany(TenderCriteria::class, 'TenderRef', 'TenderRef');
    }
}
