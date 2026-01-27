<?php

namespace App\Models\PropertyManagement;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyReceipt extends Model
{
    use SoftDeletes;
    use UserActorTrait;

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
        'ServicesCharge',
        'ParkingFee',
        'OtherCharges',
        'TotalDue',
        'AmountPaidSoFar',
        'Balance',
        'PaymentDate',
        'AmountPaidNow',
        'PaymentMethod',
        'ReferenceNo',
        'Remarks',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'PropertyReceiptId';
    }

    public function invoice()
    {
        return $this->belongsTo(PropertyInvoice::class, 'InvoiceID', 'Id');
    }

    public function tenant()
    {
        return $this->belongsTo(PropertyNewTenant::class, 'TenantId', 'Id');
    }

    public function paymentmethod()
    {
        return $this->belongsTo(CodeDetail::class, 'PaymentMethod', 'ID');
    }

    public static function getAmountPaidSoFar(int $invoiceId): float
    {
        return static::where('InvoiceID', $invoiceId)->sum('AmountPaidNow');
    }

    public function code()
    {
        return $this->belongsTo(CodeDetail::class, 'PaymentMethod', 'ID');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }
}
