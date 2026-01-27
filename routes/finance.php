<?php

use App\Http\Controllers\Finance\AgingReportARController;
use App\Http\Controllers\Finance\AgingReportController;
use App\Http\Controllers\Finance\BalanceSheetController;
use App\Http\Controllers\Finance\BankAccountSetupController;
use App\Http\Controllers\Finance\BankBranchController;
use App\Http\Controllers\Finance\BankController;
use App\Http\Controllers\Finance\BankReconciliationController;
use App\Http\Controllers\Finance\BankTransactionController;
use App\Http\Controllers\Finance\BankTransferController;
use App\Http\Controllers\Finance\CashBookController;
use App\Http\Controllers\Finance\CashFlowStatementController;
use App\Http\Controllers\Finance\CashManagementController;
use App\Http\Controllers\Finance\ChartOfAccountsController;
use App\Http\Controllers\Finance\ChequeBookController;
use App\Http\Controllers\Finance\ChequeController;
use App\Http\Controllers\Finance\ChequeManagementController;
use App\Http\Controllers\Finance\COASegmentController;
use App\Http\Controllers\Finance\ConsolidationReportsController;
use App\Http\Controllers\Finance\CreditAdjustmentController;
use App\Http\Controllers\Finance\CreditManagementController;
use App\Http\Controllers\Finance\CreditNoteController;
use App\Http\Controllers\Finance\CustomerMasterController;
use App\Http\Controllers\Finance\CustomerStatementController;
use App\Http\Controllers\Finance\DebitNoteController;
use App\Http\Controllers\Finance\FinanceTaxTypeController;
use App\Http\Controllers\Finance\GLDynamicController;
use App\Http\Controllers\Finance\GLMappingController;
use App\Http\Controllers\Finance\HierarchyViewerController;
use App\Http\Controllers\Finance\IncomeStatementController;
use App\Http\Controllers\Finance\InvoiceApprovalController;
use App\Http\Controllers\Finance\InvoiceEntryController;
use App\Http\Controllers\Finance\InvoiceEntryV2Controller;
use App\Http\Controllers\Finance\InvoiceGenerationController;
use App\Http\Controllers\Finance\JournalBatchController;
use App\Http\Controllers\Finance\JournalEntryController;
use App\Http\Controllers\Finance\LedgerAccountsController;
use App\Http\Controllers\Finance\LedgerReportController;
use App\Http\Controllers\Finance\PaymentAndReceiptsController;
use App\Http\Controllers\Finance\PaymentProcessingController;
use App\Http\Controllers\Finance\PaymentVoucherController;
use App\Http\Controllers\Finance\PeriodManagementController;
use App\Http\Controllers\Finance\PettyCashController;
use App\Http\Controllers\Finance\PettyCashFloatController;
use App\Http\Controllers\Finance\POInvoiceSyncController;
use App\Http\Controllers\Finance\ReceiptsPostingController;
use App\Http\Controllers\Finance\ReconDashboardController;
use App\Http\Controllers\Finance\ReconUploadController;
use App\Http\Controllers\Finance\RecurrentJournalController;
use App\Http\Controllers\Finance\ReportsController;
use App\Http\Controllers\Finance\ReversingJournalController;
use App\Http\Controllers\Finance\SalaryJournalTemplateController;
use App\Http\Controllers\Finance\TaxEfillingController;
use App\Http\Controllers\Finance\TaxGLMappingController;
use App\Http\Controllers\Finance\TaxJurisdictionController;
use App\Http\Controllers\Finance\TaxReturnGeneratorController;
use App\Http\Controllers\Finance\TaxRuleController;
use App\Http\Controllers\Finance\TaxSummaryReportController;
use App\Http\Controllers\Finance\TransactionTypesController;
use App\Http\Controllers\Finance\TrialBalanceController;
use App\Http\Controllers\Finance\VendorMasterController;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Support\Facades\Route;

// Newly added

