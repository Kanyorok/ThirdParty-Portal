<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\JournalBatchController;
use App\Http\Controllers\Finance\LedgerAccountsController;
use App\Http\Controllers\Finance\TransactionTypesController;
use App\Http\Controllers\Finance\BankReconciliationController;
use App\Http\Controllers\Finance\PeriodManagementController;

Route::namespace('Finance')->group(function () {
    Route::resource('journalbatch', JournalBatchController::class);
    Route::resource('ledgeraccounts', LedgerAccountsController::class);
    Route::resource('transactiontypes', TransactionTypesController::class);
    Route::resource('bankreconciliation', BankReconciliationController::class);
    Route::resource('periodmanagement', PeriodManagementController::class);
});

