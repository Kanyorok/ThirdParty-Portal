<?php

namespace App\Models\Procurement;

use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BidSubmission extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_BidSubmissions';

    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'BidSubmissionsId';
    }

    protected $fillable = [
        'TenderRef',
        'SupplierName',
        'SubmissionMode',
        'ReceivedAt',
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $casts = [
        'ReceivedAt' => 'datetime',

    ];

    // Relationships
    public function submissionMode()
    {
        return $this->belongsTo(CodeDetail::class, 'SubmissionMode', 'ID');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

}
