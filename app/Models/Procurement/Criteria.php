<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Criteria extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_Criterias';
    protected $fillable = [
        'CriteriaName',
        'Description',
        'SectionID',
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

    public function rfqSection()
    {
        return $this->belongsTo(RFQSection::class, 'SectionID', 'SectionID');
    }

    public function getRouteKeyName(): string
    {
        return 'CriteriaID';
    }
}
