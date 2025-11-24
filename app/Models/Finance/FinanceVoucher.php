<?php

namespace App\Models\Finance;

use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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
        'TotalAmount',
        'PaymentMethod',
        'PaymentType',
        'StartDate',
        'EndDate',
        'Frequency',
        'ApprovalStatus',
        'ApprovalReason',
        'Status',
        'IsProcessed',
        'Description',
        'Reasons',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'FinanceVoucherId';
    }


    public static function booted()
    {
        static::creating(function ($voucher) {
            // Get the next auto-increment ID (if DB driver supports it)
            $nextId = self::max('Id') + 1;

            // Generate current date components
            $datePart = now()->format('Ymd');

            // Generate random 3-letter string
            $randomPart = strtoupper(Str::random(3));

            // Combine all parts
            $voucher->VoucherNo = 'VCN-' . $datePart . '-' . $randomPart . $nextId;
        });
    }


    public function invoice()
    {
        return $this->belongsTo(FinanceInvoiceEntry::class, 'InvoiceNo', 'Id')
            ->where('ApprovalStatus', 'posted');
    }
}
