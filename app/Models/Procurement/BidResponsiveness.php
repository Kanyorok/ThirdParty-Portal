<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BidResponsiveness extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    protected $table = 't_BidResponsiveness';
    protected $primaryKey = 'Id';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
