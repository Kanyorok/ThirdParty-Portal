<?php

use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\Inventory\ReceiptController;
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

Route::get('/itemmaster', [ItemMasterListController::class, 'index'])->name('itemmaster.index');

Route::namespace('Inventory')->prefix('inventory')->group(function () {
    Route::resource('receipts', ReceiptController::class);
    Route::resource('transactionsreceipts', TransactionReceiptsController::class);
    Route::resource('transactionstransfers', TransactionTransfersController::class);
    Route::resource('transactionsadjustment', TransactionAdjustmentController::class);
    //Route::resource('itemmaster', ItemMasterController::class);
    Route::get('/itemmasterlist/create', [ItemMasterListController::class, 'create'])->name('itemmasterlist.create');
    Route::post('/itemmasterlist', [ItemMasterListController::class, 'store'])->name('itemmasterlist.store');
    Route::get('/itemmasterlist/{Id}', [ItemMasterListController::class, 'show'])->name('itemmasterlist.show');
    Route::get('/itemmasterlist/{Id}/edit', [ItemMasterListController::class, 'edit'])->name('itemmasterlist.edit');
    Route::put('/itemmasterlist/{Id}', [ItemMasterListController::class, 'update'])->name('itemmasterlist.update');
    Route::delete('/itemmasterlist/{Id}', [ItemMasterListController::class, 'destroy'])->name('itemmasterlist.destroy');
    Route::get('/get-subcategories', [ItemMasterListController::class, 'getSubcategories'])->name('get.subcategories');
    Route::resource('sku', SKUController::class);


    //Route::resource('itemcategory', ItemCategoryController::class);
    Route::get('/itemcategory', [ItemCategoryController::class, 'index'])->name('itemcategory.index');
    Route::get('/itemcategory/create', [ItemCategoryController::class, 'create'])->name('itemcategory.create');
    Route::post('/itemcategory', [ItemCategoryController::class, 'store'])->name('itemcategory.store');
    Route::get('/itemcategory/{id}', [ItemCategoryController::class, 'show'])->name('itemcategory.show');
    Route::get('/itemcategory/edit/{id}', [ItemCategoryController::class, 'edit'])->name('itemcategory.edit');
    Route::put('/itemcategory/{id}', [ItemCategoryController::class, 'update'])->name('itemcategory.update');
    Route::delete('/itemcategory/{id}', [ItemCategoryController::class, 'destroy'])->name('itemcategory.destroy');
    Route::resource('sku', SKUController::class);

    //Route::resource('itemsubcategory', ItemSubCategoryController::class);
    Route::get('/itemsubcategory', [ItemSubCategoryController::class, 'index'])->name('itemsubcategory.index');
    Route::get('/itemsubcategory/create', [ItemSubCategoryController::class, 'create'])->name('itemsubcategory.create');
    Route::post('/itemsubcategory', [ItemSubCategoryController::class, 'store'])->name('itemsubcategory.store');
    Route::get('/itemsubcategory/{Id}', [ItemSubCategoryController::class, 'show'])->name('itemsubcategory.show');
    Route::get('/itemsubcategory/{Id}/edit', [ItemSubCategoryController::class, 'edit'])->name('itemsubcategory.edit');
    Route::put('/itemsubcategory/{Id}', [ItemSubCategoryController::class, 'update'])->name('itemsubcategory.update');
    Route::delete('/itemsubcategory/{Id}', [ItemSubCategoryController::class, 'destroy'])->name('itemsubcategory.destroy');
    Route::resource('sku', SKUController::class);

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
