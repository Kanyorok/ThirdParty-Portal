<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\JournalBatchController;
use App\Http\Controllers\Finance\LedgerAccountsController;
use App\Http\Controllers\Finance\TransactionTypesController;
use App\Http\Controllers\Finance\BankReconciliationController;
use App\Http\Controllers\Finance\PeriodManagementController;
use App\Http\Controllers\Finance\VendorMasterController;
use App\Http\Controllers\Finance\InvoiceEntryController;
use App\Http\Controllers\Finance\CustomerMasterController;
use App\Http\Controllers\Finance\InvoiceGenerationController;
use App\Http\Controllers\Finance\CreditNoteController;
use App\Http\Controllers\Finance\PaymentProcessingController;
use App\Http\Controllers\Finance\AgingReportController;
use App\Http\Controllers\Finance\ReceiptsPostingController;
use App\Http\Controllers\Finance\CreditManagementController;
use App\Http\Controllers\Finance\AgingReportARController;
use App\Http\Controllers\Finance\CustomerStatementController;
use App\Http\Controllers\Finance\PaymentVoucherController;
use App\Http\Controllers\Finance\BankAccountSetupController;
use App\Http\Controllers\Finance\CashBookController;
use App\Http\Controllers\Finance\CashManagementController;
use App\Http\Controllers\Finance\ChequeManagementController;
use App\Http\Controllers\Finance\PaymentAndReceiptsController;
use App\Http\Controllers\Finance\BalanceSheetController;
use App\Http\Controllers\Finance\ConsolidationReportsController;
use App\Http\Controllers\Finance\IncomeStatementController;


Route::namespace('Finance')->group(function () {
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

});

