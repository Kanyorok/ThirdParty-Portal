<?php

namespace App\Models\Procurement;

use App\Models\ThirdParies\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderSupplier extends Model
{
    use SoftDeletes;
    protected $table = 't_TenderSuppliers';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'TenderID',
        'SupplierID',
        'CreatedBy', 
        'ModifiedBy',
    ];

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'TenderID');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierID');
    }
}
