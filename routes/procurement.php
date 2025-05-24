<?php

use App\Http\Controllers\Procurement\PurchaseOrderController;
use App\Http\Controllers\Procurement\RequisitionItemsController;
use App\Http\Controllers\Procurement\RequisitionsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Procurement\ItemController;
use App\Http\Controllers\Procurement\ModeTimelineController;
use App\Http\Controllers\Procurement\ProcurementModeController;
use App\Http\Controllers\Procurement\TenderController;
use App\Http\Controllers\Procurement\SasraAuditorController;
use App\Http\Controllers\Procurement\EngagedAuditorController;
use App\Http\Controllers\Procurement\SupplierController;
use App\Http\Controllers\Procurement\ProcurementPeriodController;
use App\Http\Controllers\Procurement\ProcurementPlanController;
use App\Http\Controllers\Procurement\RFQController;
use App\Http\Controllers\Procurement\RFQResponseController;
use App\Http\Controllers\Procurement\RFQEvaluationController;
use App\Http\Controllers\Procurement\GoodsReceiptController;
use App\Http\Controllers\Procurement\TenderInitiationController;
use App\Http\Controllers\Procurement\TenderCategoryController;
use App\Http\Controllers\Procurement\TenderTypeController;
use App\Http\Controllers\Procurement\TenderInitiationApproveController;
use App\Http\Controllers\Procurement\TenderResponseController;
use App\Http\Controllers\Procurement\TenderclarificationController;
use App\Http\Controllers\Procurement\TenderSubmissionController;

use App\Http\Controllers\Procurement\TenderOpeningController;
use App\Http\Controllers\Procurement\TenderDecryptController;
use App\Http\Controllers\Procurement\TenderCommitteeController;
use App\Http\Controllers\Procurement\TenderAcceptController;
use App\Http\Controllers\Procurement\TenderAssignRoleController;
use App\Http\Controllers\Procurement\EvaluationCriteriaController;
use App\Http\Controllers\Procurement\BidEvaluationController;
use App\Http\Controllers\Procurement\BidScoreConsolidationController;
use App\Http\Controllers\Procurement\EvaluatorDashboardController;
use App\Http\Controllers\Procurement\ProcurementReportsController;

use App\Http\Controllers\Procurement\ProcurementPlanMaintainController;
use App\Http\Controllers\Procurement\ProcurementApprovalController;
use App\Http\Controllers\Procurement\RaiseNeedsController;
use App\Http\Controllers\Procurement\NeedApprovalController;
use App\Http\Controllers\Procurement\ConsolidatedDashboardController;
use App\Http\Controllers\Procurement\ProcurementPlanDetailController;
use App\Http\Controllers\Procurement\ProcurementquaterlyController;
use App\Http\Controllers\Procurement\ProcurementAssignMethodController;
use App\Http\Controllers\Procurement\MapToBudgetController;
use App\Http\Controllers\Procurement\RFQLinesController;
use App\Http\Controllers\Procurement\DepartmentNeedsController;
use App\Http\Controllers\Procurement\ProcurementPlanDashboardController;
use App\Http\Controllers\Procurement\TimelineController;
use App\Http\Controllers\Procurement\CalenderBasedController;
use App\Http\Controllers\Procurement\DelayedItemsController;
use App\Http\Controllers\Procurement\PlanvsActualController;
use App\Http\Controllers\Procurement\PlanFromNeedsController;
use App\Http\Controllers\Procurement\PlanManualInputController;
use App\Http\Controllers\Procurement\SubmitForApprovalController;
use App\Http\Controllers\Procurement\PlanEditController;
use App\Http\Controllers\Procurement\PlanApprovalInboxController;
use App\Http\Controllers\Procurement\PlanExectionDashboardController;
use App\Http\Controllers\Procurement\DepartmentNeedApprovalController;

