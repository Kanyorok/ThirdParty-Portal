<?php

namespace App\Models\Procurement;

use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorClarifications extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_VendorClarifications';
    protected $primaryKey = 'ClarificationID';
    public $incrementing = true;

    public static function getPrimaryKey(): string
    {
        return 'ClarificationID';
    }

    protected $fillable = [
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
        'DeletedOn',
    ];

    // Define the data types for specific columns
    protected $casts = [
        'QuestionDate' => 'datetime',
        'AnswerDate' => 'datetime',
        'ISPUBLISHEDTOALL' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    // Use custom timestamps
    public $timestamps = true;

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
