<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceReceiptAllocation extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_FinanceReceiptAllocations';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'ReceiptID',
        'InvoiceID',
        'AmountAllocated',
        'AllocationNotes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'AmountAllocated' => 'decimal:2',
    ];

    public static function getPrimaryKey(): string
    {
        return 'FinanceReceiptAllocationId';
    }

    // Relationships
    public function receipt()
    {
        return $this->belongsTo(FinanceReceipt::class, 'ReceiptID', 'Id');
    }

    public function invoice()
    {
        return $this->belongsTo(FinanceInvoice::class, 'InvoiceID', 'Id');
    }
}
