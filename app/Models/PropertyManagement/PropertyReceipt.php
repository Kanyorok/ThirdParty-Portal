<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyReceipt extends Model
{
    //
    protected $table = 't_RentReceipt';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'InvoiceID',
        'BillingMonth',
        'InvoiceDate',
        'RentAmount',
        'ServiceCharge',
        'OtherCharges',
        'TotalDue',
        'AmountPaid',
        'Balance',
        'PaymentDate',
        'Amount',
        'PaymentMethod',
        'ReferenceNo',
        'Remarks',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'PropertyReceiptId';
    }

    public function invoice()
    {
        return $this->belongsTo(PropertyInvoice::class, 'InvoiceID', 'Id');
    }
}
