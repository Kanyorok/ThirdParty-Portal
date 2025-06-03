<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyReceipt extends Model
{
    //
    protected $table = 't_RentReceipt';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'InvoiceID',
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

}
