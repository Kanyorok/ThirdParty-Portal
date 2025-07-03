<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Procurement\ApprovalSetupController;
use App\Http\Controllers\Procurement\AwardsController;
use App\Http\Controllers\Procurement\BidEvaluationController;
use App\Http\Controllers\Procurement\BidScoreConsolidationController;
use App\Http\Controllers\Procurement\CalenderBasedController;
use App\Http\Controllers\Procurement\ConsolidatedDashboardController;
use App\Http\Controllers\Procurement\CriteriaController;
use App\Http\Controllers\Procurement\DelayedItemsController;
use App\Http\Controllers\Procurement\DepartmentNeedApprovalController;
use App\Http\Controllers\Procurement\DepartmentNeedsController;
use App\Http\Controllers\Procurement\EngagedAuditorController;
use App\Http\Controllers\Procurement\EvaluationCriteriaController;
use App\Http\Controllers\Procurement\EvaluatorDashboardController;
use App\Http\Controllers\Procurement\GoodsReceiptController;
use App\Http\Controllers\Procurement\MapToBudgetController;
use App\Http\Controllers\Procurement\ModeTimelineController;
use App\Http\Controllers\Procurement\PlanApprovalInboxController;
use App\Http\Controllers\Procurement\PlanEditController;
use App\Http\Controllers\Procurement\PlanExectionDashboardController;
use App\Http\Controllers\Procurement\PlanFromNeedsController;
use App\Http\Controllers\Procurement\PlanManualInputController;
use App\Http\Controllers\Procurement\PlanvsActualController;
use App\Http\Controllers\Procurement\PrequalificationApplicationsController;
use App\Http\Controllers\Procurement\PrequalificationEvalAprovalController;
use App\Http\Controllers\Procurement\PrequalificationEvaluationController;
use App\Http\Controllers\Procurement\PrequalificationPeriodController;
use App\Http\Controllers\Procurement\PrequalificationCriteriaController;
use App\Http\Controllers\Procurement\PrequalificationCriteriaSetupController;
use App\Http\Controllers\Procurement\PrequalifiedSuppliersController;
use App\Http\Controllers\Procurement\ProcurementApprovalController;
use App\Http\Controllers\Procurement\ProcurementModeController;
use App\Http\Controllers\Procurement\ProcurementPeriodController;
use App\Http\Controllers\Procurement\ProcurementPlanController;
use App\Http\Controllers\Procurement\ProcurementPlanMaintainController;
use App\Http\Controllers\Procurement\ProcurementReportsController;
use App\Http\Controllers\Procurement\ProcurementSchedulePlanController;
use App\Http\Controllers\Procurement\ProcurementSetMethodController;
use App\Http\Controllers\Procurement\ProcurementSubmitPlanController;
use App\Http\Controllers\Procurement\PurchaseOrderController;
use App\Http\Controllers\Procurement\ReportsController;
use App\Http\Controllers\Procurement\RequisitionItemsController;
use App\Http\Controllers\Procurement\RequisitionsController;
use App\Http\Controllers\Procurement\RFQController;
use App\Http\Controllers\Procurement\RFQEvaluationController;
use App\Http\Controllers\Procurement\RFQLinesController;
use App\Http\Controllers\Procurement\RFQResponseController;
use App\Http\Controllers\Procurement\SasraAuditorController;
use App\Http\Controllers\Procurement\SectionController;
use App\Http\Controllers\Procurement\SubmitForApprovalController;
use App\Http\Controllers\Procurement\SupplierController;
use App\Http\Controllers\Procurement\SupplierListingController;
use App\Http\Controllers\Procurement\TenderAcceptController;
use App\Http\Controllers\Procurement\TenderAssignRoleController;
use App\Http\Controllers\Procurement\TenderBidResponsivenessController;
use App\Http\Controllers\Procurement\TenderCategoryController;
use App\Http\Controllers\Procurement\TenderclarificationController;
use App\Http\Controllers\Procurement\TenderCommitteeController;
use App\Http\Controllers\Procurement\TenderController;
use App\Http\Controllers\Procurement\TenderDecryptController;
use App\Http\Controllers\Procurement\TenderEvaluationsController;
use App\Http\Controllers\Procurement\TenderInitiationApproveController;
use App\Http\Controllers\Procurement\TenderOpeningController;
use App\Http\Controllers\Procurement\TenderResponseController;
use App\Http\Controllers\Procurement\TenderSubmissionController;
use App\Http\Controllers\Procurement\TenderTypeController;
use App\Http\Controllers\Procurement\TimelineController;



