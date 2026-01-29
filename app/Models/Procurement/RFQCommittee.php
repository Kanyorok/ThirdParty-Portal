<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQCommittee extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_RFQCommittee';

    protected $primaryKey = 'Id';

    protected $fillable = [
        'RFQID',
        'CommitteeName',
        'AppointmentDate',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'RFQID' => 'integer',
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
        'DeletedOn',
    ];

    public function rfq()
    {
        return $this->belongsTo(RFQ::class, 'RFQID', 'Id');
    }

    public function members()
    {
        return $this->hasMany(RFQCommitteeMember::class, 'CommitteeID', 'Id');
    }

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    public function getRouteKeyName(): string
    {
        return 'RFQCommitteeID';
    }
}
