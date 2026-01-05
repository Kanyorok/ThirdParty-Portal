<?php

namespace App\Models\Procurement;

use App\Models\ThirdParies\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo};

// This is for suppliers
class TenderVendor extends Model
{
    protected $table = 't_TenderVendors';
    protected $primaryKey = 'TenderVendorID';
    protected $keyType = 'integer';
    public $incrementing = true;

    public function tender(): BelongsTo {
        return $this->belongsTo(Tender::class, 'TenderID');
    }

    public function supplier(): BelongsTo {
        return $this->belongsTo(Supplier::class, 'SupplierID');
    }

}