Route::namespace('Procurement')->prefix('procurement')->group(function () {


    //Requisitions
    Route::resource('requisition', 'RequisitionsController');
    Route::resource('requisitionItem', 'RequisitionItemsController');

    //this route is static affecting orders\create.blade.php & requisitions\show
    Route::get('requisitionItem/getItem/{type}', [RequisitionItemsController::class, 'getItems'])->name('requisitionItem.getItems');

    // this route is static affecting orders\create.blade.php & requisitions\show
    Route::get('requisitionItem/getItemDetails/{item}', [RequisitionItemsController::class, 'getItemDetails'])->name('requisitionItem.getItemDetails');

//    Route::get('requisitionItem/{id}', [RequisitionItemsController::class, 'show'])->name('requisitionItem.show');
//    Route::get('requisitionItem/create/{id}', [RequisitionItemsController::class, 'create'])->name('requisitionItems.create');

    Route::post('requisition/approve/{id}', [RequisitionsController::class, 'approve'])->name('requisition.approve');
    Route::get('requisition/approval/{id}', [RequisitionsController::class, 'approval'])->name('requisition.approval');
    Route::prefix('admin')->group(function () {
        Route::put('/approval-settings/{id}', [ApprovalSetupController::class, 'update'])->name('approval-setup.update');
        Route::post('/approval-settings', [ApprovalSetupController::class, 'store'])->name('approval-settings.store');
        Route::delete('/approval-settings/{id}', [ApprovalSetupController::class, 'destroy'])->name('approval-setup.destroy');
        Route::get('/approval-settings', [ApprovalSetupController::class, 'index'])->name('approval-setup.index');
    });

    //Purchase Order
    Route::get('purchaseOrder/getSuppliers', [PurchaseOrderController::class, 'getSuppliers'])->name('purchaseOrder.getSuppliers');
    Route::get('purchaseOrder/linkRFQ', [PurchaseOrderController::class, 'linkRFQ'])->name('purchaseOrder.linkRFQ');
    Route::post('purchaseOrder/approve/{id}', [PurchaseOrderController::class, 'approve'])->name('purchaseOrder.approve');
    Route::get('purchaseOrder/approval/{id}', [PurchaseOrderController::class, 'approval'])->name('purchaseOrder.approval');
    //this route is static affecting orders/rfqLink
    Route::get('purchaseOrder/rqfDetails/{id}', [PurchaseOrderController::class, 'fetchRFQDetails'])->name('purchaseOrder.RFQ');
    Route::resource('purchaseOrder', 'PurchaseOrderController');

    //Sales Order
    Route::resource('salesOrder', 'SalesOrderController');

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
    Route::get('/rfq-suppliers/{rfqId}', [RFQResponseController::class, 'getSuppliers']);

    //GoodsReceipts
    Route::get('/procurementreceipts', [GoodsReceiptController::class, 'index'])->name('procurementreceipts.index');
    Route::get('/procurementreceipts/create', [GoodsReceiptController::class, 'create'])->name('procurementreceipts.create');
    Route::post('/procurementreceipts', [GoodsReceiptController::class, 'store'])->name('procurementreceipts.store');
    Route::get('/procurementreceipts/lines/{grnId}/{poId}', [GoodsReceiptController::class, 'fetchLinesByGRN']);
    Route::put('/procurementreceipts/update-line', [GoodsReceiptController::class, 'updateLine'])->name('procurementreceipts.updateLine');
    Route::delete('/procurementreceipts/delete/{grnId}/{poId}', [GoodsReceiptController::class, 'destroy'])->name('procurementreceipts.destroy');
    Route::post('/procurementreceipts/post', [GoodsReceiptController::class, 'postReceipt'])->name('procurementreceipts.post');

    //Tenders
    Route::resource('initiatetender', TenderController::class);
    Route::resource('tendercategory', TenderCategoryController::class);
    Route::resource('tendertype', TenderTypeController::class);
    //Route for tender approval and Reject
    Route::post('/tenderapproval', [TenderController::class, 'approveTender'])->name('tender.approve');
    Route::post('/tenderRejection', [TenderController::class, 'rejectTender'])->name('tender.reject');
    Route::resource('initiateapprove', TenderInitiationApproveController::class);
    Route::resource('tenderresponse', TenderResponseController::class);
    Route::resource('tenderclarification', TenderclarificationController::class);
    Route::resource('tendersubmission', TenderSubmissionController::class);
    Route::resource('tenderopening', TenderOpeningController::class);
    Route::resource('tendersubmission', TenderSubmissionController::class);
    Route::resource('tenderdecrypt', TenderDecryptController::class);
    Route::resource('tendercommittee', TenderCommitteeController::class);
    Route::post('/store-tender-committee', [TenderCommitteeController::class, 'membersAdd'])->name('tendercommittee.save');
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

    //Tender Creteria setup
    Route::resource('sections',SectionController::class);
    Route::resource('criterias',CriteriaController::class);
    Route::resource('tenderevaluations',TenderEvaluationsController::class);
    Route::post("/store-tender-sections",[TenderEvaluationsController::class,'tenderSections'])->name('store-tender-sections');
    Route::get('/tender-criteria/{tenderId}', [TenderEvaluationsController::class, 'getTenderCriteria'])->name('tender-criteria');
    Route::post('/tender-criteria', [TenderEvaluationsController::class, 'storeTenderCriteria'])->name('tender-criteria.store');
    Route::post('/store-criteria-scores', [TenderEvaluationsController::class, 'criteriaScores'])->name('store-criteria-scores');
    Route::get('/criteria-sections', [EvaluationCriteriaController::class, 'viewCriteria'])->name('tender-criteria.index');
    //Route::prefix('procurement')->group(function () {
    Route::put('/criterias/{id}', [CriteriaController::class, 'update'])->name('criterias.update');
    Route::delete('/criterias/{id}', [CriteriaController::class, 'destroy'])->name('criterias.destroy');
    Route::put('/sections/{id}', [SectionController::class, 'update'])->name('sections.update');
    Route::delete('/sections/{id}', [SectionController::class, 'destroy'])->name('sections.destroy');
    Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');


    //});


    //Route::get('/tenderevaluations', [EvaluationCriteriaController::class, 'tenderEvaluations'])->name('tenderevaluations.index');


    //Procurementplan
    Route::resource('procurementplanmaintain', ProcurementPlanMaintainController::class);

    //Procurement plan Approval
    Route::resource('approvals/department-need', DepartmentNeedApprovalController::class)->only([
        'index', 'show', 'update', 'destroy'
    ])->names([
        'index' => 'department-need-approval.index', 'show' => 'department-need-approval.show', 'update' => 'department-need-approval.update',
        'destroy' => 'department-need-approval.destroy'
    ]);


    //Procurement plan set Method
    Route::get('/Procurement-Set-Method', [ProcurementSetMethodController::class, 'index'])->name('procurement-set-method.index');
    Route::get('/procurement/set-method/plan-items/{PlanId}', [ProcurementSetMethodController::class, 'getPlanItems']);
    Route::get('/Procurement-Set-Method/create', [ProcurementSetMethodController::class, 'create'])->name(name: 'procurement-set-method.create');
    Route::post('/Procurement-Set-Method', [ProcurementSetMethodController::class, 'store'])->name('procurement-set-method.store');


    //Procurement Plan Department Needs
    Route::get('/departmentalplan', [DepartmentNeedsController::class, 'index'])->name('procurementdepartmentalplan.index');
    Route::get('/procurementdepartmentalplan/create', [DepartmentNeedsController::class, 'create'])->name('procurementdepartmentalplan.create');
    Route::post('/procurementdepartmentalplan', [DepartmentNeedsController::class, 'store'])->name('procurementdepartmentalplan.store');
    Route::get('/procurementdepartmentalplan/lines/{NeedID}', [DepartmentNeedsController::class, 'fetchLinesByDPlan'])->name('procurementdepartmentalplan.view');
    Route::put('/procurementdepartmentalplan/update-line', [DepartmentNeedsController::class, 'update'])->name('procurementdepartmentalplan.updateLine');
    Route::delete('/procurementdepartmentalplan/delete/{NeedID}', [DepartmentNeedsController::class, 'destroy'])->name('procurementdepartmentalplan.destroy');
    Route::get('/procurementdepartmentalplan/data', [DepartmentNeedsController::class, 'getDepartmentNeeds'])->name('procurementdepartmentalplan.data');

    //Procurement Schedule Plan
    //Route::resource('procurementplanquaterly', ProcurementquaterlyController::class);
    Route::get('/Procurement-Plan-Schedule', [ProcurementSchedulePlanController::class, 'index'])->name('Procurement-Plan-Schedule.index');
    Route::get('/Procurement-Plan-Schedule/create/{PlanId}', [ProcurementSchedulePlanController::class, 'create'])->name('Procurement-Plan-Schedule.create');
    Route::get('/Procurement-Plan-Schedule/lines/{PlanId}', [ProcurementSchedulePlanController::class, 'fetchLinesByDPlan'])->name('Procurement-Plan-Schedule.view');
    Route::post('/Procurement-Plan-Schedule', [ProcurementSchedulePlanController::class, 'store'])->name('Procurement-Plan-Schedule.store');
    Route::get('/Procurement-Plan-Schedule/edit/{lineItemId}', [ProcurementSchedulePlanController::class, 'edit'])->name('Procurement-Plan-Schedule.edit');

    //Procurement Plan Approval Submission
    //Route::resource('procurementplandetails', ProcurementPlanDetailController::class);
    Route::get('/Procurement-Plan-Submission', [ProcurementSubmitPlanController::class,'index'])->name('Procurement-Plan-Submission.index');
    Route::get('/Procurement-Plan-Submission/create/{PlanId}', [ProcurementSubmitPlanController::class, 'create'])->name('Procurement-Plan-Submission.create');
    //Route::put('/Procurement-Plan-Submission', [ProcurementSubmitPlanController::class, 'store'])->name('Procurement-Plan-Submission.store');
    Route::put('/Procurement-Plan-Submission/{plan}', [ProcurementSubmitPlanController::class, 'update'])->name('Procurement-Plan-Submission.update');

    //Procurement Plan, Plan Consolidation
    // Route::resource('procurementplandetails', ProcurementPlanDetailController::class);
    Route::resource('procurementplanmaintain', ProcurementPlanMaintainController::class);
    Route::resource('procurementplanapproval', ProcurementApprovalController::class);
    Route::resource('consolidated', ConsolidatedDashboardController::class);
    Route::resource('procurementitemsdashboard', ConsolidatedDashboardController::class);
    Route::resource('maptobudget', MapToBudgetController::class);
    Route::resource('plantimeline', TimelineController::class);
    Route::resource('calenderbased', CalenderBasedController::class);
    Route::resource('delayeditems', DelayedItemsController::class);
    Route::resource('planvsactual', PlanvsActualController::class);
    Route::resource('planfromneeds', PlanFromNeedsController::class);
    Route::resource('planmanualinput', PlanManualInputController::class);
    Route::resource('submitplan', SubmitForApprovalController::class);
    Route::resource('ammendplan', PlanEditController::class);
    Route::resource('approvalinbox', PlanApprovalInboxController::class);
    Route::resource('executiondashboard', PlanExectionDashboardController::class);

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
    Route::get('/dashboard/show/{needId}', [ConsolidatedDashboardController::class, 'show'])->name('dashboard.show');
    Route::get('/procurement/dashboard/export', [ConsolidatedDashboardController::class, 'exportExcel'])->name('dashboard.export');

    Route::post('/procurement-plans', [ProcurementPlanMaintainController::class, 'store'])->name('procurementplanmaintain.store');
    Route::get('/planning/edit-draft/{plan_id}', [ProcurementPlanMaintainController::class, 'editDraft'])
        ->name('planning.editDraft');
    Route::get('/procurementplanmaintain/{id}', [ProcurementPlanMaintainController::class, 'show'])->name('procurementplanmaintain.show');


    Route::prefix('procurement')->group(function () {

        Route::get('plan-manual-input', [PlanManualInputController::class, 'index'])->name('procurement.procurementplan.planconsolidation.manualentry.index');
        Route::get('plan-manual-input/create', [PlanManualInputController::class, 'create'])->name('plan.manual-input.create');
        Route::post('plan-manual-input', [PlanManualInputController::class, 'store'])->name('plan.manual-input.store');

        Route::get('plan-manual-input/edit/{lineItem}', [PlanManualInputController::class, 'edit'])->name('plan.manual-input.edit');
        Route::put('plan-manual-input/{lineItem}', [PlanManualInputController::class, 'update'])->name('plan.manual-input.update');
        Route::delete('plan-manual-input/destroy/{lineItem}', [PlanManualInputController::class, 'destroy'])->name('plan.manual-input.destroy');

    });
    Route::prefix('procurement/plan')->name('plan-from-needs.')->group(function () {
        Route::get('/create', [PlanFromNeedsController::class, 'create'])->name('create');
        Route::post('/store', [PlanFromNeedsController::class, 'store'])->name('store');
        Route::get('/planning/filter-needs', [PlanFromNeedsController::class, 'filterNeeds'])->name('planning.filterNeeds');
        Route::get('/departments/{branchId}', [PlanFromNeedsController::class, 'getDepartments']);
        Route::get('/categories', [PlanFromNeedsController::class, 'getCategories']);
    });
    Route::get('/planning/edit', [PlanEditController::class, 'index'])->name('planning.editDraftItems');
    Route::get('/planning/edit-draft/{plan_id}', [PlanEditController::class, 'index']);
    Route::post('/planning/update-draft-items', [PlanEditController::class, 'updateDraftItems'])->name('planning.updateDraftItems');
    Route::delete('procurement/planning/delete-draft-item/{id}', [PlanEditController::class, 'deleteDraftItem'])->name('procurement.planning.deleteDraftItem');

    Route::get('/planning/assign-methods', [MapToBudgetController::class, 'index'])->name('planning.assign.methods');
    Route::post('/planning/assign-methods', [MapToBudgetController::class, 'store'])->name('planning.assign.methods.store');
//Plan Approval
Route::prefix('planning')->name('planning.')->group(function () {
    Route::get('/approval', [ProcurementApprovalController::class, 'index'])->name('approval.index');
    Route::get('/approval/plan-details', [ProcurementApprovalController::class, 'getPlanDetails'])->name('getPlanDetails');
    Route::post('/approval/submit-decision', [ProcurementApprovalController::class, 'submitDecision'])->name('submitDecision');
});

    Route::get('reports/{report}/{format}', [ReportsController::class, 'export'])->name('procurement-reports.export');
    Route::resource('reports', ReportsController::class)->only(['index', 'show'])->names([
        'index' => 'procurement-reports.index',
        'show' => 'procurement-reports.show'
    ]);
   // Route::get('/planning/get-plan-details', [ProcurementApprovalController::class, 'getPlanDetails'])
   // ->name('planning.getPlanDetails');

});


