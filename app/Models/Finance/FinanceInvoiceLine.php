<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceInvoiceLine extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_FinanceInvoiceLines';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'FinanceInvoiceLineId';
    }

    protected $fillable = [
        'InvoiceID',
        'InvoiceLineName',
        'Description',
        'UnitCost',
        'Quantity',
        'Tax',
        'TaxID',
        'TaxAmount',
        'CurrencyID',
        'Discount',
        'Total',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];
}
