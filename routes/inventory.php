<?php

use App\Http\Controllers\Inventory\BinTrackingController;
use App\Http\Controllers\Inventory\ExpiryBatchTrackingController;
use App\Http\Controllers\Inventory\InterBranchRequisitionApprovalController;
use App\Http\Controllers\Inventory\InterBranchRequisitionController;
use App\Http\Controllers\Inventory\InventoryHoldReviewController;
use App\Http\Controllers\Inventory\InventoryTypeController;
use App\Http\Controllers\Inventory\ItemCategoryController;
use App\Http\Controllers\Inventory\ItemMasterListController;
use App\Http\Controllers\Inventory\ItemSubCategoryController;
use App\Http\Controllers\Inventory\ItemTypeController;
use App\Http\Controllers\Inventory\OpeningStockController;
use App\Http\Controllers\Inventory\PriceManagementController;
use App\Http\Controllers\Inventory\ReportsController;
use App\Http\Controllers\Inventory\SKUController;
use App\Http\Controllers\Inventory\StockIssueController;
use App\Http\Controllers\Inventory\StockTakeController;
use App\Http\Controllers\Inventory\StockConsumptionController;
use App\Http\Controllers\Inventory\StockValuationHistoryController;
use App\Http\Controllers\Inventory\StoreController;
use App\Http\Controllers\Inventory\TransactionAdjustmentController;
use App\Http\Controllers\Inventory\TransactionApprovalController;
use App\Http\Controllers\Inventory\TransactionReceiptsController;
use App\Http\Controllers\Inventory\TransactionTransfersController;
use App\Http\Controllers\Inventory\UOMController;
use App\Http\Controllers\Inventory\UOMConversionController;
use App\Http\Controllers\Inventory\StockMovementController;

use Illuminate\Support\Facades\Route;


//use App\Http\Controllers\Inventory\ReceiptController;

