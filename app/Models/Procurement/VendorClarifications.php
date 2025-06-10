<?php

namespace App\Models\Procurement;

use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorClarifications extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_VendorClarifications';
    protected $primaryKey = 'ClarificationID';
    public $incrementing = true;

    public static function getPrimaryKey(): string
    {
        return 'VendorClarificationsId';
    }

    protected $fillable = [
        'ClarificationID',
        'TenderID',
        'VendorID',
        'Question',
        'QuestionDate',
        'Answer',
        'AnswerDate',
        'ISPUBLISHEDTOALL',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    // Define the data types for specific columns (optional, if you need to cast them)
    protected $casts = [
        'QuestionDate' => 'datetime',
        'AnswerDate' => 'datetime',
        'ISPUBLISHEDTOALL' => 'boolean',
    ];

    // Disable timestamps if your table doesn't have created_at and updated_at columns
    public $timestamps = false;

    // Relationships
    public function tenderID()
    {
        return $this->belongsTo(Tender::class, 'TenderID', 'Id');
    }
    public function vendorID()
    {
        return $this->belongsTo(Supplier::class, 'VendorID', 'Id');
    }
}
