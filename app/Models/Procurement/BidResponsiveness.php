<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BidResponsiveness extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $table = 't_BidResponsiveness';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'TenderSupplierID',
        'SubmittedTimely',
        'HasMandatoryDocuments',
        'IsEligible',
        'IsResponsive',
        'Remarks',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'SubmittedTimely' => 'boolean',
        'HasMandatoryDocuments' => 'boolean',
        'IsEligible' => 'boolean',
        'IsResponsive' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    // Relationships
    public static function getPrimaryKey(): string
    {
        return 'BidResponsivenessId';
    }

    public function tenderSupplier()
    {
        return $this->belongsTo(TenderSupplier::class, 'TenderSupplierID', 'id');
    }
}
