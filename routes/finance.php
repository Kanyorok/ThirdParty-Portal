<?php

use App\Http\Controllers\Finance\AgingReportARController;
use App\Http\Controllers\Finance\AgingReportController;
use App\Http\Controllers\Finance\BalanceSheetController;
use App\Http\Controllers\Finance\BankAccountSetupController;
use App\Http\Controllers\Finance\BankReconciliationController;
use App\Http\Controllers\Finance\CashBookController;
use App\Http\Controllers\Finance\CashFlowStatementController;
use App\Http\Controllers\Finance\CashManagementController;
use App\Http\Controllers\Finance\ChartOfAccountsController;
use App\Http\Controllers\Finance\ChequeManagementController;
use App\Http\Controllers\Finance\COASegmentController;
use App\Http\Controllers\Finance\ConsolidationReportsController;
use App\Http\Controllers\Finance\CreditManagementController;
use App\Http\Controllers\Finance\CreditNoteController;
use App\Http\Controllers\Finance\CustomerMasterController;
use App\Http\Controllers\Finance\CustomerStatementController;
use App\Http\Controllers\Finance\GLDynamicController;
use App\Http\Controllers\Finance\GLMappingController;
use App\Http\Controllers\Finance\HierarchyViewerController;
use App\Http\Controllers\Finance\IncomeStatementController;
use App\Http\Controllers\Finance\InvoiceApprovalController;
use App\Http\Controllers\Finance\InvoiceEntryController;
use App\Http\Controllers\Finance\InvoiceGenerationController;
use App\Http\Controllers\Finance\JournalBatchController;
use App\Http\Controllers\Finance\JournalEntryController;
use App\Http\Controllers\Finance\LedgerAccountsController;
use App\Http\Controllers\Finance\LedgerReportController;
use App\Http\Controllers\Finance\PaymentAndReceiptsController;
use App\Http\Controllers\Finance\PaymentProcessingController;
use App\Http\Controllers\Finance\PaymentVoucherController;
use App\Http\Controllers\Finance\PeriodManagementController;
use App\Http\Controllers\Finance\POInvoiceSyncController;
use App\Http\Controllers\Finance\ReceiptsPostingController;
use App\Http\Controllers\Finance\ReconDashboardController;
use App\Http\Controllers\Finance\ReconUploadController;
use App\Http\Controllers\Finance\RecurrentJournalController;
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
use Illuminate\Support\Facades\Route;

// Newly added

Route::namespace('Finance')->prefix('finance')->group(function () {
    Route::resource('journalbatch', JournalBatchController::class);
    Route::resource('ledgeraccounts', LedgerAccountsController::class);
    Route::resource('transactiontypes', TransactionTypesController::class);
    Route::resource('bankreconciliation', BankReconciliationController::class);
    Route::resource('periodmanagement', PeriodManagementController::class);
    Route::resource('vendormaster', VendorMasterController::class);
    Route::resource('invoiceentry', InvoiceEntryController::class);
    Route::resource('customermaster', CustomerMasterController::class);
    Route::resource('invoicegeneration', InvoiceGenerationController::class);
    Route::resource('creditnote', CreditNoteController::class);
    Route::resource('paymentprocessing', PaymentProcessingController::class);
    Route::resource('agingreport', AgingReportController::class);
    Route::resource('receiptsposting', ReceiptsPostingController::class);
    Route::resource('creditmanagement', CreditManagementController::class);
    Route::resource('agingreportar', AgingReportARController::class);
    Route::resource('customerstatement', CustomerStatementController::class);
    Route::resource('paymentvoucher', PaymentVoucherController::class);
    Route::resource('bankaccountsetup', BankAccountSetupController::class);
    Route::resource('cashbook', CashBookController::class);
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
    Route::resource('salaryjournaltemplate', SalaryJournalTemplateController::class);
    Route::resource('taxefilling', TaxEfillingController::class);
    Route::resource('taxglmapping', TaxGLMappingController::class);
    Route::resource('taxjurisdiction', TaxJurisdictionController::class);
    Route::resource('taxreturngenerator', TaxReturnGeneratorController::class);
    Route::resource('taxrule', TaxRuleController::class);
    Route::resource('taxsummaryreport', TaxSummaryReportController::class);
    Route::resource('trialbalance', TrialBalanceController::class);

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
    Route::post('/gl/save', [COASegmentController::class, 'segmentOrder'])->name('segment-order.save');
    Route::post('/glDigits/save', [COASegmentController::class, 'editGlDigit'])->name('glDigits.save');
    Route::post('/glTypeSegmentValue/save', [COASegmentController::class, 'editGlDigit'])->name('glTypeSegmentValue.save');

});
