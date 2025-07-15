<?php

namespace App\Models\PropertyManagement;
use App\Enums\Property\PropertyInvoiceEnum;

use Illuminate\Database\Eloquent\Model;

class PropertyInvoice extends Model
{
    //
    protected $table = 't_RentInvoice';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'InvoiceNumber',
        'Lease',
        'BillingMonth',
        'InvoiceDate',
        'RentAmount',
        'ServicesCharge',
        'OtherCharges',
        'InvoiceNotes',
        'ParkingFee',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'PropertyInvoiceId';
    }
    protected $casts = [
        'Status' => PropertyInvoiceEnum::class,
    ];

    public function lease()
    {
        return $this->belongsTo(PropertyNewLease::class, 'Lease', 'Id');
    }
}
