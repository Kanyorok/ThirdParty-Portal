<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyPaymentFrequency extends Model
{
    //
    protected $table = 't_PaymentFrequency';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'FrequencyName',
        'FrequencyCode',
        'NumberOfMonths',
        'Description',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

}
