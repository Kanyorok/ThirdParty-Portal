<?php

namespace App\Models\Procurement;

use App\Models\ThirdParies\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Procurement\BidResponsiveness;

class TenderSupplier extends Model
{
    use SoftDeletes;

    protected $table = 't_TenderSuppliers';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
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
        return $this->belongsTo(Supplier::class, 'SupplierID', 'Id');
    }

    public function bidResponsiveness()
    {
        return $this->hasOne(BidResponsiveness::class, 'TenderSupplierID');
    }

}