Route::namespace('Inventory')->prefix('inventory')->group(function () {
    // Route::resource('receipts', ReceiptController::class);

    Route::get('/itemmaster', [ItemMasterListController::class, 'index'])->name('itemmaster.index');
    //Route::resource('itemmaster', ItemMasterController::class);
    Route::get('/itemmaster', [ItemMasterListController::class, 'index'])->name('itemmaster.index');
    Route::get('/itemmasterlist/create', [ItemMasterListController::class, 'create'])->name('itemmasterlist.create');
    Route::post('/itemmasterlist', [ItemMasterListController::class, 'store'])->name('itemmasterlist.store');
    Route::get('/itemmasterlist/{Id}', [ItemMasterListController::class, 'show'])->name('itemmasterlist.show');
    Route::get('/itemmasterlist/{Id}/edit', [ItemMasterListController::class, 'edit'])->name('itemmasterlist.edit');
    Route::put('/itemmasterlist/{Id}', [ItemMasterListController::class, 'update'])->name('itemmasterlist.update');
    Route::delete('/itemmasterlist/{Id}', [ItemMasterListController::class, 'destroy'])->name('itemmasterlist.destroy');
    Route::get('/get-subcategory', [ItemMasterListController::class, 'getSubcategories'])->name('get.subcategories');



    Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
    Route::get('/stores/create', [StoreController::class, 'create'])->name('stores.create');
    Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');
    Route::get('/stores/{Id}', [StoreController::class, 'show'])->name('stores.show');
    Route::get('/stores/{Id}/edit', [StoreController::class, 'edit'])->name('stores.edit');
    Route::put('/stores/{Id}', [StoreController::class, 'update'])->name('stores.update');
    Route::delete('/stores/{Id}', [StoreController::class, 'destroy'])->name('stores.destroy');

    //Route::resource('sku', SKUController::class);
    Route::get('/sku', [SKUController::class, 'index'])->name('sku.index');
    Route::get('/sku/create', [SKUController::class, 'create'])->name('sku.create');
    Route::post('/sku', [SKUController::class, 'store'])->name('sku.store');
    Route::get('/sku/{Id}', [SKUController::class, 'show'])->name('sku.show');
    Route::get('/sku/{Id}/edit', [SKUController::class, 'edit'])->name('sku.edit');
    Route::put('/sku/{Id}', [SKUController::class, 'update'])->name('sku.update');
    Route::delete('/sku/{Id}', [SKUController::class, 'destroy'])->name('sku.destroy');
    Route::get('/get-stores', [SKUController::class, 'getStores'])->name('get.stores');
    Route::get('/info/get-items', [SKUController::class, 'getItemsByCategoryOrSubcategory'])->name('get.items');
    Route::get('get-item-details', [SKUController::class, 'getItemDetails'])->name('sku.item-details');



    //Route::resource('itemcategory', ItemCategoryController::class);
    Route::get('/itemcategory', [ItemCategoryController::class, 'index'])->name('itemcategory.index');
    Route::get('/itemcategory/create', [ItemCategoryController::class, 'create'])->name('itemcategory.create');
    Route::post('/itemcategory', [ItemCategoryController::class, 'store'])->name('itemcategory.store');
    Route::get('/itemcategory/{id}', [ItemCategoryController::class, 'show'])->name('itemcategory.show');
    Route::get('/itemcategory/edit/{id}', [ItemCategoryController::class, 'edit'])->name('itemcategory.edit');
    Route::put('/itemcategory/{id}', [ItemCategoryController::class, 'update'])->name('itemcategory.update');
    Route::delete('/itemcategory/{id}', [ItemCategoryController::class, 'destroy'])->name('itemcategory.destroy');

    //Route::resource('itemsubcategory', ItemSubCategoryController::class);
    Route::get('/itemsubcategory', [ItemSubCategoryController::class, 'index'])->name('itemsubcategory.index');
    Route::get('/itemsubcategory/create', [ItemSubCategoryController::class, 'create'])->name('itemsubcategory.create');
    Route::post('/itemsubcategory', [ItemSubCategoryController::class, 'store'])->name('itemsubcategory.store');
    Route::get('/itemsubcategory/{id}', [ItemSubCategoryController::class, 'show'])->name('itemsubcategory.show');
    Route::get('/itemsubcategory/{Id}/edit', [ItemSubCategoryController::class, 'edit'])->name('itemsubcategory.edit');
    Route::put('/itemsubcategory/{Id}', [ItemSubCategoryController::class, 'update'])->name('itemsubcategory.update');
    Route::delete('/itemsubcategory/{Id}', [ItemSubCategoryController::class, 'destroy'])->name('itemsubcategory.destroy');


    //Route::resource('openingstock', OpeningStockController::class);
    Route::get('/openingstock/index', [OpeningStockController::class, 'index'])->name('openingstock.index');
    Route::get('/openingstock/create', [OpeningStockController::class, 'create'])->name('openingstock.create');
    Route::get('/downloads/opening-stock-sample', [OpeningStockController::class, 'downloadSampleTemplate'])->name('openingstock.sample');
    Route::post('/openingstock/upload', [OpeningStockController::class, 'uploadExcel'])->name('openingstock.upload');


    //Route::resource('bintracking', BinTrackingController::class);
    Route::get('/inventorytracking', [BinTrackingController::class, 'index'])->name('bintracking.index');
    Route::get('/inventorytracking/create', [BinTrackingController::class, 'create'])->name('bintracking.create');


    Route::resource('inventorydashboard', InventoryDashboardController::class);
    Route::resource('movementdashboard', StockMovementController::class);

    //Route::resource('stocktake', StockTakeController::class);
    Route::get('/stocktake/index', [StockTakeController::class, 'index'])->name('stocktake.index');
    Route::get('/stocktake', [StockTakeController::class, 'create'])->name('stocktake.create');
    Route::post('/stocktake/store', [StockTakeController::class, 'store'])->name('stocktake.store');
    Route::post('/stocktake/storeline', [StockTakeController::class, 'storeline'])->name('stocktake.storeline');
    Route::get('/stocktake/show/{Id}', [StockTakeController::class, 'show'])->name('stocktake.show');
    Route::delete('stocktake/delete/{Id}', [StockTakeController::class, 'destroy'])->name('stocktake.destroy');
    Route::get('stocktake/edit/{Id}', [StockTakeController::class, 'edit'])->name('stocktake.edit');
    Route::put('stocktake/update/{Id}', [StockTakeController::class, 'update'])->name('stocktake.update');
    Route::get('/stocktake/branches/{storeId}', [StockTakeController::class, 'getStoreByBranch'])->name('getstores');
    Route::get('/stock-items/{branchId}/{storeId}', [StockTakeController::class, 'getStockItems'])->name('stocktake.items');

    //Route::resource('stockconsumption', StockConsumptionController::class);
    Route::get('/stockconsumption', [StockConsumptionController::class, 'index'])->name('stockconsumption.index');
    Route::get('/stockconsumption/create', [StockConsumptionController::class, 'create'])->name('stockconsumption.create');
    Route::get('/stockconsumption/get-issued-to-options', [StockConsumptionController::class, 'getIssuedToOptions'])->name('stockconsumption.getIssuedToOptions');
    Route::get('/stockconsumption/get-stores', [StockConsumptionController::class, 'getStores'])->name('stockconsumption.getStores');
    Route::get('/stockconsumption/get-items', [StockConsumptionController::class, 'getItems'])->name('stockconsumption.getItems');
    Route::get('/stockconsumption/get-uom', [StockConsumptionController::class, 'getUOM'])->name('stockconsumption.getUOM');

    Route::post('/stockconsumption', [StockConsumptionController::class, 'store'])->name('stockconsumption.store');
    Route::get('/stockconsumption/{Id}', [StockConsumptionController::class, 'show'])->name('stockconsumption.show');
    Route::get('/stockconsumption/{Id}/edit', [StockConsumptionController::class, 'edit'])->name('stockconsumption.edit');
    Route::put('/stockconsumption/{Id}', [StockConsumptionController::class, 'update'])->name('stockconsumption.update');
    Route::delete('/stockconsumption/{Id}', [StockConsumptionController::class, 'destroy'])->name('stockconsumption.destroy');

    //Route::resource('uomconversion', UOMConversionController::class);
    Route::get('/uomconversion', [UOMConversionController::class, 'index'])->name('uomconversion.index');
    Route::get('/uomconversion/create', [UOMConversionController::class, 'create'])->name('uomconversion.create');
    Route::post('/uomconversion', [UOMConversionController::class, 'store'])->name('uomconversion.store');
    Route::get('/uomconversion/{Id}', [UOMConversionController::class, 'show'])->name('uomconversion.show');
    Route::get('/uomconversion/{Id}/edit', [UOMConversionController::class, 'edit'])->name('uomconversion.edit');
    Route::put('/uomconversion/{Id}', [UOMConversionController::class, 'update'])->name('uomconversion.update');
    Route::delete('/uomconversion/{Id}', [UOMConversionController::class, 'destroy'])->name('uomconversion.destroy');

    Route::resource('stockvaluationhistory', StockValuationHistoryController::class);
    Route::resource('expirytracking', ExpiryBatchTrackingController::class);
    //Route::resource('interbranchrequisition', InterBranchRequisitionController::class);
    Route::get('/interbranchrequisition', [InterBranchRequisitionController::class, 'index'])->name('interbranchrequisition.index');
    Route::get('/interbranchrequisition/create', [InterBranchRequisitionController::class, 'create'])->name('interbranchrequisition.create');
    Route::post('/interbranchrequisition', [InterBranchRequisitionController::class, 'store'])->name('interbranchrequisition.store');
    Route::get('/interbranchrequisition/{Id}', [InterBranchRequisitionController::class, 'show'])->name('interbranchrequisition.show');
    Route::get('/interbranchrequisition/{Id}/edit', [InterBranchRequisitionController::class, 'edit'])->name('interbranchrequisition.edit');
    Route::put('/interbranchrequisition/{Id}', [InterBranchRequisitionController::class, 'update'])->name('interbranchrequisition.update');
    Route::delete('/interbranchrequisition/{Id}', [InterBranchRequisitionController::class, 'destroy'])->name('interbranchrequisition.destroy');
    Route::get('/interbranchrequisition/get-items', [InterBranchRequisitionController::class, 'getItemsByCategoryOrSubcategory'])->name('interbranchrequisition.getItemsByCategoryOrSubcategory');
    Route::get('/items/code/{Id}', [InterBranchRequisitionController::class, 'getItemCode'])->name('inventory.items.code');


    Route::get('/get-categories-by-branch', [InterBranchRequisitionController::class, 'getCategoriesByBranch'])->name('inventory.get-categories-by-branch');
    Route::get('/get-subcategories-by-branch-and-category', [InterBranchRequisitionController::class, 'getSubcategoriesByBranchAndCategory'])->name('inventory.get-subcategories-by-branch-and-category');
    Route::get('/get-items', [InterBranchRequisitionController::class, 'getItemsByBranchAndCategoryOrSubcategory'])->name('inventory.get-items');

    Route::get('/get-subcategories', [InterBranchRequisitionController::class, 'getSubcategories'])->name('inventory.getSubcategories');

    //Route::resource('transactionstransfers', TransactionTransfersController::class);
    Route::get('/transactionstransfers', [TransactionTransfersController::class, 'index'])->name('transactionstransfers.index');
    Route::get('/transactionstransfers/create', [TransactionTransfersController::class, 'create'])->name('transactionstransfers.create');
    Route::post('/transactionstransfers', [TransactionTransfersController::class, 'store'])->name('transactionstransfers.store');
    Route::get('/transactionstransfers/{Id}', [TransactionTransfersController::class, 'show'])->name('transactionstransfers.show');
    Route::get('/transactionstransfers/{Id}/edit', [TransactionTransfersController::class, 'edit'])->name('transactionstransfers.edit');
    Route::put('/transactionstransfers/{Id}', [TransactionTransfersController::class, 'update'])->name('transactionstransfers.update');
    Route::delete('/transactionstransfers/{Id}', [TransactionTransfersController::class, 'destroy'])->name('transactionstransfers.destroy');
    Route::get('/transactionstransfers/requisitions/by-type/{type}', [TransactionTransfersController::class, 'getRequisitionsByType'])->name('requisitions.by-type');
    Route::get('/transactionstransfers/requisitions/details/{id}', [TransactionTransfersController::class, 'getRequisitionDetails'])
        ->name('requisitions.details');


    //Route::resource('transactionsreceipts', TransactionReceiptsController::class);
    Route::get('/transactionsreceipts', [TransactionReceiptsController::class, 'index'])->name('transactionsreceipts.index');
    Route::get('/transactionsreceipts/create', [TransactionReceiptsController::class, 'create'])->name('transactionsreceipts.create');
    Route::post('/transactionsreceipts', [TransactionReceiptsController::class, 'store'])->name('transactionsreceipts.store');
    Route::get('/transactionsreceipts/{Id}', [TransactionReceiptsController::class, 'show'])->name('transactionsreceipts.show');
    Route::get('/transactionsreceipts/{Id}/edit', [TransactionReceiptsController::class, 'edit'])->name('transactionsreceipts.edit');
    Route::put('/transactionsreceipts/{Id}', [TransactionReceiptsController::class, 'update'])->name('transactionsreceipts.update');
    Route::delete('/transactionsreceipts/{Id}', [TransactionReceiptsController::class, 'destroy'])->name('transactionsreceipts.destroy');
    Route::get('/transactionsreceipts/transfer-items/{Id}', [TransactionReceiptsController::class, 'getTransferItems']);

    Route::resource('stockissue', StockIssueController::class);

    //Route::resource('transactionsapproval', TransactionApprovalController::class);
    Route::get('/transactionsapproval', [TransactionApprovalController::class, 'index'])->name('transactionsapproval.index');
    Route::post('/transactionsapproval/approve/{Id}', [TransactionApprovalController::class, 'approve'])->name('transactionsapproval.approve');
    Route::post('/transactionsapproval/reject/{Id}', [TransactionApprovalController::class, 'reject'])->name('transactionsapproval.reject');
    Route::get('/transactionsapproval/{Id}', [TransactionApprovalController::class, 'show'])->name('transactionsapproval.show');

    //Route::resource('transactionsadjustment', TransactionAdjustmentController::class);
    Route::get('/transactionsadjustment', [TransactionAdjustmentController::class, 'index'])->name('transactionsadjustment.index');
    Route::get('/transactionsadjustment/create', [TransactionAdjustmentController::class, 'create'])->name('transactionsadjustment.create');
    Route::post('/transactionsadjustment', [TransactionAdjustmentController::class, 'store'])->name('transactionsadjustment.store');

    Route::get('/transactionsadjustment/{stock_adjustment}', [TransactionAdjustmentController::class, 'show'])->name('transactionsadjustment.show');
    Route::get('/transactionsadjustment/{stock_adjustment}/edit', [TransactionAdjustmentController::class, 'edit'])->name('transactionsadjustment.edit'); // Adjusted edit route for RESTfulness

    Route::put('/transactionsadjustment/{stock_adjustment}', [TransactionAdjustmentController::class, 'update'])->name('transactionsadjustment.update');
    Route::delete('/transactionsadjustment/{stock_adjustment}', [TransactionAdjustmentController::class, 'destroy'])->name('transactionsadjustment.destroy');

    Route::get('/branch-stock/{branchId}', [TransactionAdjustmentController::class, 'getBranchStock'])->name('branch.stock');

    //Route::resource('inventoryholdreview', InventoryHoldReviewController::class);
    Route::get('/inventoryholdreview', [InventoryHoldReviewController::class, 'index'])->name('inventoryholdreview.index');
    Route::get('/inventoryholdreview/create', [InventoryHoldReviewController::class, 'create'])->name('inventoryholdreview.create');
    Route::post('/inventoryholdreview', [InventoryHoldReviewController::class, 'store'])->name('inventoryholdreview.store');
    Route::get('/inventoryholdreview/{id}/details', [InventoryHoldReviewController::class, 'getDetails'])->name('inventoryholdreview.details');
    Route::get('/inventoryholdreview/{Id}', [InventoryHoldReviewController::class, 'show'])->name('inventoryholdreview.show');
    Route::get('/inventoryholdreview/{Id}/edit', [InventoryHoldReviewController::class, 'edit'])->name('inventoryholdreview.edit');
    Route::put('/inventoryholdreview/{id}', [InventoryHoldReviewController::class, 'update'])->name('inventoryholdreview.update');
    Route::delete('/inventoryholdreview/{id}', [InventoryHoldReviewController::class, 'destroy'])->name('inventoryholdreview.destroy');


    //Route::resource('interbranchrequisitionapproval', InterBranchRequisitionApprovalController::class);
    Route::get('/interbranchrequisitionapproval', [InterBranchRequisitionApprovalController::class, 'index'])->name('interbranchrequisitionapproval.index');
    Route::post('interbranchrequisitionapproval/submit', [InterBranchRequisitionApprovalController::class, 'submitDecision'])->name('interbranchrequisitionapproval.submit');


    //Route::resource('rentdashboard', RentDashboardController::class);

    // Route::resource('unitofmeasure', UOMController::class);
    Route::get('/unitofmeasure', [UOMController::class, 'index'])->name('unitofmeasure.index');
    Route::get('/unitofmeasure/create', [UOMController::class, 'create'])->name('unitofmeasure.create');
    Route::post('/unitofmeasure', [UOMController::class, 'store'])->name('unitofmeasure.store');
    Route::get('/unitofmeasure/{Id}', [UOMController::class, 'show'])->name('unitofmeasure.show');
    Route::get('/unitofmeasure/{Id}/edit', [UOMController::class, 'edit'])->name('unitofmeasure.edit');
    Route::put('/unitofmeasure/{Id}', [UOMController::class, 'update'])->name('unitofmeasure.update');
    Route::delete('/unitofmeasure/{Id}', [UOMController::class, 'destroy'])->name('unitofmeasure.destroy');

    Route::get('/pricemanagement', [PriceManagementController::class, 'index'])->name('pricemanagement.index');
    Route::get('/pricemanagement/create', [PriceManagementController::class, 'create'])->name('pricemanagement.create');
    Route::post('/pricemanagement', [PriceManagementController::class, 'store'])->name('pricemanagement.store');
    Route::get('/pricemanagement/{Id}', [PriceManagementController::class, 'show'])->name('pricemanagement.show');
    Route::get('/pricemanagement/{Id}/edit', [PriceManagementController::class, 'edit'])->name('pricemanagement.edit');
    Route::put('/pricemanagement/{Id}', [PriceManagementController::class, 'update'])->name('pricemanagement.update');
    Route::delete('/pricemanagement/{Id}', [PriceManagementController::class, 'destroy'])->name('pricemanagement.destroy');
    Route::post('/pricemanagement/upload', [PriceManagementController::class, 'importPricing'])->name('pricemanagement.upload');
    Route::get('/downloads/price_management_sample', [PriceManagementController::class, 'downloadSampleTemplate'])->name('pricemanagement.sample');


    //Route::resource('itemtype', ItemTypeController::class);
    Route::get('/itemtype', [ItemTypeController::class, 'index'])->name('itemtype.index');
    Route::get('/itemtype/create', [ItemTypeController::class, 'create'])->name('itemtype.create');
    Route::post('/itemtype', [ItemTypeController::class, 'store'])->name('itemtype.store');
    Route::get('/itemtype/{Id}', [ItemTypeController::class, 'show'])->name('itemtype.show');
    Route::get('/itemtype/{Id}/edit', [ItemTypeController::class, 'edit'])->name('itemtype.edit');
    Route::put('/itemtype/{Id}', [ItemTypeController::class, 'update'])->name('itemtype.update');
    Route::delete('/itemtype/{Id}', [ItemTypeController::class, 'destroy'])->name('itemtype.destroy');

    //Route::resource('inventorytype', InventoryTypeController::class);
    Route::get('/inventorytype', [InventoryTypeController::class, 'index'])->name('inventorytype.index');
    Route::get('/inventorytype/create', [InventoryTypeController::class, 'create'])->name('inventorytype.create');
    Route::post('/inventorytype', [InventoryTypeController::class, 'store'])->name('inventorytype.store');
    Route::put('/inventorytype/{id}', [InventoryTypeController::class, 'update'])->name('inventorytype.update');
    Route::delete('/inventorytype/{id}', [InventoryTypeController::class, 'destroy'])->name('inventorytype.destroy');

    Route::get('reports/{report}/{format}', [ReportsController::class, 'export'])->name('inventory-reports.export');
    Route::resource('reports', ReportsController::class)->only(['index', 'show'])->names([
        'index' => 'inventory-reports.index',
        'show' => 'inventory-reports.show'
    ]);
});
