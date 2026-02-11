<?php

namespace App\Models\Finance;

use App\Models\Procurement\ContractMilestone;
use Illuminate\Database\Eloquent\Model;

class APInvoiceMilestone extends Model
{
    protected $table = 't_APInvoiceMilestones';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'FinanceInvoiceID',
        'MilestoneID',
        'BilledAmount',
    ];

    protected $casts = [
        'BilledAmount' => 'float',
    ];

    public function invoice()
    {
        return $this->belongsTo(FinanceInvoiceEntry::class, 'FinanceInvoiceID', 'Id');
    }

    public function milestone()
    {
        return $this->belongsTo(ContractMilestone::class, 'MilestoneID', 'Id');
    }
}

