<?php

namespace App\Providers;

use App\Http\Middleware\Authenticate;
use App\Models\Auth\ModelRole;
use App\Models\Auth\PersonalAccessToken as CustomPersonalAccessToken;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\BR\Account;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetActivity;
use App\Models\Budget\BudgetActivityMaster;
use App\Models\Budget\BudgetDriver;
use App\Models\Budget\BudgetDriverMaster;
use App\Models\Budget\BudgetDriverProjections;
use App\Models\Budget\BudgetGLAccount;
use App\Models\Budget\BudgetGLAccountSubType;
use App\Models\Budget\BudgetLine;
use App\Models\Budget\BudgetLineProductTypes;
use App\Models\Budget\BudgetLinesGLAccount;
use App\Models\Budget\BudgetManualEntryAllocations;
use App\Models\Budget\BudgetMonthlyAllocation;
use App\Models\Budget\BudgetMonthlyProjectionAllocation;
use App\Models\Budget\BudgetPeriods;
use App\Models\Budget\BudgetPeriodTypes;
use App\Models\Budget\BudgetProduct;
use App\Models\Budget\BudgetProductType;
use App\Models\Budget\BudgetScenarioPlanning;
use App\Models\Budget\BudgetTopDown;
use App\Models\Budget\BudgetTopDownData;
use App\Models\Communication\Call;
use App\Models\Communication\Comment;
use App\Models\Communication\Email;
use App\Models\Core\Branch;
use App\Models\Core\CategoryMaster;
use App\Models\Core\Report;
use App\Models\Core\SpecialPermission;
use App\Models\Core\Task;
use App\Models\Core\Workflow;
use App\Models\CRM\Campaign;
use App\Models\CRM\CampaignParty;
use App\Models\CRM\Contact;
use App\Models\CRM\Discussion;
use App\Models\CRM\Lead;
use App\Models\CRM\MarketingPlanner;
use App\Models\CRM\Meeting;
use App\Models\CRM\Notes;
use App\Models\CRM\ProductDevelopment;
use App\Models\CRM\Review;
use App\Models\CRM\Schedule;
use App\Models\CRM\Social;
use App\Models\CRM\Survey;
use App\Models\CRM\Ticket;
use App\Models\DMS\DMSSignature;
use App\Models\DMS\DMSTags;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentAttribute;
use App\Models\DMS\DocumentCheckOut;
use App\Models\DMS\DocumentLegalHold;
use App\Models\DMS\DocumentRelation;
use App\Models\DMS\DocumentSignature;
use App\Models\DMS\DocumentTaggingRules;
use App\Models\DMS\DocumentTags;
use App\Models\DMS\DocumentValidation;
use App\Models\DMS\DocumentValidationAttribute;
use App\Models\DMS\DocumentValidationType;
use App\Models\DMS\DocumentVersion;
use App\Models\DMS\Image;
use App\Models\DMS\LegalHold;
use App\Models\DMS\Repository;
use App\Models\Finance\FinanceCDNotes;
use App\Models\Finance\FinanceCreditManagement;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\Finance\FinanceGLMapping;
use App\Models\Finance\FinanceGLSubAccountTypes;
use App\Models\Finance\FinanceGLTypeGroup;
use App\Models\Finance\FinanceInvoice;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Finance\FinanceInvoiceLine;
use App\Models\Finance\FinanceJournalEntry;
use App\Models\Finance\FinanceJournalLines;
use App\Models\Finance\FinanceModuleTransactions;
use App\Models\Finance\FinanceReceipt;
use App\Models\Finance\FinanceTaxType;
use App\Models\Finance\FinanceTransaction;
use App\Models\Finance\FinanceTransactionTypes;
use App\Models\Finance\RecurrentJournal;
use App\Models\Finance\ReverseJournalEntry;
use App\Models\Finance\TaxJurisdiction;
use App\Models\Fleet\ContractedDriver;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\FleetInspectionSchedule;
use App\Models\Fleet\FleetInsuranceTracker;
use App\Models\Fleet\FleetMaintenanceSchedule;
use App\Models\Fleet\FleetRepairLog;
use App\Models\Fleet\FleetRoutePlan;
use App\Models\Fleet\FleetServiceAlert;
use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetVehicleInspection;
use App\Models\Fleet\FleetVehicleRequest;
use App\Models\FleetManagement\FleetMake;
use App\Models\FleetManagement\FleetModel;
use App\Models\HRM\Committee;
use App\Models\HRM\Department;
use App\Models\HRM\Employee;
use App\Models\Insurance\BancassuranceBeneficiaries;
use App\Models\Insurance\BancassuranceClaim;
use App\Models\Insurance\BancassuranceCommissionRule;
use App\Models\Insurance\BancassuranceCustomer;
use App\Models\Insurance\BancassuranceCustomerContact;
use App\Models\Insurance\BancassurancePolicy;
use App\Models\Insurance\BancassurancePremiumPayments;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\Insurance\BancassuranceUnderwriting;
use App\Models\Insurance\InsuranceProduct;
use App\Models\Insurance\InsuranceProductRider;
use App\Models\Insurance\InsuranceProvider;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Inventory\InventoryHoldReview;
use App\Models\Inventory\InventoryType;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemType;
use App\Models\Inventory\PriceManagement;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\Store;
use App\Models\Inventory\StockTake;
use App\Models\Inventory\TransactionReceipt;
use App\Models\Procurement\Tender;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\UOMConversion;
use App\Models\Legal\LegalCase;
use App\Models\Legal\LegalCaseCounsel;
use App\Models\Legal\LegalCaseEvidence;
use App\Models\Legal\LegalCaseOutcome;
use App\Models\Legal\LegalClause;
use App\Models\Legal\LegalDocument;
use App\Models\Legal\LegalIntellectualProperty;
use App\Models\Legal\LegalObligation;
use App\Models\Legal\LegalSearchRequest;
use App\Models\Legal\LegalTemplate;
use App\Models\Legal\LoanSecurity;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\ProcurementPlan;
use App\Models\Procurement\DepartmentNeed;
use App\Models\Procurement\Order;
use App\Models\Procurement\PlanLineItem;
use App\Models\Procurement\ProcurementMethod;
use App\Models\Procurement\RequisitionLine;
use App\Models\Procurement\Requisitions;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQLine;
use App\Models\Procurement\SchedulePlan;
use App\Models\Procurement\Section;
use App\Models\PropertyManagement\PropertyAttachments;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyLeaseRenewal;
use App\Models\PropertyManagement\PropertyLeaseSchedule;
use App\Models\PropertyManagement\PropertyLeaseTermination;
use App\Models\PropertyManagement\PropertyMaintenanceAssign;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;
use App\Models\PropertyManagement\PropertyMaintenanceWorkCompletion;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyReceipt;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyTenantClearance;
use App\Models\PropertyManagement\PropertyType;
use App\Models\PropertyManagement\PropertyUnit;
use App\Models\Settings\APICredential;
use App\Models\Settings\WorkFlowLimit;
use App\Models\Settings\WorkFlowStage;
use App\Models\ThirdParies\Board;
use App\Models\ThirdParies\Competitor;
use App\Models\ThirdParty\SupplierMaster;
use App\Policies\CrmBranchPolicy;
use App\Policies\DMS\DMSSignaturePolicy;
use App\Policies\DMS\DMSTagPolicy;
use App\Policies\DMS\DocumentPolicy;
use App\Policies\DMS\DocumentValidationTypePolicy;
use App\Policies\DMS\LegalHoldPolicy;
use App\Policies\DMS\RepositoryPolicy;
use App\Policies\FleetManagement\ContractedDriverPolicy;
use App\Policies\FleetManagement\FleetDriverPolicy;
use App\Policies\FleetManagement\FleetInspectionSchedulePolicy;
use App\Policies\FleetManagement\FleetInsuranceTrackerPolicy;
use App\Policies\FleetManagement\FleetMaintenanceSchedulePolicy;
use App\Policies\FleetManagement\FleetMakePolicy;
use App\Policies\FleetManagement\FleetModelPolicy;
use App\Policies\FleetManagement\FleetRepairLogPolicy;
use App\Policies\FleetManagement\FleetRoutePlanPolicy;
use App\Policies\FleetManagement\FleetServiceAlertPolicy;
use App\Policies\FleetManagement\FleetTripLogPolicy;
use App\Policies\FleetManagement\FleetVehicleInspectionPolicy;
use App\Policies\FleetManagement\FleetVehiclePolicy;
use App\Policies\FleetManagement\FleetVehicleRequestPolicy;
use App\Policies\Insurance\BancassuranceClaimPolicy;
use App\Policies\Insurance\BancassuranceCustomersBeneficiariesPolicy;
use App\Policies\Insurance\BancassuranceCustomersContactsPolicy;
use App\Policies\Insurance\BancassuranceCustomersPolicy;
use App\Policies\Insurance\BancassurancePoliciesPolicy;
use App\Policies\Insurance\BancassurancePremiumPaymentsPolicy;
use App\Policies\Insurance\BancAssuranceReferralPolicy;
use App\Policies\Insurance\BancassuranceUnderwritingPolicy;
use App\Policies\Insurance\CommissionRulePolicy;
use App\Policies\Insurance\InsuranceProductPolicy;
use App\Policies\Insurance\InsuranceProductRiderPolicy;
use App\Policies\Insurance\InsuranceProviderPolicy;
use App\Policies\Inventory\InterBranchRequisitionPolicy;
use App\Policies\Inventory\InventoryHoldReviewPolicy;
use App\Policies\Inventory\InventoryTypePolicy;
use App\Policies\Inventory\ItemCategoryPolicy;
use App\Policies\Inventory\ItemMasterListPolicy;
use App\Policies\Inventory\ItemTypePolicy;
use App\Policies\Inventory\PriceManagementPolicy;
use App\Policies\Inventory\StockAdjustmentPolicy;
use App\Policies\Inventory\StockTakePolicy;
use App\Policies\Inventory\StockItemPolicy;
use App\Policies\Inventory\StorePolicy;
use App\Policies\Inventory\TransactionReceiptPolicy;
use App\Policies\Inventory\TransactionTransferPolicy;
use App\Policies\Inventory\UnitOfMeasurePolicy;
use App\Policies\Inventory\UOMConversionPolicy;
use App\Policies\Procurement\DepartmentNeedsPolicy;
use App\Policies\Procurement\OrderPolicy;
use App\Policies\Procurement\PlanManualInputPolicy;
use App\Policies\Procurement\ProcurementMethodPolicy;
use App\Policies\Procurement\SupplierPolicy;
use App\Policies\Procurement\ProcurementPlanMaintainPolicy;
use App\Policies\Procurement\RequisitionLinesPolicy;
use App\Policies\Procurement\RequisitionPolicy;
use App\Policies\Procurement\RFQPolicy;
use App\Policies\Procurement\SchedulePlanPolicy;
use App\Policies\ProductDevelopmentPolicy;
use App\Policies\PropertyManagement\PropertyAttachmentsPolicy;
use App\Policies\PropertyManagement\PropertyCategoryPolicy;
use App\Policies\PropertyManagement\PropertyFloorPolicy;
use App\Policies\PropertyManagement\PropertyInvoicePolicy;
use App\Policies\PropertyManagement\PropertyLeaseRenewalPolicy;
use App\Policies\PropertyManagement\PropertyLeaseSchedulePolicy;
use App\Policies\PropertyManagement\PropertyLeaseTerminationPolicy;
use App\Policies\PropertyManagement\PropertyMaintenanceAssignPolicy;
use App\Policies\PropertyManagement\PropertyMaintenanceRequestPolicy;
use App\Policies\PropertyManagement\PropertyMaintenanceWorkCompletionPolicy;
use App\Policies\PropertyManagement\PropertyNewLeasePolicy;
use App\Policies\PropertyManagement\PropertyNewTenantPolicy;
use App\Policies\PropertyManagement\PropertyReceiptPolicy;
use App\Policies\PropertyManagement\PropertyRegistryPolicy;
use App\Policies\PropertyManagement\PropertyStructuralPolicy;
use App\Policies\PropertyManagement\PropertyTenantClearancePolicy;
use App\Policies\PropertyManagement\PropertyTypePolicy;
use App\Policies\PropertyManagement\PropertyUnitPolicy;
use App\Policies\RolePolicy;
use App\Policies\Procurement\ProcurementPlanPolicy;
use App\Policies\Procurement\DepartmentNeedPolicy;
use App\Policies\Procurement\ConsolidatedProcurementPlanPolicy;
use App\Policies\Procurement\PlanLineItemPolicy;
use App\Policies\Procurement\TenderSubmissionPolicy;
use App\Policies\Procurement\TenderInvitationPolicy;
use App\Policies\Procurement\BidOpeningPolicy;
use App\Policies\Procurement\RFQResponsePolicy;
use App\Policies\Procurement\ContractPolicy;
use App\Policies\Procurement\PurchaseOrderPolicy;
use App\Policies\Procurement\GoodsReceiptPolicy;
use App\Models\Procurement\BidSubmission;
use App\Models\Procurement\TenderInvitation;
use App\Models\Procurement\RFQResponse;
use App\Models\Procurement\TenderAward;
use App\Models\Procurement\Order as ProcurementOrder;
use App\Models\Procurement\GoodsReceipt;
use App\Services\Workflow\ApprovalWorkflow;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\Procurement\TenderController;
use App\Http\Controllers\Procurement\RequisitionsController;
use App\Http\Controllers\Procurement\AwardsController;
use App\Http\Controllers\Procurement\PurchaseOrderController;
use App\Services\Procurement\Requisition\RequisitionWorkflowService;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

