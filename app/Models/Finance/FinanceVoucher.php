<?php

namespace App\Models\Finance;

use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceVoucher extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = "t_FinanceVoucher";
    protected $primaryKey = 'Id';
    protected $fillable = [
        'VoucherNo',
        'InvoiceNo',
        'TotAmnt',
        'PaymentMethod',
        'PaymentType',
        'StartDate',
        'Frequency',
        'Status',
        'Description',
        'Reasons',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'FinanceVoucherId';
    }

    public function invoice()
    {
        return $this->belongsTo(FinanceInvoiceEntry::class, 'InvoiceNo', 'Id');
    }
}
