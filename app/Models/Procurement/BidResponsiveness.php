<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Auth\User;
use App\Models\Procurement\TenderSupplier;


class BidResponsiveness extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_BidResponsiveness';
    protected $primaryKey = 'Id';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
