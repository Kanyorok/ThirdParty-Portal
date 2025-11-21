<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use App\Models\Finance\FinanceInvoice;
use App\Models\Finance\FinanceTaxRuleConfiguration;
use App\Models\Auth\User;
class InvoiceTax extends Model
{
    protected $table = 't_FinanceInvoiceTaxes';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'APInvoiceID',
        'ARInvoiceID',
        'TaxID',
        'TaxAmount',
        'TaxPercentage',
        'AmountPaid',
        'SourceType',
        'Sourcetable',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'TaxAmount' => 'decimal:2',
        'TaxPercentage' => 'decimal:2',
        'TaxRate' => 'decimal:2',
        'AmountPaid' => 'decimal:2',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function apInvoice()
    {
        return $this->belongsTo(FinanceInvoice::class, 'APInvoiceID');
    }

    public function arInvoice()
    {
        return $this->belongsTo(FinanceInvoice::class, 'ARInvoiceID');
    }

    public function tax()
    {
        return $this->belongsTo(FinanceTaxRuleConfiguration::class, 'TaxID');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'DeletedBy');
    }

    public function getPrimaryKey(): string
    {
        return 'InvoiceTaxId';
    }
}
