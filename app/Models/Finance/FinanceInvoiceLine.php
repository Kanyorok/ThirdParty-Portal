<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceInvoiceLine extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_FinanceInvoiceLines';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
