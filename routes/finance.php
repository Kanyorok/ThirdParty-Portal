<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\JournalBatchController;
use App\Http\Controllers\Finance\LedgerAccountsController;
use App\Http\Controllers\Finance\TransactionTypesController;
use App\Http\Controllers\Finance\BankReconciliationController;
use App\Http\Controllers\Finance\PeriodManagementController;
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