//use App\Policies\FleetManagement\DriverPolicy;

//use App\Policies\Procurement\PrequalificationPeriodPolicy;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void

    {
        // Default binding for Department Needs
        $this->app->bind(ApprovalWorkflow::class, function ($app) {
            return new ApprovalWorkflow('DepartmentNeedsStatus', 'Status');
        });

        // 🔥 FIX: Bind Tender Workflow using contextual binding
        $this->app->when(TenderController::class)
            ->needs(ApprovalWorkflow::class)
            ->give(function () {
                return new ApprovalWorkflow(
                    'TenderApprovalStatus',      // Correct CodeID for verification
                    'ApprovalStatus'             // Status column name
                );
            });

        // Bind Requisition Workflow Service
        $this->app->singleton(RequisitionWorkflowService::class, function ($app) {
            return new RequisitionWorkflowService();
        });

        // Bind Requisitions Workflow (generic)
        $this->app->when(RequisitionsController::class)
            ->needs(ApprovalWorkflow::class)
            ->give(function () {
                return new ApprovalWorkflow(
                    'RequisitionStatus', // CodeID for requisition workflow
                    'DocStatus'          // Status column name for requisitions
                );
            });

        // Bind Awards Workflow
        $this->app->when(AwardsController::class)
            ->needs(ApprovalWorkflow::class)
            ->give(function () {
                return new ApprovalWorkflow(
                    'TenderAwardApprovalStatus',  // CodeID for tender award approval workflow
                    'AwardStatus'                 // Status column name
                );
            });

        // Bind Purchase Order Workflow
        $this->app->when(PurchaseOrderController::class)
            ->needs(ApprovalWorkflow::class)
            ->give(function () {
                return new ApprovalWorkflow(
                    'ApprovalStatus',  // CodeID for purchase order approval workflow
                    'DocStatus'        // Status column name for orders
                );
            });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(
            \Illuminate\Auth\Middleware\Authenticate::class,
            Authenticate::class
        );

        \Illuminate\Support\Facades\Blade::if('canRead', function (string $submodule) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if (!$user) return false;
            return \App\Services\Core\PermissionResolver::can($user, $submodule, 'read');
        });
        \Illuminate\Support\Facades\Blade::if('canWrite', function (string $submodule) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if (!$user) return false;
            return \App\Services\Core\PermissionResolver::can($user, $submodule, 'write');
        });
        \Illuminate\Support\Facades\Blade::if('canUpdate', function (string $submodule) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if (!$user) return false;
            return \App\Services\Core\PermissionResolver::can($user, $submodule, 'update');
        });
        \Illuminate\Support\Facades\Blade::if('canDelete', function (string $submodule) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if (!$user) return false;
            return \App\Services\Core\PermissionResolver::can($user, $submodule, 'delete');
        });
        \Illuminate\Support\Facades\Blade::if('canApprove', function (string $submodule) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if (!$user) return false;
            return \App\Services\Core\PermissionResolver::can($user, $submodule, 'approve');
        });
        Relation::morphMap([

            //Core
            SpecialPermission::getPrimaryKey() => SpecialPermission::class,
            ModelRole::getPrimaryKey() => ModelRole::class,
            'RoleId' => Role::class,
            APICredential::getPrimaryKey() => APICredential::class,
            Comment::getPrimaryKey() => Comment::class,
            Report::getPrimaryKey() => Report::class,
            Workflow::getPrimaryKey() => Workflow::class,
            WorkFlowStage::getPrimaryKey() => WorkFlowStage::class,
            WorkFlowLimit::getPrimaryKey() => WorkFlowLimit::class,

            //CRM
            Account::getPrimaryKey() => Account::class,
            Board::getPrimaryKey() => Board::class,
            Call::getPrimaryKey() => Call::class,
            Campaign::getPrimaryKey() => Campaign::class,
            CampaignParty::getPrimaryKey() => CampaignParty::class,
            Client::getPrimaryKey() => Client::class,
            Email::getPrimaryKey() => Email::class,
            Competitor::getPrimaryKey() => Competitor::class,
            Committee::getPrimaryKey() => Committee::class,
            Contact::getPrimaryKey() => Contact::class,
            DebtProduct::getPrimaryKey() => DebtProduct::class,
            Department::getPrimaryKey() => Department::class,
            Discussion::getPrimaryKey() => Discussion::class,
            Lead::getPrimaryKey() => Lead::class,
            MarketingPlanner::getPrimaryKey() => MarketingPlanner::class,
            Meeting::getPrimaryKey() => Meeting::class,
            Notes::getPrimaryKey() => Notes::class,
            ProductDevelopment::getPrimaryKey() => ProductDevelopment::class,
            Review::getPrimaryKey() => Review::class,
            Schedule::getPrimaryKey() => Schedule::class,
            Social::getPrimaryKey() => Social::class,
            Survey::getPrimaryKey() => Survey::class,
            Task::getPrimaryKey() => Task::class,
            Team::getPrimaryKey() => Team::class,
            Ticket::getPrimaryKey() => Ticket::class,
            User::getPrimaryKey() => User::class,

            //hrm
            Branch::getPrimaryKey() => Branch::class,
            Employee::getPrimaryKey() => Employee::class,

            //PROCUREMENT
            Tender::getPrimaryKey() => Tender::class,
            RFQ::getPrimaryKey() => RFQ::class,
            RFQLine::getPrimaryKey() => RFQLine::class,
            Requisitions::getPrimaryKey() => Requisitions::class,
            RequisitionLine::getPrimaryKey() => RequisitionLine::class,
            Order::getPrimaryKey() => Order::class,
            DepartmentNeed::getPrimaryKey() => DepartmentNeed::class,
            ProcurementMethod::getPrimaryKey() => ProcurementMethod::class,
            SchedulePlan::getPrimaryKey() => SchedulePlan::class,
            InterBranchRequisition::getPrimaryKey() => InterBranchRequisition::class,
            ConsolidatedProcurementPlan::getPrimaryKey() => ConsolidatedProcurementPlan::class,
            PlanLineItem::getPrimaryKey() => PlanLineItem::class,

            //iINVENTORY
            ItemMasterList::getPrimaryKey() => ItemMasterList::class,
            ItemCategories::getPrimaryKey() => ItemCategories::class,
            ItemType::getPrimaryKey() => ItemType::class,
            InventoryType::getPrimaryKey() => InventoryType::class,
            StockItem::getPrimaryKey() => StockItem::class,
            Store::getPrimaryKey() => Store::class,
            UnitOfMeasure::getPrimaryKey() => UnitOfMeasure::class,
            PriceManagement::getPrimaryKey() => PriceManagement::class,
            TransactionReceipt::getPrimaryKey() => TransactionReceipt::class,
            TransactionTransfer::getPrimaryKey() => TransactionTransfer::class,
            StockAdjustment::getPrimaryKey() => StockAdjustment::class,
            InventoryHoldReview::getPrimaryKey() => InventoryHoldReview::class,
            UOMConversion::getPrimaryKey() => UOMConversion::class,
            StockTake::getPrimaryKey() => StockTake::class,

            ///////// Budget and Analytics /////////
            BudgetActivityMaster::getPrimaryKey() => BudgetActivityMaster::class,
            BudgetLinesGLAccount::getPrimaryKey() => BudgetLinesGLAccount::class,
            BudgetGLAccount::getPrimaryKey() => BudgetGLAccount::class,
            BudgetLine::getPrimaryKey() => BudgetLine::class,
            BudgetPeriods::getPrimaryKey() => BudgetPeriods::class,
            BudgetPeriodTypes::getPrimaryKey() => BudgetPeriodTypes::class,
            BudgetScenarioPlanning::getPrimaryKey() => BudgetScenarioPlanning::class,
            BudgetProduct::getPrimaryKey() => BudgetProduct::class,
            BudgetProductType::getPrimaryKey() => BudgetProductType::class,
            BudgetDriver::getPrimaryKey() => BudgetDriver::class,
            BudgetDriverMaster::getPrimaryKey() => BudgetDriverMaster::class,
            BudgetDriverProjections::getPrimaryKey() => BudgetDriverProjections::class,
            BudgetTopDown::getPrimaryKey() => BudgetTopDown::class,
            BudgetTopDownData::getPrimaryKey() => BudgetTopDownData::class,
            BudgetActivity::getPrimaryKey() => BudgetActivity::class,
            BudgetMonthlyAllocation::getPrimaryKey() => BudgetMonthlyAllocation::class,
            Budget::getPrimaryKey() => Budget::class,
            BudgetGLAccountSubType::getPrimaryKey() => BudgetGLAccountSubType::class,
            BudgetLineProductTypes::getPrimaryKey() => BudgetLineProductTypes::class,
            BudgetMonthlyProjectionAllocation::getPrimaryKey() => BudgetMonthlyProjectionAllocation::class,
            BudgetManualEntryAllocations::getPrimaryKey() => BudgetManualEntryAllocations::class,

            //Property Management
            PropertyType::getPrimaryKey() => PropertyType::class,
            CategoryMaster::getPrimaryKey() => CategoryMaster::class,
            PropertyRegistry::getPrimaryKey() => PropertyRegistry::class,
            PropertyAttachments::getPrimaryKey() => PropertyAttachments::class,
            PropertyBlock::getPrimaryKey() => PropertyBlock::class,
            PropertyMaintenanceRequest::getPrimaryKey() => PropertyMaintenanceRequest::class,
            PropertyMaintenanceAssign::getPrimaryKey() => PropertyMaintenanceAssign::class,
            PropertyMaintenanceWorkCompletion::getPrimaryKey() => PropertyMaintenanceWorkCompletion::class,
            PropertyNewTenant::getPrimaryKey() => PropertyNewTenant::class,
            PropertyTenantClearance::getPrimaryKey() => PropertyTenantClearance::class,
            PropertyFloor::getPrimaryKey() => PropertyFloor::class,
            PropertyUnit::getPrimaryKey() => PropertyUnit::class,
            PropertyNewLease::getPrimaryKey() => PropertyNewLease::class,
            PropertyLeaseSchedule::getPrimaryKey() => PropertyLeaseSchedule::class,
            PropertyLeaseRenewal::getPrimaryKey() => PropertyLeaseRenewal::class,
            PropertyInvoice::getPrimaryKey() => PropertyInvoice::class,
            PropertyLeaseTermination::getPrimaryKey() => PropertyLeaseTermination::class,

            //DMS
            DMSTags::getPrimaryKey() => DMSTags::class,
            DMSSignature::getPrimaryKey() => DMSSignature::class,
            Document::getPrimaryKey() => Document::class,
            DocumentAttribute::getPrimaryKey() => DocumentAttribute::class,
            DocumentCheckOut::getPrimaryKey() => DocumentCheckOut::class,
            DocumentLegalHold::getPrimaryKey() => DocumentLegalHold::class,
            DocumentRelation::getPrimaryKey() => DocumentRelation::class,
            DocumentSignature::getPrimaryKey() => DocumentSignature::class,
            DocumentTaggingRules::getPrimaryKey() => DocumentTaggingRules::class,
            DocumentTags::getPrimaryKey() => DocumentTags::class,
            DocumentValidation::getPrimaryKey() => DocumentValidation::class,
            DocumentValidationAttribute::getPrimaryKey() => DocumentValidationAttribute::class,
            DocumentValidationType::getPrimaryKey() => DocumentValidationType::class,
            DocumentVersion::getPrimaryKey() => DocumentVersion::class,
            Image::getPrimaryKey() => Image::class,
            LegalHold::getPrimaryKey() => LegalHold::class,
            Repository::getPrimaryKey() => Repository::class,

            //Third Parties
            // Allow resolving morph type 'ThirdParty' used by legacy data
            'ThirdParty' => \App\Models\ThirdParty\ThirdParties::class,
            \App\Models\ThirdParty\ThirdParties::getPrimaryKey() => \App\Models\ThirdParty\ThirdParties::class,
            \App\Models\ThirdParty\SupplierMaster::getPrimaryKey() => \App\Models\ThirdParty\SupplierMaster::class,
            \App\Models\ThirdParty\ThirdPartyUser::getPrimaryKey() => \App\Models\ThirdParty\ThirdPartyUser::class,
            //Fleet Management
            // FleetMake::getPrimaryKey() => FleetMake::class,
            // FleetModel::getPrimaryKey() => FleetModel::class,
            // VehicleRegistry::getPrimaryKey() => VehicleRegistry::class,
            // DriverManagement::getPrimaryKey() => DriverManagement::class,
            FleetMake::getPrimaryKey() => FleetMake::class,
            FleetModel::getPrimaryKey() => FleetModel::class,
            FleetVehicle::getPrimaryKey() => FleetVehicle::class,
            FleetInsuranceTracker::getPrimaryKey() => FleetInsuranceTracker::class,
            FleetInspectionSchedule::getPrimaryKey() => FleetInspectionSchedule::class,
            FleetDriver::getPrimaryKey() => FleetDriver::class,
            ContractedDriver::getPrimaryKey() => ContractedDriver::class,
            FleetTripLog::getPrimaryKey() => FleetTripLog::class,
            FleetRoutePlan::getPrimaryKey() => FleetRoutePlan::class,
            FleetVehicleRequest::getPrimaryKey() => FleetVehicleRequest::class,
            FleetMaintenanceSchedule::getPrimaryKey() => FleetMaintenanceSchedule::class,
            FleetRepairLog::getPrimaryKey() => FleetRepairLog::class,
            FleetServiceAlert::getPrimaryKey() => FleetServiceAlert::class,
            FleetVehicleInspection::getPrimaryKey() => FleetVehicleInspection::class,


            //Insurance
            BancAssuranceReferral::getPrimaryKey() => BancAssuranceReferral::class,
            BancassurancePolicy::getPrimaryKey() => BancassurancePolicy::class,
            BancassuranceCustomer::getPrimaryKey() => BancassuranceCustomer::class,
            BancassuranceCustomerContact::getPrimaryKey() => BancassuranceCustomerContact::class,
            BancassuranceBeneficiaries::getPrimaryKey() => BancassuranceBeneficiaries::class,
            BancassurancePremiumPayments::getPrimaryKey() => BancassurancePremiumPayments::class,
            BancassuranceUnderwriting::getPrimaryKey() => BancassuranceUnderwriting::class,
            BancassuranceClaim::getPrimaryKey() => BancassuranceClaim::class,
            InsuranceProvider::getPrimaryKey() => InsuranceProvider::class,
            InsuranceProduct::getPrimaryKey() => InsuranceProduct::class,
            InsuranceProductRider::getPrimaryKey() => InsuranceProductRider::class,
            BancassuranceCommissionRule::getPrimaryKey() => BancassuranceCommissionRule::class,

            //////////////  Finance  ////////////////
            FinanceGLAccounts::getPrimaryKey() => FinanceGLAccounts::class,
            FinanceGLSubAccountTypes::getPrimaryKey() => FinanceGLSubAccountTypes::class,
            FinanceGLTypeGroup::getPrimaryKey() => FinanceGLTypeGroup::class,
            TaxJurisdiction::getPrimaryKey() => TaxJurisdiction::class,
            FinanceTaxType::getPrimaryKey() => FinanceTaxType::class,
            FinanceInvoiceEntry::getPrimaryKey() => FinanceInvoiceEntry::class,
            FinanceReceipt::getPrimaryKey() => FinanceReceipt::class,

            //Fleet Management

            FinanceJournalEntry::getPrimaryKey() => FinanceJournalEntry::class,
            FinanceJournalLines::getPrimaryKey() => FinanceJournalLines::class,
            RecurrentJournal::getPrimaryKey() => RecurrentJournal::class,
            ReverseJournalEntry::getPrimaryKey() => ReverseJournalEntry::class,
            FinanceTransaction::getPrimaryKey() => FinanceTransaction::class,
            FinanceCDNotes::getPrimaryKey() => FinanceCDNotes::class,
            FinanceTransactionTypes::getPrimaryKey() => FinanceTransactionTypes::class,
            FinanceModuleTransactions::getPrimaryKey() => FinanceModuleTransactions::class,
            FinanceGLMapping::getPrimaryKey() => FinanceGLMapping::class,
            FinanceInvoice::getPrimaryKey() => FinanceInvoice::class,
            FinanceInvoiceLine::getPrimaryKey() => FinanceInvoiceLine::class,
            FinanceCreditManagement::getPrimaryKey() => FinanceCreditManagement::class,


            //////////////  Legal  ////////////////
            LegalDocument::getPrimaryKey() => LegalDocument::class,
            LegalClause::getPrimaryKey() => LegalClause::class,
            LegalCase::getPrimaryKey() => LegalCase::class,
            LegalObligation::getPrimaryKey() => LegalObligation::class,
            LegalSearchRequest::getPrimaryKey() => LegalSearchRequest::class,
            LegalIntellectualProperty::getPrimaryKey() => LegalIntellectualProperty::class,
            LoanSecurity::getPrimaryKey() => LoanSecurity::class,
            LegalCaseEvidence::getPrimaryKey() => LegalCaseEvidence::class,
            LegalCaseCounsel::getPrimaryKey() => LegalCaseCounsel::class,
            LegalCaseOutcome::getPrimaryKey() => LegalCaseOutcome::class,
            LegalTemplate::getPrimaryKey() => LegalTemplate::class,

        ]);

        // Super-admin bypass: Admin roles can perform any ability
        Gate::before(function ($user, string $ability = null, $arguments = null) {
            try {
                if ($user->hasRole(['admin', 'Admin', 'super-admin', 'Super Admin'])) {
                    return true;
                }
            } catch (\Throwable $e) {
            }
            return null;
        });

        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Branch::class, CrmBranchPolicy::class);
        Gate::policy(Repository::class, RepositoryPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(DMSTags::class, DMSTagPolicy::class);
        Gate::policy(DMSSignature::class, DMSSignaturePolicy::class);
        Gate::policy(DocumentValidationType::class, DocumentValidationTypePolicy::class);

        Gate::policy(LegalHold::class, LegalHoldPolicy::class);
        Gate::policy(Requisitions::class, RequisitionPolicy::class);
        Gate::policy(RequisitionLine::class, RequisitionLinesPolicy::class);
        Gate::policy(RFQ::class, RFQPolicy::class);
        Gate::policy(ProductDevelopment::class, ProductDevelopmentPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(DepartmentNeed::class, DepartmentNeedsPolicy::class);
        Gate::policy(ProcurementMethod::class, ProcurementMethodPolicy::class);
        Gate::policy(ItemMasterList::class, ItemMasterListPolicy::class);
        Gate::policy(ItemCategories::class, ItemCategoryPolicy::class);
        Gate::policy(StockTake::class, StockTakePolicy::class);
        Gate::policy(ItemType::class, ItemTypePolicy::class);
        Gate::policy(StockItem::class, StockItemPolicy::class);
        Gate::policy(InventoryType::class, InventoryTypePolicy::class);
        Gate::policy(Store::class, StorePolicy::class);
        Gate::policy(UnitOfMeasure::class, UnitOfMeasurePolicy::class);
        Gate::policy(PriceManagement::class, PriceManagementPolicy::class);
        Gate::policy(ConsolidatedProcurementPlan::class, ProcurementPlanMaintainPolicy::class);
        Gate::policy(SchedulePlan::class, SchedulePlanPolicy::class);
        Gate::policy(PlanLineItem::class, PlanManualInputPolicy::class);
        Gate::policy(InterBranchRequisition::class, InterBranchRequisitionPolicy::class);
        Gate::policy(TransactionReceipt::class, TransactionReceiptPolicy::class);
        Gate::policy(StockAdjustment::class, StockAdjustmentPolicy::class);
        Gate::policy(InventoryHoldReview::class, InventoryHoldReviewPolicy::class);
        Gate::policy(TransactionTransfer::class, TransactionTransferPolicy::class);
        Gate::policy(CategoryMaster::class, PropertyCategoryPolicy::class);
        Gate::policy(PropertyType::class, PropertyTypePolicy::class);
        Gate::policy(PropertyRegistry::class, PropertyRegistryPolicy::class);
        Gate::policy(PropertyBlock::class, PropertyStructuralPolicy::class);
        Gate::policy(PropertyAttachments::class, PropertyAttachmentsPolicy::class);
        Gate::policy(PropertyNewTenant::class, PropertyNewTenantPolicy::class);
        Gate::policy(PropertyTenantClearance::class, PropertyTenantClearancePolicy::class);
        Gate::policy(PropertyFloor::class, PropertyFloorPolicy::class);
        Gate::policy(PropertyUnit::class, PropertyUnitPolicy::class);
        Gate::policy(PropertyLeaseSchedule::class, PropertyLeaseSchedulePolicy::class);
        Gate::policy(PropertyLeaseRenewal::class, PropertyLeaseRenewalPolicy::class);
        Gate::policy(PropertyInvoice::class, PropertyInvoicePolicy::class);
        Gate::policy(PropertyReceipt::class, PropertyReceiptPolicy::class);
        Gate::policy(PropertyMaintenanceRequest::class, PropertyMaintenanceRequestPolicy::class);
        Gate::policy(SupplierMaster::class, SupplierPolicy::class);
        Gate::policy(ProcurementPlan::class, ProcurementPlanPolicy::class);
        Gate::policy(DepartmentNeed::class, DepartmentNeedPolicy::class);
        Gate::policy(ConsolidatedProcurementPlan::class, ConsolidatedProcurementPlanPolicy::class);
        Gate::policy(PlanLineItem::class, PlanLineItemPolicy::class);
        Gate::policy(BidSubmission::class, TenderSubmissionPolicy::class);
        Gate::policy(TenderInvitation::class, TenderInvitationPolicy::class);
        Gate::policy(RFQResponse::class, RFQResponsePolicy::class);

        // Batch 3: Contracts & Orders
        Gate::policy(TenderAward::class, ContractPolicy::class);
        Gate::policy(ProcurementOrder::class, PurchaseOrderPolicy::class);
        Gate::policy(GoodsReceipt::class, GoodsReceiptPolicy::class);
        Gate::policy(\App\Http\Controllers\Procurement\BidOpeningCeremonyController::class, BidOpeningPolicy::class); // Virtual policy binding
        Gate::policy(PropertyMaintenanceAssign::class, PropertyMaintenanceAssignPolicy::class);
        Gate::policy(PropertyMaintenanceWorkCompletion::class, PropertyMaintenanceWorkCompletionPolicy::class);
        // Gate::policy(PrequalificationPeriod::class, PrequalificationPeriodPolicy::class);
        Gate::Policy(PropertyNewLease::class, PropertyNewLeasePolicy::class);
        Gate::policy(PropertyLeaseTermination::class, PropertyLeaseTerminationPolicy::class);
        Gate::policy(BancAssuranceReferral::class, BancAssuranceReferralPolicy::class);
        Gate::policy(BancassurancePolicy::class, BancassurancePoliciesPolicy::class);
        Gate::policy(BancassuranceCustomer::class, BancassuranceCustomersPolicy::class);
        Gate::policy(BancassuranceCustomerContact::class, BancassuranceCustomersContactsPolicy::class);
        Gate::policy(BancassuranceBeneficiaries::class, BancassuranceCustomersBeneficiariesPolicy::class);
        Gate::policy(BancassurancePremiumPayments::class, BancassurancePremiumPaymentsPolicy::class);
        Gate::policy(BancassuranceUnderwriting::class, BancassuranceUnderwritingPolicy::class);
        Gate::policy(BancassuranceClaim::class, BancassuranceClaimPolicy::class);
        Gate::policy(InsuranceProvider::class, InsuranceProviderPolicy::class);
        Gate::policy(InsuranceProduct::class, InsuranceProductPolicy::class);
        Gate::policy(InsuranceProductRider::class, InsuranceProductRiderPolicy::class);
        Gate::policy(BancassuranceCommissionRule::class, CommissionRulePolicy::class);
        Gate::policy(FleetMake::class, FleetMakePolicy::class);
        Gate::policy(FleetModel::class, FleetModelPolicy::class);
        Gate::policy(ContractedDriver::class, ContractedDriverPolicy::class);
        Gate::policy(FleetVehicleRequest::class, FleetVehicleRequestPolicy::class);
        Gate::policy(FleetVehicle::class, FleetVehiclePolicy::class);
        Gate::policy(FleetInsuranceTracker::class, FleetInsuranceTrackerPolicy::class);
        Gate::policy(FleetInspectionSchedule::class, FleetInspectionSchedulePolicy::class);
        Gate::policy(FleetDriver::class, FleetDriverPolicy::class);
        Gate::policy(FleetTripLog::class, FleetTripLogPolicy::class);
        Gate::policy(FleetServiceAlert::class, FleetServiceAlertPolicy::class);
        Gate::policy(FleetRoutePlan::class, FleetRoutePlanPolicy::class);
        Gate::policy(FleetMaintenanceSchedule::class, FleetMaintenanceSchedulePolicy::class);
        Gate::policy(UOMConversion::class, UOMConversionPolicy::class);
        Gate::policy(FleetRepairLog::class, FleetRepairLogPolicy::class);
        Gate::policy(FleetVehicleInspection::class, FleetVehicleInspectionPolicy::class);

        Sanctum::usePersonalAccessTokenModel(CustomPersonalAccessToken::class);

        /* Event::listen(EmailSendEvent::class, EmailSendListener::class);
         Event::listen(SMSSendEvent::class, SMSSendListener::class);

         Event::listen(BulkNotificationEvent::class, BulkSendListener::class);

         Event::listen(BoardMeetingUpdatedEvent::class, BoardMeetingUpdatedListener::class);
         Event::listen(BoardMeetingCanceledEvent::class, BoardMeetingCanceledListener::class);

         Event::listen(ProductDevCommentingEvent::class, ProductDevCommentingListener::class);
         Event::listen(ReopenTicketEvent::class, ReopenWorkflowListener::class);

         //Event::listen(PlannerSubmitEvent::class, PlannerSubmittedListener::class);
         Event::listen(CompetitorRoachEvent::class, CompetitorRoachListener::class);
         Event::listen(NewScheduleEvent::class, NotifyScheduleListener::class);
         Event::listen(NewCampaignEvent::class, ProcessContactsListener::class);
         Event::listen(CampaignSubmittedEvent::class, CampaignSubmittedWorkflowListener::class);
         Event::listen(CampaignRunEvent::class, CampaignRunListener::class);*/


        /* Event::listen(EmailSendEvent::class);
         Event::listen(SMSSendEvent::class);

         Event::listen(BulkNotificationEvent::class);

         Event::listen(BoardMeetingUpdatedEvent::class);
         Event::listen(BoardMeetingCanceledEvent::class);

         Event::listen(ProductDevCommentingEvent::class);
         Event::listen(ReopenTicketEvent::class);

         //Event::listen(PlannerSubmitEvent::class);
         Event::listen(CompetitorRoachEvent::class);
         Event::listen(NewScheduleEvent::class);
         Event::listen(NewCampaignEvent::class);
         Event::listen(CampaignSubmittedEvent::class);
         Event::listen(CampaignRunEvent::class);*/

        View::composer(
            ['procurement.suppliers.prequalification.prequalification-rounds.create', 'procurement.suppliers.prequalification.prequalification-rounds.edit'],
            function ($view) {
                $masterSections = Section::with('criteria')->get();
                $view->with('masterSections', $masterSections);
            }
        );
    }
}
