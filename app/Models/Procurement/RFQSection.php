<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQSection extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_RFQSection';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'RFQID',
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
        'IsActive' => 'boolean',
    ];

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    public function getRouteKeyName(): string
    {
        return 'RFQSectionID';
    }


    public function section()
    {
        return $this->belongsTo(\App\Models\Procurement\Section::class, 'SectionID', 'Id');
    }

    public function criteria()
    {
        return $this->hasMany(Criteria::class, 'SectionID', 'SectionID');
    }

    public function rfq()
    {
        return $this->belongsTo(RFQ::class, 'RFQID', 'Id');
    }
}
