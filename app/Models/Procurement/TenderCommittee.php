<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderCommittee extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_TenderCommittee';

    protected $fillable = [
        'TenderID',
        'CommitteeType',
        'ReferenceId',
        'CommitteeName',
        'AppointmentDate',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn'
    ];

    protected $casts = [
        'CommitteeType' => 'string',
        'ReferenceId' => 'integer',
        'TenderID' => 'integer',
        'CommitteeName' => 'string',
        'AppointmentDate' => 'date',
        'IsActive' => 'boolean',
        'CreatedBy' => 'integer',
        'CreatedOn' => 'datetime',
        'ModifiedBy' => 'integer',
        'ModifiedOn' => 'datetime',
        'DeletedBy' => 'integer',
        'DeletedOn' => 'datetime',
    ];

    protected $dates = [
        'AppointmentDate',
        'CreatedOn',
        'ModifiedOn',
        'DeletedOn'
    ];

    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    public function getRouteKeyName(): string
    {
        return 'TenderCommitteeID';
    }

    public function members()
    {
        return $this->hasMany(TenderCommitteeMember::class, 'CommitteeID', 'Id');
    }

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'TenderID', 'Id');
    }

    public function reference()
    {
        return match ($this->CommitteeType) {
            'tender' => $this->belongsTo(Tender::class, 'ReferenceId'),
            'rfq' => $this->belongsTo(RFQ::class, 'ReferenceId'),
            default => null,
        };
    }
}
