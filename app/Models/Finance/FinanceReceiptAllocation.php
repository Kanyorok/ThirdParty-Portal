<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceReceiptAllocation extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_FinanceReceiptAllocations';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
        'DeletedOn'
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
