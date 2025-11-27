<?php

namespace App\Models\Finance;

use App\Models\Auth\User;
use App\Models\Core\Currency;
use App\Models\Core\Module;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\ThirdParty\ThirdParties;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FinanceInvoice extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_FinanceInvoices';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $primaryKey = 'Id';

    // NOTE: You set a custom getPrimaryKey for your workflow engine; keep it if required elsewhere.
    public static function getPrimaryKey(): string
    {
        return 'FinanceInvoiceId';
    }

    protected $fillable = [
        'RequestID',
        'IdempotencyKey',     // <— optional; see migration in section C
        'ModuleID',
        'CurrencyID',
        'CustomerID',
        'InvoiceID',
        'InvoiceNumber',
        'InvoiceTitle',
        'InvoiceDate',
        'DueDate',
        'InvoiceRemarks',
        'InvoiceAmount',
        'TaxID',
        'TaxAmount',
        'TaxPercentage',
        'TotalAmount',
        'AmountPaid',
        'IsPaid',
        'SourceTable',
        'IsGenerated',
        'Status',
        'ApprovalStatus',
        'ApprovalReason',

        // Credit application tracking
        'UseCredit',
        'CreditAppliedOn',
        'CreditAppliedBy',
        'CreditApplicationReason',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'InvoiceDate' => 'date',
        'DueDate'     => 'date',
        'IsPaid'      => 'boolean',
        'IsGenerated' => 'boolean',
        'TaxAmount' => 'float',
        'InvoiceAmount' => 'float',
        'TaxPercentage' => 'float',
        'UseCredit' => 'boolean',
        'CreditAppliedOn' => 'datetime',
    ];

    /**
     * Generate a human-friendly RequestID like: REQ-20250821-3F6A9C
     */
    public static function generateRequestId(?string $prefix = 'REQ'): string
    {
        $date = Carbon::now()->format('Ymd');
        $rand = strtoupper(Str::random(6));
        return "{$prefix}-{$date}-{$rand}";
    }

    /**
     * Build a deterministic idempotency key from common fields.
     * Pass in anything that uniquely identifies this invoice intake from the source.
     */
    public static function makeIdempotencyKey(array $parts): string
    {
        // Normalize and hash to a fixed-length token (64 hex chars).
        $payload = json_encode(array_map(
            fn($v) => is_string($v) ? trim(mb_strtoupper($v)) : $v,
            $parts
        ));
        return hash('sha256', $payload);
    }

    /**
     * Auto-generate RequestID on create; compute IdempotencyKey if not provided.
     */
    protected static function booted(): void
    {
        static::creating(function (FinanceInvoice $model) {
            // Ensure RequestID exists
            if (empty($model->RequestID)) {
                $model->RequestID = self::generateRequestId();
            }

            // Optional: default IsGenerated true for system-originated intakes
            if (is_null($model->IsGenerated)) {
                $model->IsGenerated = true;
            }

            // If you added IdempotencyKey column and it’s empty, compute one
            if (empty($model->IdempotencyKey)) {
                $model->IdempotencyKey = self::makeIdempotencyKey([
                    'SourceTable'  => $model->SourceTable,
                    'InvoiceID'    => $model->InvoiceID,     // external ref if any
                    'CustomerID'   => $model->CustomerID,
                    'InvoiceDate'  => optional($model->InvoiceDate)->format('Y-m-d'),
                    'InvoiceTitle' => $model->InvoiceTitle,
                    // add other fields if your sources need them
                ]);
            }
        });

        static::updating(function (FinanceInvoice $model) {
            // Keep IdempotencyKey stable once set; only set if it’s empty.
            if (empty($model->IdempotencyKey)) {
                $model->IdempotencyKey = self::makeIdempotencyKey([
                    'SourceTable'  => $model->SourceTable,
                    'InvoiceID'    => $model->InvoiceID,
                    'CustomerID'   => $model->CustomerID,
                    'InvoiceDate'  => optional($model->InvoiceDate)->format('Y-m-d'),
                    'InvoiceTitle' => $model->InvoiceTitle,
                ]);
            }
        });
    }

    /** Relationships */
    public function lines()
    {
        return $this->hasMany(FinanceInvoiceLine::class, 'InvoiceID', 'Id');
    }

    public function customer(){
        return $this->belongsTo(ThirdParties::class, 'CustomerID', 'Id');
    }

    public function source(){
        return $this->belongsTo(Module::class,'ModuleID','ModuleID');
    }

    public function createdBy(){
        return $this->belongsTo(User::class,'CreatedBy','Id');
    }

    public function modifiedBy(){
        return $this->belongsTo(User::class,'ModifiedBy','Id');
    }

    public function currency(){
        return $this->belongsTo(Currency::class,'CurrencyID','Id');
    }

    public function creditAppliedByUser()
    {
        return $this->belongsTo(User::class, 'CreditAppliedBy', 'Id');
    }

    /**
     * Check if credit has been applied to this invoice
     */
    public function hasCreditApplied(): bool
    {
        return (bool)$this->UseCredit;
    }

    /**
     * Get credit movement for this invoice
     */
    public function creditMovement()
    {
        return $this->hasOne(FinanceCreditMovement::class, 'ReferenceID', 'Id')
            ->where('ReferenceType', 'invoice')
            ->where('MovementType', 'invoice_usage');
    }

    /**
     * Scope to filter invoices with credit applied
     */
    public function scopeWithCreditApplied($query)
    {
        return $query->where('UseCredit', true);
    }

    /**
     * Scope to filter invoices without credit applied
     */
    public function scopeWithoutCreditApplied($query)
    {
        return $query->where('UseCredit', false);
    }

    /**
     * Get receipt allocations for this invoice
     */
    public function receiptAllocations()
    {
        return $this->hasMany(FinanceReceiptAllocation::class, 'InvoiceID', 'Id');
    }

    /**
     * Get receipts that have been applied to this invoice
     */
    public function receipts()
    {
        return $this->hasManyThrough(
            FinanceReceipt::class,
            FinanceReceiptAllocation::class,
            'InvoiceID',
            'Id',
            'Id',
            'ReceiptID'
        );
    }

}
