<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyInvoice extends Model
{
    //
    protected $table = 't_RentInvoice';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Lease',
        'BillingMonth',
        'InvoiceDate',
        'RentAmount',
        'ServicesCharge',
        'OtherCharges',
        'InvoiceNotes',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

}
