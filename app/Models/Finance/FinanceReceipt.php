<?php

namespace App\Models\Finance;

use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceReceipt extends Model
{
    use UserActorTrait;
    use SoftDeletes;
    use DocumentsTrait;

    protected $table = 't_FinanceReceipts';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'ReceiptNumber',
        'CustomerID',
        'ReceiptDate',
        'AmountReceived',
        'PaymentMethod',
        'ReferenceNumber',
        'ValueDate',
        'PostingDate',
        'AttachmentPath',
        'Remarks',
        'Status',
        'ApprovalReason',
        'UnappliedAmount',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'ReceiptDate' => 'date',
        'ValueDate' => 'date',
        'PostingDate' => 'date',
        'AmountReceived' => 'decimal:2',
        'UnappliedAmount' => 'decimal:2',
    ];

    public static function getPrimaryKey(): string
    {
        return 'FinanceReceiptId';
    }

    /**
     * Generate receipt number: RCP-YYYYMMDD-XXXX
     */
    public static function generateReceiptNumber(): string
    {
        $date = Carbon::now()->format('Ymd');
        $sequence = str_pad(self::whereDate('CreatedOn', Carbon::now())->count() + 1, 4, '0', STR_PAD_LEFT);

        return "RCP-{$date}-{$sequence}";
    }

    protected static function booted(): void
    {
        static::creating(function (FinanceReceipt $model) {
            if (empty($model->ReceiptNumber)) {
                $model->ReceiptNumber = self::generateReceiptNumber();
            }
        });
    }

    // Relationships
    public function customer()
    {
        return $this->belongsTo(\App\Models\ThirdParty\ThirdParties::class, 'CustomerID', 'Id');
    }

    public function allocations()
    {
        return $this->hasMany(FinanceReceiptAllocation::class, 'ReceiptID', 'Id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(CodeDetail::class, 'PaymentMethod', 'Value')
            ->where('CodeID', 'PaymentMethod');
    }

    // Scopes
    public function scopePosted($query)
    {
        return $query->where('Status', 'Posted');
    }

    public function scopeDraft($query)
    {
        return $query->where('Status', 'Draft');
    }

    // Accessors
    public function getTotalAllocatedAttribute()
    {
        return $this->allocations->sum('AmountAllocated');
    }

    public function getIsFullyAllocatedAttribute()
    {
        return $this->AmountReceived <= $this->total_allocated;
    }

    public function hasAttachment(): bool
    {
        return ! empty($this->AttachmentPath);
    }
}
