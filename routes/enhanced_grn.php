<?php

use App\Http\Controllers\Procurement\EnhancedGoodsReceiptController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Enhanced Goods Receipt Note (GRN) Routes
|--------------------------------------------------------------------------
|
| These routes handle the comprehensive GRN system with PO matching,
| inventory integration, finance integration, and item type processing
|
*/

Route::prefix('goods-receipt')->name('goods-receipt.')->group(function () {
    // Main GRN management
    Route::get('/', [EnhancedGoodsReceiptController::class, 'index'])->name('index');
    Route::get('/dashboard', [EnhancedGoodsReceiptController::class, 'dashboard'])->name('dashboard');
    Route::get('/create', [EnhancedGoodsReceiptController::class, 'create'])->name('create');
    Route::post('/', [EnhancedGoodsReceiptController::class, 'store'])->name('store');

    // GRN details and processing
    Route::get('/{grnId}/{poId}', [EnhancedGoodsReceiptController::class, 'show'])->name('show');
    Route::post('/process/{grnId}/{poId}', [EnhancedGoodsReceiptController::class, 'processGRN'])->name('process');

    // AJAX endpoints
    Route::get('/api/po-details/{poId}', [EnhancedGoodsReceiptController::class, 'getPODetails'])->name('po-details');
    Route::get('/api/line-details/{lineId}', [EnhancedGoodsReceiptController::class, 'getLineDetails'])->name('api.line-details');
    Route::post('/api/retry-processing/{lineId}', [EnhancedGoodsReceiptController::class, 'retryProcessing'])->name('api.retry-processing');
    Route::post('/api/quality-check/{grnLineId}', [EnhancedGoodsReceiptController::class, 'updateQualityStatus'])->name('quality-check');

    // Reports and summaries
    Route::get('/summary/{grnId}/{poId}', [EnhancedGoodsReceiptController::class, 'showSummary'])->name('summary');
    Route::get('/reports/processing-status', [EnhancedGoodsReceiptController::class, 'processingStatusReport'])->name('reports.processing-status');
    Route::get('/reports/item-type-analysis', [EnhancedGoodsReceiptController::class, 'itemTypeAnalysisReport'])->name('reports.item-type-analysis');
});

// Legacy GRN routes (for backward compatibility)
Route::prefix('goodreceipts')->name('procurementreceipts.')->group(function () {
    Route::redirect('/', '/procurement/goods-receipt');
    Route::redirect('/create', '/procurement/goods-receipt/create');
});
