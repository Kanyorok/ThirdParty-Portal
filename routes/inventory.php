<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inventory\ReceiptController;
use App\Http\Controllers\Inventory\TransactionReceiptsController;

Route::namespace('Inventory')->group(function () {
    Route::resource('receipts', ReceiptController::class);
    Route::resource('transactions', TransactionReceiptsController::class);
});