//Route::resource('preqrounds', PrequalificationRoundsController::class);
Route::get('preqrounds', [PrequalificationPeriodController::class, 'index'])->name('preqrounds.index');
Route::get('preqrounds/create', [PrequalificationPeriodController::class, 'create'])->name('preqrounds.create');
Route::post('preqrounds', [PrequalificationPeriodController::class, 'store'])->name('preqrounds.store');
Route::get('preqrounds/{Id}', [PrequalificationPeriodController::class, 'show'])->name('preqrounds.show');
Route::get('preqrounds/edit/{Id}', [PrequalificationPeriodController::class, 'edit'])->name('preqrounds.edit');
Route::put('preqrounds/{Id}', [PrequalificationPeriodController::class, 'update'])->name('preqrounds.update');
Route::delete('preqrounds/{Id}', [PrequalificationPeriodController::class, 'destroy'])->name('preqrounds.destroy');


//Route::resource('preqcriteria', PrequalificationCriteriaSetupController::class);
Route::get('preqcriteria', [PrequalificationCriteriaSetupController::class, 'index'])->name('preqcriteria.index');
Route::get('preqcriteria/criteria/{round_id}/{section_id}', [PrequalificationCriteriaSetupController::class, 'getCriteriabySection'])->name('getCriteriabySection');
Route::get('preqcriteria/section/create', [PrequalificationCriteriaSetupController::class, 'create'])->name('preqcriteria.create');
Route::post('preqcriteria/store', [PrequalificationCriteriaSetupController::class, 'store'])->name('preqcriteria.store');
Route::get('preqcriteria/{Id}', [PrequalificationCriteriaSetupController::class, 'show'])->name('preqcriteria.show');
Route::get('preqcriteria/edit/{Id}', [PrequalificationCriteriaSetupController::class, 'edit'])->name('preqcriteria.edit');
Route::put('preqcriteria/update/{id}', [PrequalificationCriteriaSetupController::class, 'update'])->name('preqcriteria.update');
Route::delete('preqcriteria/destroy/{id}', [PrequalificationCriteriaSetupController::class, 'destroy'])->name('preqcriteria.destroy');


Route::resource('supplierslist', SupplierListingController::class);
 Route::resource('preqapplications', PrequalificationApplicationsController::class);
 Route::resource('preqevaluation', PrequalificationEvaluationController::class);
 Route::resource('preqevalapproval', PrequalificationEvalAprovalController::class);
 Route::resource('preqsuppliers', PrequalifiedSuppliersController::class);
Route::resource('bidresponsiveness', TenderBidResponsivenessController::class);
Route::get('bidresponsiveness/create/{tenderSupplier}', [TenderBidResponsivenessController::class, 'create'])->name('bidresponsiveness.create');

Route::get('/awards-tender/view/{id}', [AwardsController::class, 'view_tender'])->name('awards.view');
Route::get('/awards-rfq/view/{id}', [AwardsController::class, 'view_rfq'])->name('awards.view');

Route::resource('procawards', AwardsController::class);





