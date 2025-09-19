<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Procurement\ApprovalSetupController;
use App\Http\Controllers\Procurement\AwardsController;
use App\Http\Controllers\Procurement\BidEvaluationController;
use App\Http\Controllers\Procurement\BidScoreConsolidationController;
use App\Http\Controllers\Procurement\CalenderBasedController;
use App\Http\Controllers\Procurement\ConsolidatedDashboardController;
use App\Http\Controllers\Procurement\ContractsController;
use App\Http\Controllers\Procurement\ContractsLifecycleController;
use App\Http\Controllers\Procurement\CriteriaController;
use App\Http\Controllers\Procurement\DelayedItemsController;
use App\Http\Controllers\Procurement\DeliveryController;
use App\Http\Controllers\Procurement\DepartmentNeedApprovalController;
use App\Http\Controllers\Procurement\DepartmentNeedsController;
use App\Http\Controllers\Procurement\EngagedAuditorController;
use App\Http\Controllers\Procurement\EvaluationCriteriaController;
use App\Http\Controllers\Procurement\EvaluatorDashboardController;
use App\Http\Controllers\Procurement\GoodsReceiptController;
use App\Http\Controllers\Procurement\InspectionController;
use App\Http\Controllers\Procurement\MapToBudgetController;
use App\Http\Controllers\Procurement\ModeTimelineController;
use App\Http\Controllers\Procurement\PlanApprovalInboxController;
use App\Http\Controllers\Procurement\PlanEditController;
use App\Http\Controllers\Procurement\PlanExectionDashboardController;
use App\Http\Controllers\Procurement\PlanFromNeedsController;
use App\Http\Controllers\Procurement\PlanManualInputController;
use App\Http\Controllers\Procurement\PlanvsActualController;
use App\Http\Controllers\Procurement\PrequalificationApplicationsController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationCriteriaSetupController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationEvalAprovalController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationApplicationController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationEvaluationController;
use App\Http\Controllers\Procurement\PrequalificationPeriodController;
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
use App\Http\Controllers\Procurement\RFQCommitteeController;
use App\Http\Controllers\Procurement\RFQController;
use App\Http\Controllers\Procurement\RFQCriteriaController;
use App\Http\Controllers\Procurement\RFQEvaluationController;
use App\Http\Controllers\Procurement\RFQLinesController;
use App\Http\Controllers\Procurement\RFQResponseController;
use App\Http\Controllers\Procurement\RFQSectionController;
use App\Http\Controllers\Procurement\RFQSettingCriteriaController;
use App\Http\Controllers\Procurement\RFQSettingSectionController;
use App\Http\Controllers\Procurement\SasraAuditorController;
use App\Http\Controllers\Procurement\SectionController;
use App\Http\Controllers\Procurement\RFQSettingController;
use App\Http\Controllers\Procurement\SubmitForApprovalController;
use App\Http\Controllers\Procurement\SupplierController;
use App\Http\Controllers\Procurement\SupplierCategoryApiController;
// use App\Http\Controllers\Procurement\SupplierListingController;
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
    Route::get('procurementplan/details/{id}', [RequisitionsController::class, 'getPlanDetails'])
        ->name('procurement.plan.details');
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
    Route::get('/purchase-order/rfq-items/{rfqId}', [PurchaseOrderController::class, 'getRFQItems'])->name('purchase-order.rfq-items');
    Route::get('/purchase-order/items/{item}', [PurchaseOrderController::class, 'getItemDetails'])->name('purchase-order.item-details');
    Route::get('/supplier-categories', [SupplierCategoryApiController::class, 'index']);
    Route::get('/purchase-order/prequalified-suppliers/{categoryId}', [PurchaseOrderController::class, 'prequalifiedSuppliersByCategory'])->name('purchase-order.prequalified-suppliers');
    Route::get('/purchase-order/awarded-rfqs', [PurchaseOrderController::class, 'getAwardedRFQs'])->name('purchase-order.awarded-rfqs');

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
    Route::get('/requisition/{requisitionId}/categories', [RFQLinesController::class, 'getRequisitionCategories']);
    Route::get('/rfq/{rfqId}/lines/create', [RFQLinesController::class, 'create'])->name('rfqlines.create');


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
    Route::get('/rfqresponses/find-existing', [RFQResponseController::class, 'findExisting']);

    // RFQ Evaluation routes
    Route::get('/rfq-evaluations', [RFQEvaluationController::class, 'index'])->name('evaluations.index');
    Route::get('/rfq-evaluations/create', [RFQEvaluationController::class, 'create'])->name('evaluations.create');
    Route::post('/procurement/rfq-evaluations/store', [RFQEvaluationController::class, 'store'])->name('evaluations.store');
    Route::get('/rfq-suppliers/{rfqId}', [RFQResponseController::class, 'getSuppliers']);

    // Consolidated scoring view and award action
    Route::get('/rfq-evaluations/consolidated/{rfq}', [RFQEvaluationController::class, 'consolidated'])->name('evaluations.consolidated');
    Route::post('/rfq-evaluations/{rfq}/award/{supplier}', [RFQEvaluationController::class, 'awardSupplier'])->name('evaluations.award');

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

    Route::resource('tenderopening', TenderOpeningController::class);
    Route::resource('tenderdecrypt', TenderDecryptController::class);
    Route::resource('tendercommittee', TenderCommitteeController::class);
    Route::post('/store-tender-committee', [TenderCommitteeController::class, 'membersAdd'])->name('tendercommittee.save');
    Route::resource('memberresponse', TenderAcceptController::class);
    Route::resource('assignrole', TenderAssignRoleController::class);
    Route::resource('evaluationcriteria', EvaluationCriteriaController::class);
    Route::resource('bidevaluation', BidEvaluationController::class);
    Route::resource('evaluationdashboard', EvaluatorDashboardController::class);
    Route::resource('bidscores', BidScoreConsolidationController::class);
    Route::get('bidscores/{tenderId}/section-drilldown', [BidScoreConsolidationController::class, 'sectionDrilldown'])->name('bidscores.section-drilldown');
    Route::get('bidscores/{tenderId}/evaluator-drilldown', [BidScoreConsolidationController::class, 'evaluatorDrilldown'])->name('bidscores.evaluator-drilldown');
    Route::get('bidscores/{tenderId}/{supplierId}/drilldown', [BidScoreConsolidationController::class, 'show'])->name('bidscores.drilldown');
    Route::resource('procurementreports', ProcurementReportsController::class);

    //Tender Creteria setup
    Route::resource('sections', SectionController::class)->except(['update', 'destroy']);
    Route::resource('criterias', CriteriaController::class)
        ->except(['update', 'destroy'])
        ->names([
            'index' => 'procurement.criterias.index',
            'show' => 'procurement.criterias.show',
            'store' => 'procurement.criterias.store',
        ]);
    Route::resource('tenderevaluations',  TenderEvaluationsController::class);
    Route::post("/store-tender-sections",  [TenderEvaluationsController::class,  'tenderSections'])->name('store-tender-sections');
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


    //Procurement plan Approval
    Route::resource('approvals/department-need', DepartmentNeedApprovalController::class)->only([
        'index',
        'show',
        'update',
        'destroy'
    ])->names([
        'index' => 'department-need-approval.index',
        'show' => 'department-need-approval.show',
        'update' => 'department-need-approval.update',
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
    Route::get('/Procurement-Plan-Submission', [ProcurementSubmitPlanController::class, 'index'])->name('Procurement-Plan-Submission.index');
    Route::get('/Procurement-Plan-Submission/create/{PlanId}', [ProcurementSubmitPlanController::class, 'create'])->name('Procurement-Plan-Submission.create');
    //Route::put('/Procurement-Plan-Submission', [ProcurementSubmitPlanController::class, 'store'])->name('Procurement-Plan-Submission.store');
    Route::put('/Procurement-Plan-Submission/{plan}', [ProcurementSubmitPlanController::class, 'update'])->name('Procurement-Plan-Submission.update');

    //Procurement Plan, Plan Consolidation
    // Route::resource('procurementplandetails', ProcurementPlanDetailController::class);

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

    // Enhanced Tender Clarification Management
    Route::get('/tenderclarification', [TenderclarificationController::class, 'index'])->name('tenderclarification.index');
    Route::get('/tenderclarification/pending', [TenderclarificationController::class, 'pending'])->name('tenderclarification.pending');
    Route::get('/tenderclarifications/{clarification_id}/create', [TenderclarificationController::class, 'create'])->name('tenderclarification.create');
    Route::patch('/tenderclarification/update', [TenderclarificationController::class, 'update'])->name('tenderclarification.update');
    Route::get('/clarifications/edit', [TenderclarificationController::class, 'edit'])->name('tenderclarification.edit');
    Route::post('/tenderclarification/bulk-action', [TenderclarificationController::class, 'bulkAction'])->name('tenderclarification.bulk-action');

    //Bid Submission
    // Use resourceful routes for tender submissions. Custom manual view/edit URIs remain below.
    Route::resource('tendersubmission', TenderSubmissionController::class);
    // Custom manual routes (unique names) - keep these if you need different URIs for manual submissions
    Route::get('/bid-submission/manual/{Id}/view', [TenderSubmissionController::class, 'view'])->name('tendersubmission.view');
    Route::get('/bid-submission/manual/{Id}/edit', [TenderSubmissionController::class, 'edit'])->name('tendersubmission.manual.edit');

    // Bid Opening Ceremony Routes
    Route::post('/tender-opening/start-ceremony', [TenderOpeningController::class, 'startCeremony'])->name('tender-opening.start-ceremony');
    Route::post('/tender-opening/open-bid/{submissionId}', [TenderOpeningController::class, 'openIndividualBid'])->name('tender-opening.open-bid');
    Route::get('/tender-opening/bid-details/{submissionId}', [TenderOpeningController::class, 'showBidDetails'])->name('tender-opening.bid-details');
    Route::get('/tender-opening/read-out/{submissionId}', [TenderOpeningController::class, 'showReadOutSummary'])->name('tender-opening.read-out');
    Route::post('/tender-opening/complete-ceremony/{tenderRef}', [TenderOpeningController::class, 'completeCeremony'])->name('tender-opening.complete-ceremony');
    Route::get('/tender-opening/access-documents/{submissionId}', [TenderOpeningController::class, 'accessDocuments'])->name('tender-opening.access-documents');
    Route::get('/tender-opening/download/{submissionId}/{documentIndex}', [TenderOpeningController::class, 'downloadDocument'])->name('tender-opening.download-document');

    // Bid Responsiveness Check Routes
    Route::get('/bid-responsiveness', [\App\Http\Controllers\Procurement\BidResponsivenessController::class, 'index'])->name('bid-responsiveness.index');
    Route::post('/bid-responsiveness/{bidId}/check', [\App\Http\Controllers\Procurement\BidResponsivenessController::class, 'checkResponsiveness'])->name('bid-responsiveness.check');
    Route::post('/bid-responsiveness/{bidId}/detailed-check', [\App\Http\Controllers\Procurement\BidResponsivenessController::class, 'detailedResponsivenessCheck'])->name('bid-responsiveness.detailed-check');
    Route::post('/bid-responsiveness/bulk-check', [\App\Http\Controllers\Procurement\BidResponsivenessController::class, 'bulkCheck'])->name('bid-responsiveness.bulk-check');
    Route::get('/bid-responsiveness/criteria', [\App\Http\Controllers\Procurement\BidResponsivenessController::class, 'getCriteria'])->name('bid-responsiveness.criteria');
    Route::get('/bid-responsiveness/{tenderRef}/report', [\App\Http\Controllers\Procurement\BidResponsivenessController::class, 'exportReport'])->name('bid-responsiveness.report');
    
    // Drill-down functionality for bid details and documents
    Route::get('/bid-responsiveness/{bidId}/details', [\App\Http\Controllers\Procurement\BidResponsivenessController::class, 'showBidDetails'])->name('bid-responsiveness.bid-details');
    Route::get('/bid-responsiveness/{bidId}/document/{documentId}', [\App\Http\Controllers\Procurement\BidResponsivenessController::class, 'viewDocument'])->name('bid-responsiveness.view-document');

    // Tender Section Management for Evaluation Setup
    Route::get('/tender-sections', [\App\Http\Controllers\Procurement\TenderSectionController::class, 'index'])->name('tender-sections.index');
    Route::get('/tender-sections/assign', [\App\Http\Controllers\Procurement\TenderSectionController::class, 'show'])->name('tender-sections.show');
    Route::post('/tender-sections', [\App\Http\Controllers\Procurement\TenderSectionController::class, 'store'])->name('tender-sections.store');
    Route::get('/tender-sections/{tenderId}/api', [\App\Http\Controllers\Procurement\TenderSectionController::class, 'getTenderSections'])->name('tender-sections.api');
    Route::delete('/tender-sections/{tenderId}', [\App\Http\Controllers\Procurement\TenderSectionController::class, 'destroy'])->name('tender-sections.destroy');

    // Enhanced Evaluator Dashboard Routes
    Route::get('/evaluator/tender/{tenderId}/evaluation', [\App\Http\Controllers\Procurement\EvaluatorDashboardController::class, 'showTenderEvaluation'])->name('evaluator.tender-evaluation');

    // Section-based Evaluation Routes
    Route::get('/evaluation/{bidId}/form', [\App\Http\Controllers\Procurement\TenderEvaluationController::class, 'showEvaluationForm'])->name('evaluation.form');
    Route::post('/evaluation/{bidId}/submit', [\App\Http\Controllers\Procurement\TenderEvaluationController::class, 'submitEvaluation'])->name('evaluation.submit');
    Route::get('/evaluation/tender/{tenderId}/summary', [\App\Http\Controllers\Procurement\TenderEvaluationController::class, 'getEvaluationSummary'])->name('evaluation.summary');

    // Bid Opening Ceremony Routes (Enhanced)
    Route::get('/bid-opening', [\App\Http\Controllers\Procurement\BidOpeningCeremonyController::class, 'index'])->name('bid-opening.index');
    Route::post('/bid-opening/start-ceremony', [\App\Http\Controllers\Procurement\BidOpeningCeremonyController::class, 'startCeremony'])->name('bid-opening.start-ceremony');
    Route::get('/bid-opening/{submissionId}/documents', [\App\Http\Controllers\Procurement\BidOpeningCeremonyController::class, 'accessDocuments'])->name('bid-opening.documents');
    Route::get('/bid-opening/{submissionId}/download/{documentIndex}', [\App\Http\Controllers\Procurement\BidOpeningCeremonyController::class, 'downloadDocument'])->name('bid-opening.download');
    Route::get('/bid-opening/{tenderRef}/summary', [\App\Http\Controllers\Procurement\BidOpeningCeremonyController::class, 'generateSummary'])->name('bid-opening.summary');

    // Bid Evaluation Routes
    Route::get('/bid-evaluation', [\App\Http\Controllers\Procurement\BidEvaluationController::class, 'index'])->name('bid-evaluation.index');
    Route::post('/bid-evaluation/{bidId}/scores', [\App\Http\Controllers\Procurement\BidEvaluationController::class, 'updateScores'])->name('bid-evaluation.scores');
    Route::get('/bid-evaluation/{tenderRef}/report', [\App\Http\Controllers\Procurement\BidEvaluationController::class, 'generateReport'])->name('bid-evaluation.report');

    //procurement Consolidation
    Route::get('/dashboard', [ConsolidatedDashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/show/{needId}', [ConsolidatedDashboardController::class, 'show'])->name('dashboard.show');
    Route::get('/procurement/dashboard/export', [ConsolidatedDashboardController::class, 'exportExcel'])->name('dashboard.export');

    //Route::resource('procurementplanmaintain', ProcurementPlanMaintainController::class);
    Route::resource('procurementplanmaintain', ProcurementPlanMaintainController::class)->except(['show', 'store']);
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
// Route::get('preqrounds', [PrequalificationPeriodController::class, 'index'])->name('preqrounds.index');
// Route::get('preqrounds/create', [PrequalificationPeriodController::class, 'create'])->name('preqrounds.create');
// Route::post('preqrounds', [PrequalificationPeriodController::class, 'store'])->name('preqrounds.store');
// Route::get('preqrounds/{Id}', [PrequalificationPeriodController::class, 'show'])->name('preqrounds.show');
// Route::get('preqrounds/edit/{Id}', [PrequalificationPeriodController::class, 'edit'])->name('preqrounds.edit');
// Route::put('preqrounds/{Id}', [PrequalificationPeriodController::class, 'update'])->name('preqrounds.update');
// Route::delete('preqrounds/{Id}', [PrequalificationPeriodController::class, 'destroy'])->name('preqrounds.destroy');


//Route::resource('preqcriteria', PrequalificationCriteriaSetupController::class);
Route::get('preqcriteria', [PrequalificationCriteriaSetupController::class, 'index'])->name('preqcriteria.index');
Route::get('preqcriteria/criteria/{round_id}/{section_id}', [PrequalificationCriteriaSetupController::class, 'getCriteriabySection'])->name('getCriteriabySection');
Route::get('preqcriteria/section/create', [PrequalificationCriteriaSetupController::class, 'create'])->name('preqcriteria.create');
Route::post('preqcriteria/store', [PrequalificationCriteriaSetupController::class, 'store'])->name('preqcriteria.store');
Route::get('preqcriteria/{Id}', [PrequalificationCriteriaSetupController::class, 'show'])->name('preqcriteria.show');
Route::get('preqcriteria/edit/{Id}', [PrequalificationCriteriaSetupController::class, 'edit'])->name('preqcriteria.edit');
Route::put('preqcriteria/update/{id}', [PrequalificationCriteriaSetupController::class, 'update'])->name('preqcriteria.update');
Route::delete('preqcriteria/destroy/{id}', [PrequalificationCriteriaSetupController::class, 'destroy'])->name('preqcriteria.destroy');

// Prequalification Routes (ModuleIDs 303200-303240)
Route::prefix('prequalification')->name('prequalification.')->group(function () {
    // Prequalification Rounds (ModuleID 303210)
    Route::get('rounds', [PrequalificationApplicationController::class, 'apiIndex'])->name('rounds.index');
    Route::get('rounds/{round}', [PrequalificationApplicationController::class, 'apiShow'])->name('rounds.show');
    
    // Supplier Applications (ModuleID 303230)
    Route::post('applications', [PrequalificationApplicationController::class, 'store'])->name('applications.store');
    
    // Evaluation & Approval (ModuleID 303240) - handled by existing preqevaluation routes below
});

// Evaluator Routes (for ModuleID 303240)
Route::middleware('role:evaluator')->group(function () {
    Route::controller(PrequalificationEvaluationController::class)->group(function () {
        Route::post('evaluator/evaluations', 'store');
    });
});

// Removed this; kinda rendundant
// Route::resource('supplierslist', SupplierListingController::class);

// Route::resource('preqapplications', PrequalificationApplicationsController::class);
Route::resource('preqevaluation', PrequalificationEvaluationController::class);
Route::resource('preqevalapproval', PrequalificationEvalAprovalController::class);
Route::resource('preqsuppliers', PrequalifiedSuppliersController::class);

// Legacy routes - redirect to new enhanced responsiveness check system
Route::get('bidresponsiveness', function () {
    return redirect()->route('bid-responsiveness.index');
})->name('bidresponsiveness.index');

// Keep the old system for backward compatibility but use the old controller
Route::resource('bidresponsiveness-legacy', TenderBidResponsivenessController::class)->except(['create', 'index']);
Route::get('bidresponsiveness/create/{tenderSupplier}', [TenderBidResponsivenessController::class, 'create'])->name('bidresponsiveness.create');
Route::post('bidresponsiveness/bulk', [TenderBidResponsivenessController::class, 'bulkUpdate'])->name('bidresponsiveness.bulk');

Route::get('/awards-tender/view/{id}', [AwardsController::class, 'view_tender'])->name('awards.tender');
Route::get('/awards-rfq/view/{id}', [AwardsController::class, 'view_rfq'])->name('awards.rfq');
Route::get('/awards/unified/{id}', [AwardsController::class, 'showUnifiedAward'])->name('awards.unified');
Route::post('/awards/switch-type', [AwardsController::class, 'switchType'])->name('awards.switch-type');
Route::post('/awards/{award}/approve', [AwardsController::class, 'approve'])->name('awards.approve');
Route::post('/awards/{award}/reject', [AwardsController::class, 'reject'])->name('awards.reject');

// Award creation from consolidated scores
Route::get('awards/create-from-consolidation/{tenderId}', [AwardsController::class, 'createFromConsolidation'])->name('awards.create-from-consolidation');

Route::resource('procawards', AwardsController::class);

// Contracts - Core CRUD
Route::get('contracts', [ContractsController::class, 'index'])->name('contracts.index');
Route::get('contracts/create', [ContractsController::class, 'create'])->name('contracts.create');
Route::post('contracts', [ContractsController::class, 'store'])->name('contracts.store');
Route::get('contracts/{id}/view', [ContractsController::class, 'view'])->name('contracts.view');
Route::get('contracts/{id}/edit', [ContractsController::class, 'edit'])->name('contracts.edit');
Route::put('contracts/{id}', [ContractsController::class, 'update'])->name('contracts.update');

// Contracts - Approval Queue
Route::get('contracts/approval-queue', [ContractsController::class, 'approvalQueue'])->name('contracts.approve_index');
Route::get('contracts/{id}/approve', [ContractsController::class, 'approve'])->name('contracts.approve');
Route::post('contracts/{id}/approve', [ContractsController::class, 'submitApproval'])->name('contracts.approve.submit');

// Contracts - LPO Link
Route::get('contracts/{id}/lpo', [ContractsController::class, 'linkLPO'])->name('contracts.lpo.link');

// Contracts Lifecycle
Route::get('contracts/lifecycle', [ContractsLifecycleController::class, 'index'])->name('contractcycle.index');
Route::get('contracts/lifecycle/{id}/view', [ContractsLifecycleController::class, 'view'])->name('contractcycle.view');
Route::get('contracts/lifecycle/{id}/amend', [ContractsLifecycleController::class, 'amend'])->name('contractcycle.amend');
Route::post('contracts/lifecycle/{id}/amend', [ContractsLifecycleController::class, 'submitAmendment'])->name('contractcycle.amend.submit');
Route::get('contracts/lifecycle/{id}/terminate', [ContractsLifecycleController::class, 'terminate'])->name('contractcycle.terminate');
Route::post('contracts/lifecycle/{id}/terminate', [ContractsLifecycleController::class, 'submitTermination'])->name('contractcycle.terminate.submit');
Route::get('contracts/lifecycle/{id}/execute', [ContractsLifecycleController::class, 'monitorExecution'])->name('contractcycle.execution');


Route::resource('deliverynotes', DeliveryController::class);
Route::resource('goodsinspection', InspectionController::class);

Route::post('/rfqcommittee', [RFQCommitteeController::class, 'store'])->name('rfqcommittee.store');

//RFQ Criteria Setup
Route::prefix('procurement/rfq')->group(function () {
    //Route::resource('sections', RFQSettingSectionController::class)->names('rfqsettingsections');
    Route::resource('criterias', RFQSettingCriteriaController::class)->except(['show', 'store'])->names('rfqsettingcriterias');

    Route::get('/procurement/rfqcriteriasetup/evaluations', [RFQSectionController::class, 'evaluationSetup'])->name('rfqcriteriasetup.evaluations');
});
Route::post('/procurement/rfqcriteriasetup/evaluations/save', [RFQSectionController::class, 'saveEvaluation'])->name('rfqcriteriasetup.evaluations.save');
Route::get('/sections/info', [RFQSettingSectionController::class, 'index'])->name('rfqsettingsections.index');
Route::get('/procurement/rfq/criterias/section/{id}', [RFQSettingCriteriaController::class, 'show'])->name('rfqsettingcriterias.show');
Route::post('/sectionsetting', [RFQSettingSectionController::class, 'store'])->name('rfqsettingsections.store');
Route::post('/criteria', [RFQSettingCriteriaController::class, 'store'])->name('rfqsettingcriterias.store');
Route::get('/sections/{id}', [RFQSettingSectionController::class, 'show'])->name('rfqsettingsections.show');


Route::prefix('procurement/rfq')->group(function () {
    Route::resource('procurement/rfq/criterias', RFQCriteriaController::class)->only(['store', 'show', 'destroy']);
    Route::resource('procurement/rfq/sections', RFQSectionController::class);
    Route::resource('procurement/rfq/criterias', RFQCriteriaController::class)->except(['store', 'show'])->names('rfqcriterias');
    Route::resource('procurement/rfq/sections', RFQSectionController::class)->names('rfqsections');
});
Route::get('procurement/rfq/criterias/setup/{rfqId}', [RFQCriteriaController::class, 'show'])->name('rfqcriterias.show');
Route::post('procurement/rfq/criterias', [RFQCriteriaController::class, 'store'])->name('rfqcriterias.store');
Route::get('/procurement/committee-references/{type}', [TenderCommitteeController::class, 'getReferences']);
Route::get('procurement/tendercommittee/{id}/{type}', [TenderCommitteeController::class, 'show'])->name('tendercommittee.manual.show');
Route::get('/procurement/rfq-committee-member/{rfqId}', [RFQEvaluationController::class, 'getCommitteeMemberInfo']);



//// RFQ Criteria Setup
//Route::prefix('procurement/rfq')->group(function () {
//    // Routes for RFQSettingSectionController
//    Route::get('/sections/info', [RFQSettingSectionController::class, 'index'])->name('rfqsettingsections.index');
//    Route::post('/sections', [RFQSettingSectionController::class, 'store'])->name('rfqsettingsections.store');
//    Route::get('/sections/{id}/edit', [RFQSettingSectionController::class, 'edit'])->name('rfqsettingsections.edit');
//    Route::put('/sections/{id}', [RFQSettingSectionController::class, 'update'])->name('rfqsettingsections.update');
//    Route::delete('/sections/{id}', [RFQSettingSectionController::class, 'destroy'])->name('rfqsettingsections.destroy');
//
//    // Routes for RFQSettingCriteriaController
//    Route::get('/criterias/section/{id}', [RFQSettingCriteriaController::class, 'show'])->name('rfqsettingcriterias.show');
//    Route::post('/procurement/criterias', [RFQSettingCriteriaController::class, 'store'])->name('rfqsettingcriterias.store');
//    Route::get('/criterias/{id}/edit', [RFQSettingCriteriaController::class, 'edit'])->name('rfqsettingcriterias.edit');
//    Route::put('/procurement/criterias/{id}', [RFQSettingCriteriaController::class, 'update'])->name('rfqsettingcriterias.update');
//    Route::delete('/criterias/{id}', [RFQSettingCriteriaController::class, 'destroy'])->name('rfqsettingcriterias.destroy');
//
//    // Routes for RFQSectionController
//    Route::get('/rfqcriteriasetup/evaluations', [RFQSectionController::class, 'evaluationSetup'])->name('rfqcriteriasetup.evaluations');
//    Route::post('/rfqcriteriasetup/evaluations/save', [RFQSectionController::class, 'saveEvaluation'])->name('rfqcriteriasetup.evaluations.save');
//    Route::get('/sections/{id}/edit', [RFQSectionController::class, 'edit'])->name('rfqsections.edit');
//    Route::put('/sections/{id}', [RFQSectionController::class, 'update'])->name('rfqsections.update');
//    Route::delete('/sections/{id}', [RFQSectionController::class, 'destroy'])->name('rfqsections.destroy');
//});
//
//Route::prefix('procurement/rfq')->group(function () {
//    // Routes for RFQCriteriaController
//    Route::get('/criterias/setup/{rfqId}', [RFQCriteriaController::class, 'show'])->name('rfqcriterias.show');
//    Route::post('/criterias', [RFQCriteriaController::class, 'store'])->name('rfqcriterias.store');
//    Route::get('/criterias/{id}/edit', [RFQCriteriaController::class, 'edit'])->name('rfqcriterias.edit');
//    Route::put('/criterias/{id}', [RFQCriteriaController::class, 'update'])->name('rfqcriterias.update');
//    Route::delete('/criterias/{id}', [RFQCriteriaController::class, 'destroy'])->name('rfqcriterias.destroy');
//
//    // Routes for RFQSectionController (already defined above, no duplicates needed)
//});
//
//// Additional procurement routes
//Route::get('/procurement/committee-references/{type}', [TenderCommitteeController::class, 'getReferences']);
//Route::get('/procurement/tendercommittee/{id}/{type}', [TenderCommitteeController::class, 'show'])->name('tendercommittee.show');
//Route::get('/procurement/rfq-committee-member/{rfqId}', [RFQEvaluationController::class, 'getCommitteeMemberInfo']);
