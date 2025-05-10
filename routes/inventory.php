<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inventory\ReceiptController;
use App\Http\Controllers\Inventory\TransactionReceiptsController;
use App\Http\Controllers\Inventory\TransactionTransfersController;
use App\Http\Controllers\Inventory\TransactionAdjustmentController;
use App\Http\Controllers\Inventory\ItemMasterController;
use App\Http\Controllers\Inventory\ItemMasterListController;
use App\Http\Controllers\Inventory\SKUController;
use App\Http\Controllers\Inventory\ItemCategoryController;
use App\Http\Controllers\Inventory\ItemSubCategoryController;
use App\Http\Controllers\Inventory\InventoryDashboardController;
use App\Http\Controllers\Inventory\MovementDashboardController;
use App\Http\Controllers\Inventory\StockTakeController;
use App\Http\Controllers\Inventory\OpeningStockController;
use App\Http\Controllers\Inventory\UOMConversionController;
use App\Http\Controllers\Inventory\BinTrackingController;
use App\Http\Controllers\Inventory\StockValuationHistoryController;
use App\Http\Controllers\Inventory\InterBranchRequisitionController;
use App\Http\Controllers\Inventory\ReportsController; 
use App\Http\Controllers\Inventory\InterBranchRequisitionApprovalController;
use App\Http\Controllers\Inventory\PropertyReceiptPrintController;



Route::namespace('Inventory')->group(function () {
    Route::resource('receipts', ReceiptController::class);
    Route::resource('transactionsreceipts', TransactionReceiptsController::class);
    Route::resource('transactionstransfers', TransactionTransfersController::class);
    Route::resource('transactionsadjustment', TransactionAdjustmentController::class);
    //Route::resource('itemmaster', ItemMasterController::class);
    Route::get('/itemmaster', [ItemMasterListController::class, 'index'])->name('itemmaster.index');
    Route::get('/itemmaster/create', [ItemMasterListController::class, 'create'])->name('itemmaster.create');
    Route::resource('sku', SKUController::class);
    
    //Route::resource('itemmasterlist', ItemMasterListController::class);
    Route::get('/itemmasterlist', [ItemMasterListController::class, 'index'])->name('itemmasterlist.index');
    Route::get('/itemmasterlist/create', [ItemMasterListController::class, 'create'])->name('itemmasterlist.create');
    
    Route::resource('itemcategory', ItemCategoryController::class);
    Route::resource('itemsubcategory', ItemSubCategoryController::class);
    Route::resource('inventorydashboard', InventoryDashboardController::class);
    Route::resource('movementdashboard', MovementDashboardController::class);
    Route::resource('stocktake', StockTakeController::class);
    Route::resource('openingstock', OpeningStockController::class);
    Route::resource('uomconversion', UOMConversionController::class);
    Route::resource('bintracking', BinTrackingController::class);
    Route::resource('stockvaluationhistory', StockValuationHistoryController::class);
    Route::resource('expirytracking', ExpiryBatchTrackingController::class);
    Route::resource('interbranchrequisition', InterBranchRequisitionController::class);
    Route::resource('interbranchrequisitionapproval', InterBranchRequisitionApprovalController::class);
    Route::resource('inventoryreports', ReportsController::class);
    Route::resource('rentdashboard', RentDashboardController::class);
    Route::resource('receiptprint', PropertyReceiptPrintController::class);
});