Route::namespace('Procurement')->prefix('procurement')->group(function () {



    //Requisitions
    Route::resource('requisition', 'RequisitionsController');
    Route::resource('requisitionItem', 'RequisitionItemsController');
    Route::get('items/download', [ItemController::class, 'download'])->name('items.download');

    //this route is static affecting orders\create.blade.php & requisitions\show
    Route::get('requisitionItem/getItem/{type}', [RequisitionItemsController::class, 'getItems'])->name('requisitionItem.getItems');

    // this route is static affecting orders\create.blade.php & requisitions\show
    Route::get('requisitionItem/getItemDetails/{item}', 'RequisitionItemsController@getItemDetails')->name('requisitionItem.getItemDetails');

    Route::get('requisitionItem/{id}', [RequisitionItemsController::class, 'show'])->name('requisitionItem.show');
    Route::get('requisitionItem/create/{id}', [RequisitionItemsController::class, 'create'])->name('requisitionItems.create');
    Route::get('requisition/approval', [RequisitionsController::class, 'approvalList'])->name('requisition.approval');

    //Purchase Order
    Route::get('purchaseOrder/getSuppliers', [PurchaseOrderController::class, 'getSuppliers'])->name('purchaseOrder.getSuppliers');
    Route::resource('purchaseOrder', 'PurchaseOrderController');



    //Sales Order
    Route::resource('salesOrder', 'SalesOrderController');


    //Items
    Route::resource('items', 'ItemController');
    Route::resource('categories', 'ItemCategoryController');

    // Procurement Modes
    Route::resource('procurement-modes', ProcurementModeController::class);

    //Mode Timelines
    Route::post('/timelines', [ModeTimelineController::class, 'store'])->name('timelines.store');
    Route::delete('timelines/{id}', [ModeTimelineController::class, 'destroy'])->name('timelines.destroy');
    Route::get('timelines/{id}/edit', [ModeTimelineController::class, 'edit'])->name('timelines.edit');
    Route::put('timelines/{id}', [ModeTimelineController::class, 'update'])->name('timelines.update');

    // Tendering Process
    // Route::resource('tendering-process', TenderController::class);

    //Auditor Routes
    Route::get('/sasra-auditors', [SasraAuditorController::class, 'index'])->name('sasra-auditors.index');
    Route::get('/sasra-auditors/upload', [SasraAuditorController::class, 'showImportForm'])->name('sasra-auditors.show');
    Route::post('/sasra-auditors/upload', [SasraAuditorController::class, 'import'])->name('sasra-auditors.import');
    Route::get('/sasra-auditors/download', [SasraAuditorController::class, 'download'])->name('sasra-auditors.download');
    Route::get('/engaged-auditors', [EngagedAuditorController::class, 'index'])->name('engaged-auditors.index');
    Route::post('/engaged-auditors', [EngagedAuditorController::class, 'store'])->name('engaged-auditors.store');
    Route::get('/engaged-auditors/create', [EngagedAuditorController::class, 'create'])->name('engaged-auditors.create');
    Route::get('/engaged-auditors/{id}/edit', [EngagedAuditorController::class, 'edit'])->name('engaged-auditors.edit');
    Route::put('/engaged-auditors/{id}', [EngagedAuditorController::class, 'update'])->name('engaged-auditors.update');
    Route::delete('/engaged-auditors/{id}', [EngagedAuditorController::class, 'destroy'])->name('engaged-auditors.destroy');

    // Suppliers
    Route::resource('suppliers', SupplierController::class);


    // Procurement Periods
    Route::resource('procurement-periods', ProcurementPeriodController::class);

    Route::get('/procurement-periods/{id}/assign-suppliers', [ProcurementPeriodController::class, 'assignSuppliersForm'])->name('procurement-periods.assign-suppliers-form');
    Route::post('/procurement-periods/{id}/assign-suppliers', [ProcurementPeriodController::class, 'assignSuppliers'])->name('procurement-periods.assign-suppliers');

    Route::get('/procurement-periods/{period}/plans/create', [ProcurementPlanController::class, 'create'])->name('procurement-periods.plans.create');
    Route::post('/procurement-periods/{period}/plans', [ProcurementPlanController::class, 'store'])->name('procurement-periods.plans.store');

    // RFQLines Routes
    Route::post('/rfqlines', [RFQLinesController::class, 'store'])->name('linecategories.store');
    Route::get('/rfqlines/create', [RFQLinesController::class, 'create'])->name('rfqlines.create');
    Route::get('/requisitionlines/categories', [RFQLinesController::class, 'getCategories']);

    // RFQ routes
    Route::get('/rfqs/create', [RFQController::class, 'create'])->name('rfqs.create');
    Route::post('/rfqs', [RFQController::class, 'store'])->name('rfqs.store');
    Route::get('/rfqs/{id}', [RFQController::class, 'show'])->name('rfqs.show');
    Route::get('/rfqs', [RFQController::class, 'index'])->name('rfqs.index');
    Route::post('/rfqs/{rfq}/approve', [RFQController::class, 'approve'])->name('rfqs.approve');
    Route::post('/rfqs/{rfq}/reject', [RFQController::class, 'reject'])->name('rfqs.reject');

    // RFQ Response routes
    Route::get('/rfqresponses', [RFQResponseController::class, 'index'])->name('rfqresponses.index');
    Route::get('/rfqresponses/create', [RFQResponseController::class, 'create'])->name('rfqresponses.create');
    Route::post('/rfqresponses', [RFQResponseController::class, 'store'])->name('rfqresponses.store');
    Route::get('/rfqresponses/{id}', [RFQResponseController::class, 'show'])->name('rfqresponses.show');
    Route::get('/rfqresponses/{id}/edit', [RFQResponseController::class, 'edit'])->name('rfqresponses.edit');
    Route::put('/rfqresponses/{id}', [RFQResponseController::class, 'update'])->name('rfqresponses.update');
    Route::delete('/rfqresponses/{id}', [RFQResponseController::class, 'destroy'])->name('rfqresponses.destroy');
    Route::get('/rfq-responses/{rfqId}', [RFQEvaluationController::class, 'getRFQResponses'])->name('rfq.responses');
    Route::get('/rfqs/{rfqId}/requisition-items', [RFQResponseController::class, 'getRequisitionItems']);

    // RFQ Evaluation routes
    Route::get('/rfq-evaluations', [RFQEvaluationController::class, 'index'])->name('evaluations.index');
    Route::get('/rfq-evaluations/create', [RFQEvaluationController::class, 'create'])->name('evaluations.create');
    Route::post('/rfq-evaluations', [RFQEvaluationController::class, 'store'])->name('evaluations.store');
    Route::get('/rfq-suppliers/{rfqId}', [RFQResponseController::class, 'getSuppliersByRFQ']);

    //GoodsReceipts
    //Route::resource('procurementreceipts', GoodsReceiptController::class);
    Route::get('/procurementreceipts', [GoodsReceiptController::class, 'index'])->name('procurementreceipts.index');
    Route::get('/procurementreceipts/create', [GoodsReceiptController::class, 'create'])->name('procurementreceipts.create');
    Route::post('/procurementreceipts', [GoodsReceiptController::class, 'store'])->name('procurementreceipts.store');
    Route::get('/procurementreceipts/lines/{grnId}/{poId}', [GoodsReceiptController::class, 'fetchLinesByGRN']);
    Route::put('/procurementreceipts/update-line', [GoodsReceiptController::class, 'updateLine'])->name('procurementreceipts.updateLine');
    Route::delete('/procurementreceipts/delete/{grnId}/{poId}', [GoodsReceiptController::class, 'destroy'])->name('procurementreceipts.destroy');
    Route::post('/procurementreceipts/post', [GoodsReceiptController::class, 'postReceipt'])->name('procurementreceipts.post');


    //Route::resource('procurementreceipts', GoodsReceiptController::class);

    //Tenders
    Route::resource('initiatetender', TenderController::class);
    Route::resource('tendercategory', TenderCategoryController::class);
    Route::resource('tendertype', TenderTypeController::class);
    Route::resource('initiateapprove', TenderInitiationApproveController::class);
    Route::resource('tenderresponse', TenderResponseController::class);
    Route::resource('tenderclarification', TenderclarificationController::class);
    Route::resource('tendersubmission', TenderSubmissionController::class);
    Route::resource('tenderopening', TenderOpeningController::class);
    //Route::resource('tenderresponse', TenderResponseController::class);
    //Route::resource('tenderclarification', TenderclarificationController::class);
    Route::resource('tendersubmission', TenderSubmissionController::class);
    Route::resource('tenderopening', TenderOpeningController::class);
    Route::resource('tenderdecrypt', TenderDecryptController::class);
    Route::resource('tendercommittee', TenderCommitteeController::class);
    Route::resource('memberresponse', TenderAcceptController::class);
    Route::resource('assignrole', TenderAssignRoleController::class);
    Route::resource('evaluationcriteria', EvaluationCriteriaController::class);
    Route::resource('bidevaluation', BidEvaluationController::class);
    Route::resource('evaluationdashboard', EvaluatorDashboardController::class);
    Route::resource('bidscores', BidScoreConsolidationController::class);
    Route::resource('procurementreports', ProcurementReportsController::class);
    Route::resource('evaluationdashboard', EvaluatorDashboardController::class);
    Route::resource('bidscores', BidScoreConsolidationController::class);
    Route::resource('procurementreports', ProcurementReportsController::class);


    //Procurementplan
    Route::resource('procurementplanmaintain', ProcurementPlanMaintainController::class);

    //Procurement plan Approval
    //Route::resource('procurementplanapproval', ProcurementApprovalController::class);
    Route::get('/NeedApproval', [NeedApprovalController::class,'index'])->name('department-need-approval.index');
    Route::get('/NeedApproval/{Id}', [NeedApprovalController::class, 'show'])->name('department-need-approval.show');

    Route::resource('approvals/department-need', DepartmentNeedApprovalController::class)->only([
        'index','show','update','destroy'
    ])->names([
       'index' => 'department-need-approval.index','show' => 'department-need-approval.show','update' => 'department-need-approval.update',
       'destroy' => 'department-need-approval.destroy'
    ]);

    //Procurement Plan Department Needs
    //Route::resource('procurementdepartmentalplan', DepartmentNeedsController::class);
    Route::get('/departmentalplan', [DepartmentNeedsController::class,'index'])->name('procurementdepartmentalplan.index');

    Route::get('/procurementdepartmentalplan/create', [DepartmentNeedsController::class,'create'])->name('procurementdepartmentalplan.create');
    Route::post('/procurementdepartmentalplan', [DepartmentNeedsController::class,'store'])->name('procurementdepartmentalplan.store');
    Route::get('/procurementdepartmentalplan/lines/{NeedID}', [DepartmentNeedsController::class, 'fetchLinesByDPlan'])->name('procurementdepartmentalplan.view');
    Route::put('/procurementdepartmentalplan/update-line', [DepartmentNeedsController::class, 'update'])->name('procurementdepartmentalplan.updateLine');
    Route::delete('/procurementdepartmentalplan/delete/{NeedID}', [DepartmentNeedsController::class, 'destroy'])->name('procurementdepartmentalplan.destroy');
    Route::get('/procurementdepartmentalplan/data', [DepartmentNeedsController::class, 'getDepartmentNeeds'])->name('procurementdepartmentalplan.data');


    //Procurement Plan, Plan Consolidation
    Route::resource('consolidated', ConsolidatedDashboardController::class);
    Route::resource('procurementplandetails', ProcurementPlanDetailController::class);
    Route::resource('procurementplanquaterly', ProcurementquaterlyController::class);
    Route::resource('procurementitemsdashboard', ConsolidatedDashboardController::class);
    Route::resource('procurementassignitem', ProcurementAssignMethodController::class);
    Route::resource('procurementplanmaintain', ProcurementPlanMaintainController::class);
    Route::resource('procurementplanapproval', ProcurementApprovalController::class);
    //Route::resource('procurementdepartmentalplan', RaiseNeedsController::class);
    Route::resource('consolidated', ConsolidatedDashboardController::class);
    Route::resource('procurementplandetails', ProcurementPlanDetailController::class);
    Route::resource('procurementplanquaterly', ProcurementquaterlyController::class);
    Route::resource('procurementitemsdashboard', ConsolidatedDashboardController::class);
    Route::resource('procurementassignitem', ProcurementAssignMethodController::class);
    Route::resource('maptobudget', MapToBudgetController::class);
    Route::resource('plantimeline', TimelineController::class);
    Route::resource('calenderbased', CalenderBasedController::class);
    Route::resource('delayeditems', DelayedItemsController::class);
    Route::resource('planvsactual', PlanvsActualController::class);
    //Route::resource('needsapproval', NeedApprovalController::class);
    Route::resource('planfromneeds', PlanFromNeedsController::class);
    Route::resource('planmanualinput', PlanManualInputController::class);
    Route::resource('submitplan', SubmitForApprovalController::class);
    Route::resource('editplan', PlanEditController::class);
    Route::resource('approvalinbox', PlanApprovalInboxController::class);
    Route::resource('exectiondashboard', PlanExectionDashboardController::class);

    //Tendering
    Route::get('/tenderresponse', [TenderResponseController::class, 'index'])->name('tenderresponse.index');
    Route::get('/tenderresponse/create', [TenderResponseController::class, 'create'])->name('tenderresponse.create');
    Route::post('/tenderresponse', [TenderResponseController::class, 'storeResponse'])->name('tenderresponse.storeResponse');

    // Route::resource('tenderclarification', TenderclarificationController::class)
    Route::get('/tenderclarification', [TenderclarificationController::class, 'index'])->name('tenderclarification.index');
    //Route::get('/tenderclarification/create', [TenderclarificationController::class, 'create'])->name('tenderclarification.create');
    Route::patch('/tenderclarification/update', [TenderclarificationController::class, 'update'])->name('tenderclarification.update');
    Route::get('/clarifications/edit', [TenderclarificationController::class, 'edit'])->name('tenderclarification.edit');
    Route::get('/tenderclarifications/{clarification_id}/create', [TenderclarificationController::class, 'create'])->name('tenderclarification.create');

    //Bid Submission
    Route::get('/bid-submissions', [TenderSubmissionController::class, 'index'])->name('tendersubmission.index');
    Route::get('/bid-submissions/create', [TenderSubmissionController::class, 'create'])->name('tendersubmission.create');
    Route::get('/bid-submission/manual/{Id}/view', [TenderSubmissionController::class, 'view'])->name('tendersubmission.view');
    Route::get('/bid-submission/manual/{Id}/edit', [TenderSubmissionController::class, 'edit'])->name('tendersubmission.edit');
    Route::post('/bid-submissions', [TenderSubmissionController::class, 'store'])->name('tendersubmission.store');

    //procurement Consolidation
    Route::get('/dashboard', [ConsolidatedDashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/show/{id}', [ConsolidatedDashboardController::class, 'show'])->name('dashboard.show');

    Route::post('/procurement-plans', [ProcurementPlanMaintainController::class, 'store'])->name('procurementplanmaintain.store');

    Route::prefix('procurement')->group(function () {
    Route::get('plan-manual-input/create', [PlanManualInputController::class, 'create'])->name('plan.manual-input.create');
    Route::post('plan-manual-input', [PlanManualInputController::class, 'store'])->name('plan.manual-input.store');
        });
    Route::prefix('procurement/plan')->name('plan-from-needs.')->group(function () {
    Route::get('/select-approved-needs', [PlanFromNeedsController::class, 'create'])->name('create');
    Route::post('/store-from-needs', [PlanFromNeedsController::class, 'store'])->name('store');
        });

});
