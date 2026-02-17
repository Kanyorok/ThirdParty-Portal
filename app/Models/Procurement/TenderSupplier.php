<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderSupplier extends Model
{
    use SoftDeletes;

    protected $table = 't_TenderSuppliers';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'id';

    protected $fillable = [
        'TenderID',
        'SupplierID',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'tendersuppliersid';
    }

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'TenderID', 'Id');
    }

    public function supplier()
    {
        // CRITICAL FIX: SupplierID references t_SupplierMaster.Id, not t_Suppliers.Id
        return $this->belongsTo(\App\Models\ThirdParty\SupplierMaster::class, 'SupplierID', 'Id');
    }

    public function bidResponsiveness()
    {
        return $this->hasOne(BidResponsiveness::class, 'TenderSupplierID');
    }
}
