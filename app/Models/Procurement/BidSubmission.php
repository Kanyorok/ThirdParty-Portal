<?php

namespace App\Models\Procurement;

use App\Models\Core\CodeDetail;
use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BidSubmission extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_BidSubmissions';

    protected $primaryKey = 'Id';

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
