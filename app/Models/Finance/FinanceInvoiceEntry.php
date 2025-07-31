<?php

namespace App\Models\Finance;

use App\Models\Procurement\Order;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceInvoiceEntry extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $primaryKey = 'Id';
    protected $table = 't_FinanceInvoiceEntry';
    protected $fillable = [
        'InvoiceNumber',
        'SupplierID',
        'CurrencyID',
        'ExchangeRate',
        'POReference',
        'GRNReference',
        'InvoiceDate',
        'InvoiceAmount',
        'Description',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey() : string
    {
        return 'FinanceInvoiceEntryId';
    }

    public function orders()
    {
        return $this->belongsTo(Order::class, 'SupplierID', 'Id');
    }
}