Route::middleware(['module:1100000'])->prefix('finance')->group(function () {
    Route::resource('journalbatch', JournalBatchController::class);
    Route::resource('ledgeraccounts', LedgerAccountsController::class);
    Route::resource('transactiontypes', TransactionTypesController::class);
    Route::resource('bankreconciliation', BankReconciliationController::class);
    Route::resource('periodmanagement', PeriodManagementController::class);
    Route::resource('vendormaster', VendorMasterController::class);
    // Original invoice entry (kept for compatibility)
    // Route::resource('invoiceentry', InvoiceEntryController::class);

    // New simplified invoice entry approach
    Route::resource('invoiceentry', InvoiceEntryV2Controller::class)->names([
        'index' => 'invoiceentry.index',
        'create' => 'invoiceentry.create',
        'store' => 'invoiceentry.store',
        'show' => 'invoiceentry.show',
        'edit' => 'invoiceentry.edit',
        'update' => 'invoiceentry.update',
        'destroy' => 'invoiceentry.destroy',
    ]);

    // AJAX routes for supplier search in new invoice entry
    Route::post('invoiceentry-v2/api/suppliers/quick-search', [InvoiceEntryV2Controller::class, 'quickSearchSuppliers'])->name('finance.invoiceentry-v2.api.suppliers.quick-search');
    Route::post('invoiceentry-v2/api/suppliers/search', [InvoiceEntryV2Controller::class, 'findSupplier'])->name('finance.invoiceentry-v2.api.suppliers.search');
    Route::resource('customermaster', CustomerMasterController::class);
    Route::resource('invoicegeneration', InvoiceGenerationController::class);
    Route::resource('creditnote', CreditNoteController::class);
    Route::resource('debitnote', DebitNoteController::class);
    Route::resource('paymentprocessing', PaymentProcessingController::class);
    Route::get('agingreport/suppliers/search', [AgingReportController::class, 'supplierLookup'])->name('agingreport.suppliers.lookup');
    Route::resource('agingreport', AgingReportController::class);
    // Receipts posting routes moved to separate group below to avoid conflicts
    Route::resource('creditmanagement', CreditManagementController::class);
    Route::post('creditmanagement/{id}/approve', [CreditManagementController::class, 'approve'])->name('creditmanagement.approve');
    Route::get('creditmanagement/{id}/history', [CreditManagementController::class, 'history'])->name('creditmanagement.history');
    Route::resource('creditadjustment', CreditAdjustmentController::class);
    Route::get('creditadjustment/create/{id}', [CreditAdjustmentController::class, 'createWithId'])->name('creditadjustment.createWithId');
    Route::post('creditadjustment/{id}/approve', [CreditAdjustmentController::class, 'approve'])->name('creditadjustment.approve');

    // Invoice generation with credit integration
    Route::resource('invoicegeneration', InvoiceGenerationController::class);
    Route::post('invoicegeneration/check-credit', [InvoiceGenerationController::class, 'checkCredit'])->name('invoicegeneration.check-credit');
    Route::post('invoicegeneration/{id}/apply-credit', [InvoiceGenerationController::class, 'applyCredit'])->name('invoicegeneration.apply-credit');

    // Debug route to check credit utilization
    Route::get('debug/credit-utilization/{creditId}', function ($creditId) {
        $credit = \App\Models\Finance\FinanceCreditManagement::with('customer')->findOrFail($creditId);

        // Get all invoices for this customer
        $allInvoices = \App\Models\Finance\FinanceInvoice::with('customer')
            ->where('CustomerID', $credit->CustomerID)
            ->get(['Id', 'CustomerID', 'TotalAmount', 'ApprovalStatus', 'UseCredit', 'InvoiceNumber']);

        // Also search by customer name
        $invoicesByName = \App\Models\Finance\FinanceInvoice::with('customer')
            ->whereHas('customer', function ($query) use ($credit) {
                $query->where('ThirdPartyName', 'like', "%{$credit->customer->ThirdPartyName}%");
            })
            ->get(['Id', 'CustomerID', 'TotalAmount', 'ApprovalStatus', 'UseCredit', 'InvoiceNumber']);

        return response()->json([
            'credit_profile' => [
                'id' => $credit->Id,
                'customer_id' => $credit->CustomerID,
                'customer_name' => $credit->customer->ThirdPartyName,
                'credit_limit' => $credit->CreditLimit,
                'effective_from' => $credit->EffectiveFrom,
            ],
            'invoices_by_customer_id' => $allInvoices->toArray(),
            'invoices_by_customer_name' => $invoicesByName->toArray(),
            'summary' => [
                'total_invoices_by_id' => $allInvoices->count(),
                'total_invoices_by_name' => $invoicesByName->count(),
                'draft_with_credit_by_id' => $allInvoices->where('ApprovalStatus', 'draft')->where('UseCredit', true)->count(),
                'draft_with_credit_by_name' => $invoicesByName->where('ApprovalStatus', 'draft')->where('UseCredit', true)->count(),
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    });

    // Debug route to check invoices with credit applied
    Route::get('debug/invoices-with-credit', function () {
        $invoicesWithCredit = \App\Models\Finance\FinanceInvoice::with('customer')
            ->where('UseCredit', true)
            ->get(['Id', 'CustomerID', 'TotalAmount', 'ApprovalStatus', 'UseCredit', 'InvoiceNumber', 'CreditAppliedOn']);

        return response()->json([
            'total_invoices_with_credit' => $invoicesWithCredit->count(),
            'total_amount' => $invoicesWithCredit->sum('TotalAmount'),
            'invoices' => $invoicesWithCredit->toArray(),
        ], 200, [], JSON_PRETTY_PRINT);
    });


    Route::get('agingreportar/customers/search', [AgingReportARController::class, 'customerLookup'])->name('agingreportar.customers.lookup');
    Route::resource('agingreportar', AgingReportARController::class);

    // Customer Statement Select2 API and custom routes
    Route::get('api/thirdparties/select2', [CustomerStatementController::class, 'select2ThirdParties'])->name('thirdparties.select2');
    Route::get('customerstatement/{thirdPartyId}', [CustomerStatementController::class, 'statement'])->name('customerstatement.statement');
    Route::resource('customerstatement', CustomerStatementController::class)->only(['index']);

    Route::resource('paymentvoucher', PaymentVoucherController::class);
    Route::resource('cashmanagement', CashManagementController::class);
    Route::resource('chequemanagement', ChequeManagementController::class);
    Route::resource('paymentandreceiptvouchers', PaymentAndReceiptsController::class);
    Route::resource('balancesheet', BalanceSheetController::class);
    Route::resource('cashflowstatement', CashFlowStatementController::class);
    Route::resource('consolidationreports', ConsolidationReportsController::class);
    Route::resource('incomestatement', IncomeStatementController::class);

    // Newly appended resource routes
    Route::resource('coasegment', COASegmentController::class);
    Route::resource('chartofaccounts', ChartOfAccountsController::class);
    Route::resource('gldynamic', GLDynamicController::class);
    Route::resource('glmapping', GLMappingController::class);
    Route::resource('hierarchyviewer', HierarchyViewerController::class);
    Route::resource('invoiceapproval', InvoiceApprovalController::class);
    Route::resource('journalentry', JournalEntryController::class);
    Route::resource('ledgerreport', LedgerReportController::class);
    Route::resource('poinvoicesync', POInvoiceSyncController::class);
    Route::resource('recondashboard', ReconDashboardController::class);
    Route::resource('reconupload', ReconUploadController::class);
    Route::resource('recurrentjournal', RecurrentJournalController::class);
    Route::resource('reversingjournal', ReversingJournalController::class);
    Route::get('/reversingjournal/preview/{id}', [\App\Http\Controllers\Finance\ReversingJournalController::class, 'preview'])->name('reversingjournal.preview');
    Route::resource('salaryjournaltemplate', SalaryJournalTemplateController::class);
    Route::resource('taxefilling', TaxEfillingController::class);
    Route::resource('taxglmapping', TaxGLMappingController::class);
    Route::resource('taxjurisdiction', TaxJurisdictionController::class);
    Route::resource('taxreturngenerator', TaxReturnGeneratorController::class);
    Route::resource('taxrule', TaxRuleController::class);
    Route::resource('taxsummaryreport', TaxSummaryReportController::class);
    Route::resource('trialbalance', TrialBalanceController::class);
    Route::resource('taxtypes', FinanceTaxTypeController::class);

    Route::resource('hierachyviewer', HierarchyViewerController::class);
    Route::resource('segments', COASegmentController::class);
    Route::resource('journalentry', JournalEntryController::class);
    Route::resource('recurrentjournal', RecurrentJournalController::class);
    Route::resource('reversingjournal', ReversingJournalController::class);
    Route::resource('trialbalance', TrialBalanceController::class);
    Route::resource('ledgerreporting', LedgerReportController::class);

    Route::get('agingreportar/drilldown', [AgingReportARController::class, 'drilldown'])->name('ar.aging.drilldown');

    // Existing Payment Processing Sub-Routes
    Route::prefix('accounts-payable/payment-processing')->group(function () {
        Route::get('/', [PaymentProcessingController::class, 'index'])->name('ap_payment.index');
        Route::get('/create', [PaymentProcessingController::class, 'create'])->name('ap_payment.create');
        Route::get('/voucher/{voucherId}', [PaymentProcessingController::class, 'showVoucher'])->name('ap_payment.voucher');
    });

    Route::resource('taxruleconfig', TaxRuleController::class);
    Route::resource('efiling', TaxEfillingController::class);
    Route::resource('reconuploads', ReconUploadController::class);
    Route::resource('glpostingmap', GLMappingController::class);


    Route::prefix('finance/integrations')->name('integration.')->group(function () {
        Route::get('po-invoice-sync', [POInvoiceSyncController::class, 'index'])->name('po_invoice_sync.index');
        Route::get('po-invoice-sync/create', [POInvoiceSyncController::class, 'create'])->name('po_invoice_sync.create');
        Route::post('po-invoice-sync/store', [POInvoiceSyncController::class, 'store'])->name('po_invoice_sync.store');
    });

    Route::prefix('finance/integrations')->group(function () {
        Route::get('salary-journal-templates', [SalaryJournalTemplateController::class, 'index'])->name('salary-journal-templates.index');
        Route::get('salary-journal-templates/create', [SalaryJournalTemplateController::class, 'create'])->name('salary-journal-templates.create');
        Route::post('salary-journal-templates/store', [SalaryJournalTemplateController::class, 'store'])->name('salary-journal-templates.store');
    });

    // AJAX routes for dependent selects
    Route::get('/get-type-groups', [ChartOfAccountsController::class, 'getTypeGroups']);
    Route::get('/get-sub-account-types', [ChartOfAccountsController::class, 'getSubAccountTypes']);

    //Added Individual Routes
    Route::post('/segment-order/save', [COASegmentController::class, 'segmentOrder'])->name('segment-order.save');
    Route::post('/gl/save', [COASegmentController::class, 'segmentOrder'])->name('segment-order.gl.save');
    Route::post('/glDigits/save', [COASegmentController::class, 'editGlDigit'])->name('glDigits.save');
    Route::post('/glTypeSegmentValue/save', [COASegmentController::class, 'saveGLTypeSegment'])->name('glTypeSegmentValue.save');
    Route::post('/glAccountTypeSegmentValue/save', [COASegmentController::class, 'saveGLAccountTypeSegment'])->name('glAccountTypeSegmentValue.save');
    Route::post('/glSubAccountTypeSegmentValue/save', [COASegmentController::class, 'saveSubGLAccountTypeSegment'])->name('glSubAccountTypeSegmentValue.save');

    // Route for getting Order
    Route::get('/finance/pos/{selectedVendor}', [InvoiceEntryController::class, 'getOrders'])->name('finance.orders');
    // Route for getting GRNS
    Route::get('/finance/grns/{selectedPO}', [InvoiceEntryController::class, 'getGRNs'])->name('finance.grns');
    // Route for viewingPOModal
    Route::get('/finance/viewpo/{selectedPO}', [InvoiceEntryController::class, 'viewPOModal'])->name('finance.viewPOModal');
    Route::get('/finance/viewgrn/{grnId}', [InvoiceEntryController::class, 'viewGRNModal'])->name('finance.viewGRNModal');
    // Route for sAVING INVOICE
    Route::post('/finance/invoice/save', [InvoiceEntryController::class, 'saveInvoice'])->name('invoiceentry.save');
    //Route for gettng suppliers from invoices
    Route::get('/finance/supplier/{selectedInvoice}', [PaymentVoucherController::class, 'getSuppliers'])->name('finance.getSuppliers');
    //Route for getting Transaction Types
    Route::get('/finance/transactions/{selectedModule}', [GLMappingController::class, 'fetchTransactionTypes'])->name('glpostingmap.fetchTransactionTypes');
    //Route for getting GLAccounts
    Route::get('/glaccounts/list', [GLMappingController::class, 'list'])->name('glpostingmap.list');

    Route::post('/paymentvoucher/{id}/approve', [PaymentVoucherController::class, 'approve'])->name('paymentvoucher.approve');
    Route::post('/paymentvoucher/{id}/reject', [PaymentVoucherController::class, 'reject'])->name('paymentvoucher.reject');

    //Approval Routes For simulations
    //Route::patch('/journalentry/{id}/action', [FinanceJournalEntryController::class, 'action'])->name('journalentry.action');

    //////// Posting Routes ///////////
    Route::post('/journalApproval/{id}', [\App\Http\Controllers\Finance\PostingController::class, 'journalApproval'])->name('journalApproval');
    Route::post('/finance/ap/invoices/{id}/approve', [InvoiceEntryController::class, 'approve'])->name('ap.invoice.approve');
    Route::post('/finance/ap/invoices/{id}/reject', [InvoiceEntryController::class, 'reject'])->name('ap.invoice.reject');

    Route::post('/finance/cd/note/{id}/approve', [CreditNoteController::class, 'approve'])->name('cdnote.approve');
    Route::post('/finance/cd/note/{id}/reject', [CreditNoteController::class, 'reject'])->name('cdnote.reject');

    Route::post('/finance/voucher/{id}/post', [PaymentProcessingController::class, 'postVoucher'])->name('voucher.post');

    Route::post('/finance/ar/invoices/{id}/approve', [InvoiceGenerationController::class, 'approve'])->name('ar.invoice.approve');
    Route::post('/finance/ar/invoices/{id}/reject', [InvoiceGenerationController::class, 'reject'])->name('ar.invoice.reject');


    Route::withoutMiddleware(TrimStrings::class)->name('finance-')->group(function () {
        Route::get('reports/{report}/{format}', [ReportsController::class, 'export'])->name('reports.export');
        Route::resource('reports', ReportsController::class)->only(['index', 'show']);
    });
});

// Receipts Posting routes
Route::middleware(['module:1100000'])->prefix('finance')->group(function () {
    Route::resource('receiptsposting', ReceiptsPostingController::class);
    Route::post('receiptsposting/{id}/approve', [ReceiptsPostingController::class, 'approve'])->name('receiptsposting.approve');

    // AJAX endpoints for receipts posting
    Route::get('receiptsposting/api/customers', [ReceiptsPostingController::class, 'findCustomer'])->name('receiptsposting.api.customers');
    Route::post('receiptsposting/api/auto-allocate', [ReceiptsPostingController::class, 'autoAllocate'])->name('receiptsposting.api.auto-allocate');
    Route::get('receiptsposting/api/wallet-balance', [ReceiptsPostingController::class, 'getWalletBalance'])->name('receiptsposting.api.wallet-balance');
});

// Legacy AR endpoint for backward compatibility
Route::get('finance/ar/receiptsposting/api/customers', [ReceiptsPostingController::class, 'findCustomer']);

// Debug route removed


// Bank Routes

Route::middleware(['auth','module:1100000'])->prefix('finance')->name('finance.')->group(function () {

    // Banks => finance.bank.*
    Route::resource('bank', BankController::class)->names('bank');

    // Branches (menu-safe, no param) => finance.bankbranch.index
    Route::get('bank-branches', [BankBranchController::class, 'listAll'])
        ->name('bankbranch.index');

    // Branches filtered by bank => finance.bankbranch.bybank
    Route::get('bank/{bankId}/branches', [BankBranchController::class, 'index'])
        ->name('bankbranch.bybank');

    // Branch CRUD (no index here) => finance.bankbranch.*
    Route::resource('bankbranch', BankBranchController::class)
        ->except(['index'])
        ->names('bankbranch');

    // AJAX: Get cities for a selected country (used by Bank Branch create/edit)
    Route::get('bankbranch/cities', [BankBranchController::class, 'getCities'])
        ->name('bankbranch.cities');
});


Route::prefix('finance')->name('finance.')->middleware(['auth','module:1100000'])->group(function () {
    Route::resource('bankaccountsetup', BankAccountSetupController::class)
        ->names('bankaccountsetup'); // => finance.bankaccountsetup.*
});

Route::prefix('finance')->middleware(['auth','module:1100000'])->group(function () {
    Route::resource('cashbook', CashBookController::class); // cashbook.*
    Route::get('cashbook/create/receipt', [CashBookController::class, 'createReceipt'])->name('cashbook.create.receipt');
    Route::get('cashbook/create/payment', [CashBookController::class, 'createPayment'])->name('cashbook.create.payment');
    Route::post('cashbook/{id}/post', [CashBookController::class, 'post'])->name('cashbook.post');
    Route::post('cashbook/{id}/void', [CashBookController::class, 'void'])->name('cashbook.void');
    Route::get('cashbook/party/vendors', [CashBookController::class, 'partyVendors'])->name('cashbook.party.vendors');
    Route::get('cashbook/party/tenants', [CashBookController::class, 'partyTenants'])->name('cashbook.party.tenants');

    // NEW: mapping preview for auto-GL
    Route::get('cashbook/txntype/{id}/mapping', [CashBookController::class, 'txnTypeMapping'])
        ->name('cashbook.txntype.mapping');
});


Route::prefix('finance')->name('finance.')->middleware(['auth','module:1100000'])->group(function () {
    Route::resource('banktransfers', BankTransferController::class)->names([
        'index' => 'banktransfers.index',
        'create' => 'banktransfers.create',
        'store' => 'banktransfers.store',
        'show' => 'banktransfers.show',
        'edit' => 'banktransfers.edit',
        'update' => 'banktransfers.update',
        'destroy' => 'banktransfers.destroy',
    ]);

    Route::post('banktransfers/{id}/post', [BankTransferController::class, 'post'])->name('banktransfers.post');
    Route::post('banktransfers/{id}/void', [BankTransferController::class, 'void'])->name('banktransfers.void');
});


Route::prefix('finance')->name('finance.')->middleware('auth')->group(function () {
    Route::resource('banktransactions', BankTransactionController::class)->names([
        'index' => 'banktransactions.index',
        'create' => 'banktransactions.create',
        'store' => 'banktransactions.store',
        'show' => 'banktransactions.show',
        'edit' => 'banktransactions.edit',
        'update' => 'banktransactions.update',
        'destroy' => 'banktransactions.destroy',
    ]);

    Route::post('banktransactions/{id}/post', [BankTransactionController::class, 'post'])->name('banktransactions.post');
    Route::post('banktransactions/{id}/void', [BankTransactionController::class, 'void'])->name('banktransactions.void');
});


Route::prefix('finance')->name('finance.')->middleware('auth')->group(function () {
    // Helper to compute next start/end based on last book for a bank account
    Route::get('chequebooks/next-range/{bankAccountId}', [ChequeBookController::class, 'nextRange'])
        ->name('chequebooks.next-range');

    // Get available leaves for a cheque book
    Route::get('chequebooks/{id}/leaves', [ChequeBookController::class, 'getAvailableLeaves'])
        ->name('chequebooks.leaves');

    Route::resource('chequebooks', ChequeBookController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
        ->names([
            'index' => 'chequebooks.index',
            'create' => 'chequebooks.create',
            'store' => 'chequebooks.store',
            'show' => 'chequebooks.show',
            'edit' => 'chequebooks.edit',
            'update' => 'chequebooks.update',
            'destroy' => 'chequebooks.destroy',
        ])
        ->parameters(['chequebooks' => 'id']);

    // Cheques
    Route::get('cheques', [ChequeController::class, 'index'])->name('cheques.index');

    // Create
    Route::get('cheques/issued/create', [ChequeController::class, 'createIssued'])->name('cheques.issued.create');
    Route::post('cheques/issued', [ChequeController::class, 'storeIssued'])->name('cheques.issued.store');

    Route::get('cheques/received/create', [ChequeController::class, 'createReceived'])->name('cheques.received.create');
    Route::post('cheques/received', [ChequeController::class, 'storeReceived'])->name('cheques.received.store');

    // Show
    Route::get('cheques/{id}', [ChequeController::class, 'show'])->name('cheques.show');

    // Actions
    Route::post('cheques/{id}/deposit', [ChequeController::class, 'deposit'])->name('cheques.deposit'); // RECEIVED only
    Route::post('cheques/{id}/clear', [ChequeController::class, 'clear'])->name('cheques.clear');     // both
    Route::post('cheques/{id}/bounce', [ChequeController::class, 'bounce'])->name('cheques.bounce');   // both
    Route::post('cheques/{id}/cancel', [ChequeController::class, 'cancel'])->name('cheques.cancel');   // Draft/Issued/OnHand
    Route::post('cheques/{id}/issue', [ChequeController::class, 'issueCheque'])->name('cheques.issue');  // Draft -> Issued/Rejected

    // Optional: spoil a specific unused leaf (mark as not usable)
    Route::post('chequebooks/{book}/leaves/{leaf}/spoil', function ($book, $leaf) {
        $l = \App\Models\Finance\ChequeLeaf::where('ChequeBookID', $book)->findOrFail($leaf);
        if ($l->Status !== 'Unused') {
            return back()->with('error', 'Only Unused leaves can be spoiled.');
        }
        $l->Status = 'Spoiled';
        $l->Notes = 'Manually spoiled';
        $l->save();

        return back()->with('success', 'Leaf spoiled.');
    })->name('chequebooks.leaves.spoil');
});


Route::prefix('finance')->name('finance.')->middleware('auth')->group(function () {
    // Setup
    Route::resource('pettyfloats', PettyCashFloatController::class)->names([
        'index' => 'pettyfloats.index',
        'create' => 'pettyfloats.create',
        'store' => 'pettyfloats.store',
        'edit' => 'pettyfloats.edit',
        'update' => 'pettyfloats.update',
        'destroy' => 'pettyfloats.destroy',
    ])->except(['show']);

    // Vouchers
    Route::get('pettycash', [PettyCashController::class, 'index'])->name('pettycash.index');
    Route::get('pettycash/disbursement/create', [PettyCashController::class, 'createDisbursement'])->name('pettycash.disbursement.create');
    Route::get('pettycash/replenishment/create', [PettyCashController::class, 'createReplenishment'])->name('pettycash.replenishment.create');
    Route::get('pettycash/refund/create', [PettyCashController::class, 'createRefund'])->name('pettycash.refund.create');
    Route::post('pettycash', [PettyCashController::class, 'store'])->name('pettycash.store');

    Route::get('pettycash/{id}', [PettyCashController::class, 'show'])->name('pettycash.show');
    Route::post('pettycash/{id}/post', [PettyCashController::class, 'post'])->name('pettycash.post');
    Route::post('pettycash/{id}/void', [PettyCashController::class, 'void'])->name('pettycash.void');
    Route::delete('pettycash/{id}', [PettyCashController::class, 'destroy'])->name('pettycash.destroy');

    Route::post('pettycash/{id}/submit', [PettyCashController::class, 'submitForApproval'])->name('pettycash.submit');
    Route::post('pettycash/{id}/approve', [PettyCashController::class, 'approve'])->name('pettycash.approve');
    Route::post('pettycash/{id}/reject', [PettyCashController::class, 'reject'])->name('pettycash.reject');

    // Replenishment wizard
    Route::get('pettycash/replenishment/wizard', [PettyCashController::class, 'wizard'])->name('pettycash.wizard');
    Route::get('pettycash/replenishment/wizard/preview', [PettyCashController::class, 'wizardPreview'])->name('pettycash.wizard.preview');
    Route::post('pettycash/replenishment/wizard', [PettyCashController::class, 'wizardStore'])->name('pettycash.wizard.store');
});
