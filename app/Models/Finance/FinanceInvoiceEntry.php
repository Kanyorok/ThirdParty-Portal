<?php

namespace App\Models\Finance;

use App\Models\Auth\User;
use App\Models\Core\Currency;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentRelation;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\Order;
use App\Models\ThirdParies\Supplier;
use App\Models\ThirdParty\ThirdParties;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceInvoiceEntry extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use DocumentsTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $primaryKey = 'Id';
    protected $table = 't_FinanceInvoiceEntry';
    protected $fillable = [
        'InvoiceNumber',
        'SupplierID',
        'ThirdPartyID',
        'CurrencyID',
        'ExchangeRate',
        'POReference',
        'Status',
        'DocumentTypeID',
        'GRNReference',
        'ApprovalStatus',
        'ApprovalReason',
        'InvoiceDate',
        'InvoiceAmount',
        'TaxID',
        'TaxAmount',
        'TaxPercentage',
        'TotalAmount',
        'DueDate',
        'Amount', // Added for v2 compatibility
        'DueDate', // Added for v2 functionality
        'Description',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'InvoiceAmount' => 'float',
        'TaxAmount' => 'float',
        'TaxPercentage' => 'float',
        'TotalAmount' => 'float',
        'ExchangeRate' => 'float',
    ];

    public static function getPrimaryKey(): string
    {
        return 'FinanceInvoiceEntryId';
    }

    public function orders()
    {
        return $this->belongsTo(Order::class, 'SupplierID', 'Id');
    }

    public function suppliers()
    {
        return $this->belongsTo(Supplier::class, 'SupplierID', 'Id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierID', 'Id');
    }

    public function thirdParty()
    {
        return $this->belongsTo(ThirdParties::class, 'SupplierID', 'Id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyID', 'Id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'POReference', 'Id');
    }

    // 🔹 GRN (One-to-One)
    public function grn()
    {
        return $this->belongsTo(GoodsReceipt::class, 'GRNReference', 'id');
    }

    /**
     * Relation to uploaded documents - Override DocumentsTrait to fix polymorphic issue
     */
    public function documents()
    {
        return $this->hasManyThrough(
            Document::class,
            DocumentRelation::class,
            'RelatedID', // Foreign key on DocumentRelation table
            'Id', // Foreign key on Document table
            'Id', // Local key on current model
            'DocumentId' // Local key on DocumentRelation table
        )->where('t_DocumentRelations.Related', 'FinanceInvoiceEntryId');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }
}
