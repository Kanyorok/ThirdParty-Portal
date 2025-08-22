<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQCriteria extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_RFQCriteria';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'RFQID',
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
        return 'RFQCriteriaID';
    }

    public function criteria()
    {
        return $this->belongsTo(RFQSettingCriteria::class, 'CriteriaID', 'id');
    }

    public function section()
    {
        return $this->belongsTo(RFQSettingSection::class, 'SectionID', 'id');
    }

    public function rfq()
    {
        return $this->belongsTo(RFQ::class, 'RFQID', 'Id');
    }

    public function weightedSection()
    {
        return $this->belongsTo(RFQSection::class, 'SectionID', 'id');
    }

}
