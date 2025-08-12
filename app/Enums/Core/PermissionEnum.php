<?php

namespace App\Enums\Core;

use App\Traits\UsefulEnumTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LogicException;

enum PermissionEnum: string
{
    use UsefulEnumTrait;

    //calls
    case CallRead = 'call-read';
    case CallWrite = 'call-create';
    case CallUpdate = 'call-update';
    case CallDelete = 'call-delete';

    case ScheduleRead = 'schedule-read';
    case ScheduleWrite = 'schedule-create';
    case ScheduleDelete = 'schedule-delete';

    //Marketing List
    case MarketingListRead = 'marketing-list-read';
    case MarketingListWrite = 'marketing-list-create';
    case MarketingListUpdate = 'marketing-list-update';
    case MarketingListDelete = 'marketing-list-delete';
    //case MarketingListApproval = 'marketing-list-approval';

    //Marketing Plan
    case MarketingPlannerRead = 'marketing-planner-read';
    case MarketingPlannerWrite = 'marketing-planner-create';
    case MarketingPlannerUpdate = 'marketing-planner-update';
    case MarketingPlannerDelete = 'marketing-planner-delete';
    case MarketingPlannerApproval = 'marketing-planner-approval';

    //campaigns
    case CampaignRead = 'campaign-read';
    case CampaignWrite = 'campaign-create';
    case CampaignUpdate = 'campaign-update';
    case CampaignDelete = 'campaign-delete';
    case CampaignApproval = 'campaign-approval';

    //Emails
    case EmailRead = 'email-read';
    case EmailAssign = 'email-assign';
    case EmailDelete = 'email-delete';

    //tickets
    case TicketRead = 'ticket-read';
    case TicketWrite = 'ticket-create';
    case TicketUpdate = 'ticket-update';
    case TicketDelete = 'ticket-delete';
    case TicketApproval = 'ticket-approval';

    //Leads
    case LeadRead = 'lead-read';
    case LeadDelegate = 'lead-delegate';
    case LeadWrite = 'lead-create';
    case LeadViewAll = 'lead-viewAll';
    case LeadUpdate = 'lead-update';
    case LeadDelete = 'lead-delete';
    case LeadsManager = 'leadsManager';

    //Product Development
    case ProductDevelopmentRead = 'product-development-read';
    case ProductDevelopmentWrite = 'product-development-create';
    case ProductDevelopmentUpdate = 'product-development-update';
    case ProductDevelopmentDelete = 'product-development-delete';

    //surveys
    case SurveyRead = 'survey-read';
    case SurveyWrite = 'survey-create';
    //case SurveyUpdate = 'survey-update';
    case SurveyDelete = 'survey-delete';
    case SurveyApproval = 'survey-approval';

    //Competitor Analysis
    case Competitor = 'competitor';
    case CompetitorLLM = 'competitor-crawlAi';

    // Reviews
    case ReviewsView = 'review-view';

    // Debt Recovery
    case DebtCollectionView = 'debt-collection-view';
    case DebtNotificationView = 'debt-notificationsView';
    case DebtCollectionLists = 'debt-marketingLists';
    case DebtNotificationSend = 'debt-notificationsSend';
    case DebtCollectionAssignment = 'debt-collection-assignment';
    case DebtCollectionAdmin = 'debt-collection-admin';


    //Socials
    case SocialRead = 'social-read';
    case SocialWrite = 'social-create';
    case SocialDelete = 'social-delete';

    //System Codes
    case ListsView = 'lists-view';
    case ListsUpdate = 'lists-update';
    case Teams = 'teams';
    case Branches = 'branches';
    case Users = 'users';//set branch manager.
    case UsersMeeting = 'users-meetings';
    case UsersMessaging = 'users-messaging';
    case UsersSessions = 'users-nonExpiringSessions';


    case MeetingRooms = 'meeting-locations';

    case BoardManage = 'board-members-manage';
    case BoardMeeting = 'board-members-meetings';


    case TaskCreate = 'tasks-create';
    case TaskDelegate = 'tasks-delegate';

    case Integrations = 'integrations';
    case Roles = 'roles';
    case Members = 'members';

    //User role
    case Ceo = 'ceo';

    case MarketingManager = 'marketingManager';
    case Managers = 'manager';

    /*
     *
     * ========================================  Procurement  ========================================
     */
    //Requisitions
    case RequisitionRead = 'requisition-read';
    case RequisitionWrite = 'requisition-create';
    case RequisitionUpdate = 'requisition-update';
    case RequisitionDelete = 'requisition-delete';
    case RequisitionApproval = 'requisition-approval';

    //RequisitionItems
    case RequisitionItemsRead = 'requisitionItem-read';
    case RequisitionItemsWrite = 'requisitionItem-create';
    case RequisitionItemsUpdate = 'requisitionItem-update';
    case RequisitionItemsDelete = 'requisitionItem-delete';
    case RequisitionItemsApproval = 'requisitionItem-approval';

    //RFQ
    case RfqRead = 'rfq-read';
    case RfqWrite = 'rfq-create';
    case RfqUpdate = 'rfq-update';
    case RfqDelete = 'rfq-delete';
    case RfqApproval = 'rfq-approval';

    //RequisitionItems
    case PurchaseOrderRead = 'purchaseOrder-read';
    case PurchaseOrderWrite = 'purchaseOrder-create';
    case PurchaseOrderUpdate = 'purchaseOrder-update';
    case PurchaseOrderDelete = 'purchaseOrder-delete';
    case PurchaseOrderApproval = 'purchaseOrder-approval';

    //ProcurementPlan Department Needs
    case DepartmentNeedsRead = 'departmentneeds-read';
    case DepartmentNeedsWrite = 'departmentneeds-create';
    case DepartmentNeedsUpdate = 'departmentneeds-update';
    case DepartmentNeedsDelete = 'departmentneeds-delete';
    case DepartmentNeedsApproval = 'departmentneeds-approval';

    //Tender
    case TenderRead = 'tender-read';
    case TenderWrite = 'tender-create';
    case TenderUpdate = 'tender-update';
    case TenderDelete = 'tender-delete';
    case TenderApproval = 'tender-approval';

    //Tender Suppliers
    case BidSubmissionRead = 'BidSubmission-read';
    case BidSubmissionWrite = 'BidSubmission-create';
    case BidSubmissionUpdate = 'BidSubmission-update';
    case BidSubmissionDelete = 'BidSubmission-delete';

    case TenderInvitationRead = 'TenderInvitation-read';
    case TenderInvitationWrite = 'TenderInvitation-create';
    case TenderInvitationUpdate = 'TenderInvitation-update';
    case TenderInvitationDelete = 'TenderInvitation-delete';

    case VendorClarificationsRead = 'VendorClarifications-read';
    case VendorClarificationsWrite = 'VendorClarifications-create';
    case VendorClarificationsUpdate = 'VendorClarifications-update';
    case VendorClarificationsDelete = 'VendorClarifications-delete';

    //Procument Plan- Plan Consolidation
    case PlanConsolidationRead = 'planconsolidation-read';
    case PlanConsolidationWrite = 'planconsolidation-write';
    case PlanConsolidationUpdate = 'planconsolidation-update';
    case PlanConsolidationDelete = 'planconsolidation-delete';

    //Procument Plan- Plan Maintain
    case PlanMaintenanceRead = 'planmaintenance-read';
    case PlanMaintenanceWrite = 'planmaintenance-write';
    case PlanMaintenanceUpdate = 'planmaintenance-update';
    case PlanMaintenanceDelete = 'planmaintenance-delete';

    //Procument Plan- Plan Manual Input
    case PlanManualInputRead = 'planmanualinput-read';
    case PlanManualInputWrite = 'planmanualinput-write';
    case PlanManualInputUpdate = 'planmanualinput-update';
    case PlanManualInputDelete = 'planmanualinput-delete';

    //Procument Plan- Plan Amend
    case PlanEditRead = 'planedit-read';
    case PlanEditWrite = 'planedit-write';
    case PlanEditUpdate = 'planedit-update';
    case PlanEditDelete = 'planedit-delete';

    //Procument Plan- Plan Line Items
    case PlanLineItemsRead = 'planlineitems-read';
    case PlanLineItemsWrite = 'planlineitems-write';
    case PlanLineItemsUpdate = 'planlineitems-update';
    case PlanLineItemsDelete = 'planlineitems-delete';

    //ProcurementPlan ProcurementMethod
    case ProcurementMethodRead = 'procurementmethod-read';
    case ProcurementMethodWrite = 'procurementmethod-create';

    //ProcurementPlan Procurement Schedule
    //case SchedulePlanRead = 'scheduleplan-read';
    //case SchedulePlanWrite = 'scheduleplan-create';
    //case SchedulePlanUpdate = 'scheduleplan-update';
    /*
    *
    * ========================================  Inventory  ========================================
    */
    case MasterListView = 'masterList-view';
    case MasterListUpdate = 'masterList-update';
    case MasterListCreate = 'masterList-create';
    case MasterListDestroy = 'masterList-destroy';

    case ItemCategoryView = 'itemCategory-view';
    case ItemCategoryUpdate = 'itemCategory-update';
    case ItemCategoryCreate = 'itemCategory-create';
    case ItemCategoryDestroy = 'itemCategory-destroy';

    case StockItemView = 'stockItem-view';
    case StockItemUpdate = 'stockItem-update';
    case StockItemCreate = 'stockItem-create';
    case StockItemDestroy = 'stockItem-destroy';

    case StoreView = 'storeItem-view';
    case StoreUpdate = 'storeItem-update';
    case StoreCreate = 'storeItem-create';
    case StoreDestroy = 'storeItem-destroy';

    case InventoryTypeView = 'inventoryType-view';
    case InventoryTypeUpdate = 'inventoryType-update';
    case InventoryTypeCreate = 'inventoryType-create';
    case InventoryTypeDestroy = 'inventoryType-destroy';

    case ItemTypeView = 'itemType-view';
    case ItemTypeUpdate = 'itemType-update';
    case ItemTypeCreate = 'itemType-create';
    case ItemTypeDestroy = 'itemType-destroy';

    case UOMView = 'uom-view';
    case UOMUpdate = 'uom-update';
    case UOMCreate = 'uom-create';
    case UOMDestroy = 'uom-destroy';

    case InterBranchRequisitionView = 'interBranchRequisition-view';
    case InterBranchRequisitionUpdate = 'interBranchRequisition-update';
    case InterBranchRequisitionCreate = 'interBranchRequisition-create';
    case InterBranchRequisitionDestroy = 'interBranchRequisition-destroy';
    case InterBranchRequisitionApproval = 'interBranchRequisition-approval';

    case PriceManagementView = 'priceManagement-view';
    case PriceManagementUpdate = 'priceManagement-update';
    case PriceManagementCreate = 'priceManagement-create';
    case PriceManagementDestroy = 'priceManagement-destroy';

    case StockTakeView = 'stockTake-view';
    case StockTakeUpdate = 'stockTake-update';
    case StockTakeCreate = 'stockTake-create';
    case StockTakeDestroy = 'stockTake-destroy';

    case TransactionReceiptView = 'transactionReceipt-view';
    case TransactionReceiptUpdate = 'transactionReceipt-update';
    case TransactionReceiptCreate = 'transactionReceipt-create';
    case TransactionReceiptDestroy = 'transactionReceipt-destroy';

    case TransactionTransferView = 'transactionTransfer-view';
    case TransactionTransferUpdate = 'transactionTransfer-update';
    case TransactionTransferCreate = 'transactionTransfer-create';
    case TransactionTransferDestroy = 'transactionTransfer-destroy';
    case TransactionTransferApproval = 'transactionTransfer-approval';

    case StockAdjustmentView = 'stockAdjustment-view';
    case StockAdjustmentUpdate = 'stockAdjustment-update';
    case StockAdjustmentCreate = 'stockAdjustment-create';
    case StockAdjustmentDestroy = 'stockAdjustment-destroy';
    // case StockAdjustmentApproval= 'stockAdjustment-approval';

    case InventoryHoldReviewView = 'inventoryHoldReview-view';
    case InventoryHoldReviewUpdate = 'inventoryHoldReview-update';
    case InventoryHoldReviewCreate = 'inventoryHoldReview-create';
    case InventoryHoldReviewDestroy = 'inventoryHoldReview-destroy';

    case UOMConversionView = 'uomConversion-view';
    case UOMConversionUpdate = 'uomConversion-update';
    case UOMConversionCreate = 'uomConversion-create';
    case UOMConversionDestroy = 'uomConversion-destroy';

    /*
     *
     * ========================================  Property Management  ========================================
     */

    //property category
    case PropertyCategoryCreate = 'propertycategory-create';
    case PropertyCategoryUpdate = 'propertycategory-update';
    case PropertyCategoryDelete = 'propertycategory-delete';
    case PropertyCategoryView = 'propertycategory-view';

    //Property type
    case PropertyTypeCreate = 'propertytype-create';
    case PropertyTypeUpdate = 'propertytype-update';
    case PropertyTypeDelete = 'propertytype-delete';
    case PropertyTypeView = 'propertytype-view';


    //Property Registry
    case PropertyRegistryCreate = 'propertyregistry-create';
    case PropertyRegistryUpdate = 'propertyregistry-update';
    case PropertyRegistryDelete = 'propertyregistry-delete';
    case PropertyRegistryView = 'propertyregistry-view';


    //Property Block
    case PropertyStructuralCreate = 'propertystructural-create';
    case PropertyStructuralUpdate = 'propertystructural-update';
    case PropertyStructuralDelete = 'propertystructural-delete';
    case PropertyStructuralView = 'propertystructural-view';


    //Property Attachments
    case PropertyAttachmentsCreate = 'propertyattachments-create';
    case PropertyAttachmentsUpdate = 'propertyattachments-update';
    case PropertyAttachmentsDelete = 'propertyattachments-delete';
    case PropertyAttachmentsView = 'propertyattachments-view';
    


    //Tenant Mentenance
    case TenantMentenanceCreate = 'tenantmentenance-create';
    case TenantMentenanceUpdate = 'tenantmentenance-update';
    case TenantMentenanceDelete = 'tenantmentenance-delete';
    case TenantMentenanceView = 'tenantmentenance-view';

    //Property Tenant Clearance
    case TenantClearanceCreate = 'tenantclearance-create';
    case TenantClearanceUpdate = 'tenantclearance-update';
    case TenantClearanceDelete = 'tenantclearance-delete';
    case TenantClearanceView = 'tenantclearance-view';


    //Property New Lease
    case PropertyNewLeaseCreate = 'propertynewlease-create';
    case PropertyNewLeaseUpdate = 'propertynewlease-update';
    case PropertyNewLeaseDelete = 'propertynewlease-delete';
    case PropertyNewLeaseView = 'propertynewlease-view';

    //Property lease schedule
    case PropertyLeaseScheduleCreate = 'propertyleaseschedule-create';
    case PropertyLeaseScheduleUpdate = 'propertyleaseschedule-update';

    case PropertyLeaseScheduleDelete = 'propertyleaseschedule-delete';
    case PropertyLeaseScheduleView = 'propertyleaseschedule-view';

    //Property lease Renewal
    case PropertyLeaseRenewalCreate = 'propertyleaserenewal-create';
    case PropertyLeaseRenewalUpdate = 'propertyleaserenewal-update';
    case PropertyLeaseRenewalDelete = 'propertyleaserenewal-delete';
    case PropertyLeaseRenewalView = 'propertyleaserenewal-view';


    //Property Lease Termination
    case PropertyLeaseTerminationCreate = 'propertyleasetermination-create';
    case PropertyLeaseTerminationUpdate = 'propertyleasetermination-update';
    case PropertyLeaseTerminationDelete = 'propertyleasetermination-delete';
    case PropertyLeaseTerminationView = 'propertyleasetermination-view';

    //Property Invoice
    case PropertyInvoiceCreate = 'propertyinvoice-create';
    case PropertyInvoiceUpdate = 'propertyinvoice-update';
    case PropertyInvoiceDelete = 'propertyinvoice-delete';
    case PropertyInvoiceView = 'propertyinvoice-view';
    
    //Property Receipt
    case PropertyReceiptCreate = 'propertyreceipt-create';
    case PropertyReceiptUpdate = 'propertyreceipt-update';
    case PropertyReceiptDelete = 'propertyreceipt-delete';
    case PropertyReceiptView = 'propertyreceipt-view';

    //Property Maintenance Request
    case PropertyMaintenanceRequestCreate = 'propertymaintenancerequest-create';
    case PropertyMaintenanceRequestUpdate = 'propertymaintenancerequest-update';
    case PropertyMaintenanceRequestDelete = 'propertymaintenancerequest-delete';
    case PropertyMaintenanceRequestView = 'propertymaintenancerequest-view';


     //Property Maintenance Assign
    case PropertyMaintenanceAssignCreate = 'propertymaintenanceassign-create';
    case PropertyMaintenanceAssignUpdate = 'propertymaintenanceassign-update';
    case PropertyMaintenanceAssignDelete = 'propertymaintenanceassign-delete';
    case PropertyMaintenanceAssignView = 'propertymaintenanceassign-view';

     //Property Maintenance Work Completion
    case PropertyMaintenanceWorkCompletionCreate = 'propertymaintenanceworkcompletion-create';
    case PropertyMaintenanceWorkCompletionUpdate = 'propertymaintenanceworkcompletion-update';
    case PropertyMaintenanceWorkCompletionDelete = 'propertymaintenanceworkcompletion-delete';
    case PropertyMaintenanceWorkCompletionView = 'propertymaintenanceworkcompletion-view';


    

    /*
     *
     * ========================================  Human Resource management  ========================================
     */
    case Departments = 'department';
    case EmployeesView = 'employee-read';
    case EmployeesCreate = 'employee-create';
    case EmployeesUpdate = 'employee-update';
    case EmployeesDelete = 'employee-delete';


    /*
 *
 * ========================================  Budget and Analytics  ========================================
 */
    case BudgetSetupView = 'budgetSetup-view';
    case BudgetSetupCreate = 'budgetSetup-create';
    case BudgetSetupUpdate = 'budgetSetup-update';
    case BudgetSetupDelete = 'budgetSetup-delete';


 /*
 *
 * ========================================  Finance  ========================================
 */
    //Chart Of Accounts
    case FinanceCOAView = 'financeCOA-view';
    case FinanceCOACreate = 'financeCOA-create';
    case FinanceCOAUpdate = 'financeCOA-update';
    case FinanceCOADelete = 'financeCOA-delete';

    //General Ledger
    case FinanceGeneralLedgerView = 'financeGeneralLedger-view';
    case FinanceGeneralLedgerCreate = 'financeGeneralLedger-create';
    case FinanceGeneralLedgerUpdate = 'financeGeneralLedger-update';
    case FinanceGeneralLedgerDelete = 'financeGeneralLedger-delete';

    //Accounts Payable
    case FinanceAccountsPayableView = 'financeAccountsPayable-view';
    case FinanceAccountsPayableCreate = 'financeAccountsPayable-create';
    case FinanceAccountsPayableUpdate = 'financeAccountsPayable-update';
    case FinanceAccountsPayableDelete = 'financeAccountsPayable-delete';

    //Accounts Receivable
    case FinanceAccountsReceivableView = 'financeAccountsReceivable-view';
    case FinanceAccountsReceivableCreate = 'financeAccountsReceivable-create';
    case FinanceAccountsReceivableUpdate = 'financeAccountsReceivable-update';
    case FinanceAccountsReceivableDelete = 'financeAccountsReceivable-delete';

    //Tax Setting
    case FinanceTaxSettingView = 'financeTaxSetting-view';
    case FinanceTaxSettingCreate = 'financeTaxSetting-create';
    case FinanceTaxSettingUpdate = 'financeTaxSetting-update';
    case FinanceTaxSettingDelete = 'financeTaxSetting-delete';


 /*
 *
 * ========================================  FleetManagement  ========================================
 */
    case FleetModelView = 'fleetModel-view';
    case FleetModelCreate = 'fleetModel-create';
    case FleetModelUpdate = 'fleetModel-update';
    case FleetModelDestroy = 'fleetModel-delete';

    case FleetMakeView = 'fleetMake-view';
    case FleetMakeCreate = 'fleetMake-create';
    case FleetMakeUpdate = 'fleetMake-update';
    case FleetMakeDestroy = 'fleetMake-delete';

    case VehicleRegistryView = 'vehicleRegistry-view';
    case VehicleRegistryCreate = 'vehicleRegistry-create';
    case VehicleRegistryUpdate = 'vehicleRegistry-update';
    case VehicleRegistryDestroy = 'vehicleRegistry-destroy';

    case DriverManagementView = 'driverManagement-view';
    case DriverManagementCreate = 'driverManagement-create';
    case DriverManagementUpdate = 'driverManagement-update';
    case DriverManagementDestroy = 'driverManagement-destroy';



    public static function display(): Collection
    {
        return collect([
            [self::TicketRead, self::TicketWrite, self::TicketUpdate, self::TicketDelete, self::TicketApproval,],
            [self::TaskCreate, self::TaskDelegate,],
            [self::RfqRead, self::RfqWrite, self::RfqUpdate, self::RfqDelete, self::RfqApproval,],
            [self::Members],
            [self::LeadRead, self::LeadWrite, self::LeadDelegate, self::LeadUpdate, self::LeadViewAll, self::LeadsManager, self::LeadDelete,],
            [self::EmailRead, self::EmailAssign, self::EmailDelete,],
            [self::CallRead, self::CallWrite, self::CallUpdate, self::CallDelete,],
            [self::ScheduleRead, self::ScheduleWrite, self::ScheduleDelete, self::MeetingRooms,],

            [self::MarketingPlannerRead, self::MarketingPlannerWrite, self::MarketingPlannerUpdate, self::MarketingPlannerDelete, self::MarketingPlannerApproval,],
            [self::MarketingListRead, self::MarketingListWrite, self::MarketingListUpdate, self::MarketingListDelete,/*, self::MarketingListApproval*/],
            [self::ReviewsView, self::SurveyRead, self::SurveyWrite, self::SurveyDelete, self::SurveyApproval,],
            [self::CampaignRead, self::CampaignWrite, self::CampaignUpdate, self::CampaignDelete, self::CampaignApproval,],
            [self::SocialRead, self::SocialWrite, self::SocialDelete,],
            [self::Competitor, self::CompetitorLLM,],

            [self::DebtCollectionView, self::DebtCollectionAssignment, self::DebtCollectionAdmin, self::DebtNotificationView, self::DebtNotificationSend, self::DebtCollectionLists,],
            [self::ProductDevelopmentRead, self::ProductDevelopmentWrite, self::ProductDevelopmentUpdate, self::ProductDevelopmentDelete,],

            [self::ListsView, self::ListsUpdate,],
            [self::Users, self::UsersMeeting, self::UsersMessaging, self::UsersSessions, self::Teams, self::Branches,],
            [self::Ceo, self::Managers, self::MarketingManager,],

            [self::BoardManage, self::BoardMeeting,],
            [self::Integrations],
            [self::Roles],

            [self::DepartmentNeedsRead, self::DepartmentNeedsWrite, self::DepartmentNeedsUpdate, self::DepartmentNeedsDelete, self::DepartmentNeedsApproval,],
            [self::ProcurementMethodRead, self::ProcurementMethodWrite,],


            [self::EmployeesView, self::EmployeesCreate, self::EmployeesUpdate, self::EmployeesDelete, self::Departments],
            [self::RequisitionRead, self::RequisitionWrite, self::RequisitionUpdate, self::RequisitionDelete, self::RequisitionApproval, self::RequisitionItemsRead, self::RequisitionItemsWrite, self::RequisitionItemsUpdate, self::RequisitionItemsDelete, self::RequisitionItemsApproval],
            [self::PurchaseOrderRead, self::PurchaseOrderWrite, self::PurchaseOrderUpdate, self::PurchaseOrderDelete, self::PurchaseOrderApproval],

            //Tenders
            [self::TenderRead, self::TenderWrite, self::TenderUpdate, self::TenderDelete, self::TenderApproval],

            //Inventory
            [self::MasterListView, self::MasterListUpdate, self::MasterListCreate, self::MasterListDestroy],
            [self::ItemCategoryView, self::ItemCategoryUpdate, self::ItemCategoryCreate, self::ItemCategoryDestroy],
            [self::StockItemView, self::StockItemUpdate, self::StockItemCreate, self::StockItemDestroy],
            [self::StoreView, self::StoreUpdate, self::StoreCreate, self::StoreDestroy],
            [self::InventoryTypeView, self::InventoryTypeUpdate, self::InventoryTypeCreate, self::InventoryTypeDestroy],
            [self::ItemTypeView, self::ItemTypeUpdate, self::ItemTypeCreate, self::ItemTypeDestroy],
            [self::UOMView, self::UOMUpdate, self::UOMCreate, self::UOMDestroy],
            [self::InterBranchRequisitionView, self::InterBranchRequisitionUpdate, self::InterBranchRequisitionCreate, self::InterBranchRequisitionDestroy, self::InterBranchRequisitionApproval],
            [self::PriceManagementView, self::PriceManagementUpdate, self::PriceManagementCreate, self::PriceManagementDestroy],
            [self::TransactionReceiptView, self::TransactionReceiptUpdate, self::TransactionReceiptCreate, self::TransactionReceiptDestroy],
            [self::TransactionTransferView, self::TransactionTransferUpdate, self::TransactionTransferCreate, self::TransactionTransferDestroy, self::TransactionTransferApproval],
            [self::StockAdjustmentView, self::StockAdjustmentUpdate, self::StockAdjustmentCreate, self::StockAdjustmentDestroy],
            [self::InventoryHoldReviewView, self::InventoryHoldReviewUpdate, self::InventoryHoldReviewCreate, self::InventoryHoldReviewDestroy],
            [self::UOMConversionView, self::UOMConversionUpdate, self::UOMConversionCreate, self::UOMConversionDestroy],
            [self::PlanConsolidationRead, self::PlanConsolidationWrite, self::PlanConsolidationUpdate, self::PlanConsolidationDelete],
            [self::PlanLineItemsRead, self::PlanLineItemsWrite, self::PlanLineItemsUpdate, self::PlanLineItemsDelete],
            [self::BidSubmissionRead, self::BidSubmissionWrite, self::BidSubmissionUpdate, self::BidSubmissionDelete],
            [self::TenderInvitationRead, self::TenderInvitationWrite, self::TenderInvitationUpdate, self::TenderInvitationDelete],
            [self::VendorClarificationsRead, self::VendorClarificationsWrite, self::VendorClarificationsUpdate, self::VendorClarificationsDelete],
            [self::PlanMaintenanceRead, self::PlanMaintenanceWrite, self::PlanMaintenanceUpdate, self::PlanMaintenanceDelete],
            [self::PlanManualInputRead, self::PlanManualInputWrite, self::PlanManualInputUpdate, self::PlanManualInputDelete],
            [self::PlanEditRead, self::PlanEditWrite, self::PlanEditUpdate, self::PlanEditDelete],
            [self::StockTakeView, self::StockTakeCreate, self::StockTakeUpdate, self::StockTakeDestroy],

            /////////////////////////// Budget and Analytics  ///////////////////////
            [self::BudgetSetupView, self::BudgetSetupCreate, self::BudgetSetupUpdate, self::BudgetSetupDelete],


            //Property Management
            ///////////////////////  Finance  /////////////////////////////////////
            [self::FinanceCOAView, self::FinanceCOACreate, self::FinanceCOAUpdate, self::FinanceCOADelete],
            [self::FinanceGeneralLedgerView, self::FinanceGeneralLedgerCreate, self::FinanceGeneralLedgerUpdate, self::FinanceGeneralLedgerDelete],
            [self::FinanceAccountsPayableView, self::FinanceAccountsPayableCreate, self::FinanceAccountsPayableUpdate, self::FinanceAccountsPayableDelete],
            [self::FinanceAccountsReceivableView, self::FinanceAccountsReceivableCreate, self::FinanceAccountsReceivableUpdate, self::FinanceAccountsReceivableDelete],
            [self::FinanceTaxSettingView, self::FinanceTaxSettingCreate, self::FinanceTaxSettingUpdate, self::FinanceTaxSettingDelete],


            [self::PropertyCategoryView,self::PropertyCategoryCreate,self::PropertyCategoryUpdate,self::PropertyCategoryDelete],
            [self::PropertyTypeView,self::PropertyTypeCreate,self::PropertyTypeUpdate,self::PropertyTypeDelete],
            [self::PropertyRegistryView,self::PropertyRegistryCreate,self::PropertyRegistryUpdate,self::PropertyRegistryDelete],
            [self::PropertyStructuralView,self::PropertyStructuralCreate,self::PropertyStructuralUpdate,self::PropertyStructuralDelete],
            [self::PropertyAttachmentsView,self::PropertyAttachmentsCreate,self::PropertyAttachmentsUpdate,self::PropertyAttachmentsDelete],
            [self::TenantMentenanceCreate,self::TenantMentenanceUpdate,self::TenantMentenanceDelete,self::TenantMentenanceView],
            [self::TenantClearanceCreate,self::TenantClearanceUpdate,self::TenantClearanceDelete,self::TenantClearanceView],
            [self::PropertyNewLeaseCreate,self::PropertyNewLeaseUpdate,self::PropertyNewLeaseDelete,self::PropertyNewLeaseView],
            [self::PropertyLeaseTerminationCreate,self::PropertyLeaseTerminationUpdate,self::PropertyLeaseTerminationDelete,self::PropertyLeaseTerminationView],
            [self::PropertyLeaseScheduleCreate,self::PropertyLeaseScheduleUpdate,self::PropertyLeaseScheduleDelete,self::PropertyLeaseScheduleView],
            [self::PropertyLeaseRenewalCreate,self::PropertyLeaseRenewalUpdate,self::PropertyLeaseRenewalDelete,self::PropertyLeaseRenewalView],
            [self::PropertyInvoiceCreate,self::PropertyInvoiceUpdate,self::PropertyInvoiceDelete,self::PropertyInvoiceView],
            [self::PropertyReceiptCreate,self::PropertyReceiptUpdate,self::PropertyReceiptDelete,self::PropertyReceiptView],
            [self::PropertyMaintenanceRequestCreate,self::PropertyMaintenanceRequestUpdate,self::PropertyMaintenanceRequestDelete,self::PropertyMaintenanceRequestView],
            [self::PropertyMaintenanceAssignCreate,self::PropertyMaintenanceAssignUpdate,self::PropertyMaintenanceAssignDelete,self::PropertyMaintenanceAssignView],
            [self::PropertyMaintenanceWorkCompletionCreate,self::PropertyMaintenanceWorkCompletionUpdate,self::PropertyMaintenanceWorkCompletionDelete,self::PropertyMaintenanceWorkCompletionView],

              ///////////////////////  Fleet Management  /////////////////////////////////////
            [self::FleetModelView,self::FleetModelCreate,self::FleetModelUpdate,self::FleetModelDestroy],
            [self::FleetMakeView,self::FleetMakeCreate,self::FleetMakeUpdate,self::FleetMakeDestroy],
            [self::VehicleRegistryView,self::VehicleRegistryCreate,self::VehicleRegistryUpdate,self::VehicleRegistryDestroy],
            [self::DriverManagementView,self::DriverManagementCreate,self::DriverManagementUpdate,self::DriverManagementDestroy],
        ]);
    }

    public static function approvals(): Collection
    {
        return collect([self::MarketingPlannerApproval, /* self::MarketingListApproval,*/ self::TicketApproval, self::CampaignApproval, self::SurveyApproval, self::Ceo, self::MarketingManager,
            self::PurchaseOrderApproval, self::RequisitionApproval, self::RequisitionItemsApproval, self::DepartmentNeedsApproval, self::TenderApproval,
            self::PurchaseOrderApproval, self::RequisitionApproval, self::RequisitionItemsApproval]);

    }


    public function module(): ModulesEnum
    {
        return match ($this) {
            self::MarketingPlannerRead, self::MarketingPlannerWrite, self::MarketingPlannerUpdate, self::MarketingPlannerDelete, self::MarketingPlannerApproval,
            self::MarketingListRead, self::MarketingListWrite, self::MarketingListUpdate, self::MarketingListDelete, self::DebtCollectionLists/*, self::MarketingListApproval*/,
            self::ProductDevelopmentRead, self::ProductDevelopmentWrite, self::ProductDevelopmentUpdate, self::ProductDevelopmentDelete,
            self::EmailRead, self::EmailAssign, self::EmailDelete,
            self::ScheduleRead, self::ScheduleWrite, self::ScheduleDelete,
            self::CallRead, self::CallWrite, self::CallUpdate, self::CallDelete,
            self::CampaignRead, self::CampaignWrite, self::CampaignUpdate, self::CampaignDelete, self::CampaignApproval,
            self::TicketRead, self::TicketWrite, self::TicketUpdate, self::TicketDelete, self::TicketApproval,
            self::TaskCreate, self::TaskDelegate,
            self::LeadRead, self::LeadWrite, self::LeadDelegate, self::LeadUpdate, self::LeadDelete, self::LeadViewAll, self::LeadsManager,
            self::SocialRead, self::SocialWrite, self::SocialDelete,
            self::ReviewsView,
            self::DebtCollectionView, self::DebtCollectionAssignment, self::DebtCollectionAdmin,
            self::DebtNotificationView, self::DebtNotificationSend,
            self::SurveyRead, self::SurveyWrite, self::SurveyDelete, self::SurveyApproval,
            self::Competitor, self::CompetitorLLM, self::Members, self::BoardManage, self::BoardMeeting => ModulesEnum::CRM,


            self::Teams, self::Branches, self::Users, self::UsersMeeting, self::UsersMessaging, self::UsersSessions, self::Integrations,
            self::MeetingRooms, self::Roles, self::Ceo, self::Managers, self::MarketingManager, self::ListsView, self::ListsUpdate
            => ModulesEnum::Settings,

            //Procurement
            self::RequisitionRead, self::RequisitionWrite, self::RequisitionUpdate, self::RequisitionDelete, self::RequisitionApproval,
            self::RequisitionItemsRead, self::RequisitionItemsWrite, self::RequisitionItemsUpdate, self::RequisitionItemsDelete, self::RequisitionItemsApproval,
            self::PurchaseOrderRead, self::PurchaseOrderWrite, self::PurchaseOrderUpdate, self::PurchaseOrderDelete, self::PurchaseOrderApproval,
            self::RfqRead, self::RfqWrite, self::RfqUpdate, self::RfqApproval, self::RfqDelete,
            self::DepartmentNeedsRead, self::DepartmentNeedsWrite, self::DepartmentNeedsUpdate, self::DepartmentNeedsDelete, self::DepartmentNeedsApproval,
            self::PlanConsolidationRead, self::PlanConsolidationWrite, self::PlanConsolidationUpdate, self::PlanConsolidationDelete,
            self::ProcurementMethodRead, self::ProcurementMethodWrite,
            self::TenderRead, self::TenderWrite, self::TenderUpdate, self::TenderDelete, self::TenderApproval,
            self::PlanLineItemsRead, self::PlanLineItemsWrite, self::PlanLineItemsUpdate, self::PlanLineItemsDelete,
            self::BidSubmissionRead, self::BidSubmissionWrite, self::BidSubmissionUpdate, self::BidSubmissionDelete,
            self::TenderInvitationRead, self::TenderInvitationWrite, self::TenderInvitationUpdate, self::TenderInvitationDelete,
            self::VendorClarificationsRead, self::VendorClarificationsWrite, self::VendorClarificationsUpdate, self::VendorClarificationsDelete,
            self::PlanMaintenanceRead, self::PlanMaintenanceWrite, self::PlanMaintenanceUpdate, self::PlanMaintenanceDelete,
            self::PlanManualInputRead, self::PlanManualInputWrite, self::PlanManualInputUpdate, self::PlanManualInputDelete,
            self::PlanEditRead, self::PlanEditWrite, self::PlanEditUpdate, self::PlanEditDelete,
            => ModulesEnum::Procurement,

            //Human Resource Management
            self::Departments, self::EmployeesView, self::EmployeesCreate, self::EmployeesUpdate, self::EmployeesDelete => ModulesEnum::HRM,

            //Inventory
            self::MasterListView, self::MasterListUpdate, self::MasterListCreate, self::MasterListDestroy,
            self::ItemCategoryView, self::ItemCategoryUpdate, self::ItemCategoryCreate, self::ItemCategoryDestroy,
            self::StockItemView, self::StockItemUpdate, self::StockItemCreate, self::StockItemDestroy,
            self::StoreView, self::StoreUpdate, self::StoreCreate, self::StoreDestroy,
            self::InventoryTypeView, self::InventoryTypeUpdate, self::InventoryTypeCreate, self::InventoryTypeDestroy,
            self::ItemTypeView, self::ItemTypeUpdate, self::ItemTypeCreate, self::ItemTypeDestroy,
            self::UOMView, self::UOMUpdate, self::UOMCreate, self::UOMDestroy,
            self::InterBranchRequisitionView, self::InterBranchRequisitionUpdate, self::InterBranchRequisitionCreate, self::InterBranchRequisitionDestroy, self::InterBranchRequisitionApproval,
            self::InventoryHoldReviewView, self::InventoryHoldReviewUpdate, self::InventoryHoldReviewCreate, self::InventoryHoldReviewDestroy,
            self::UOMConversionView, self::UOMConversionUpdate, self::UOMConversionCreate, self::UOMConversionDestroy,
            self::PriceManagementView, self::PriceManagementUpdate, self::PriceManagementCreate, self::PriceManagementDestroy,
            self::StockTakeView, self::StockTakeCreate, self::StockTakeUpdate, self::StockTakeDestroy,
            self::TransactionReceiptView, self::TransactionReceiptUpdate, self::TransactionReceiptCreate, self::TransactionReceiptDestroy, 
            self::TransactionTransferView, self::TransactionTransferUpdate, self::TransactionTransferCreate, self::TransactionTransferDestroy, self::TransactionTransferApproval, 
            self::StockAdjustmentView, self::StockAdjustmentUpdate, self::StockAdjustmentCreate, self::StockAdjustmentDestroy => ModulesEnum::Inventory,
            //Fleet Management
            self::FleetModelView,self::FleetModelCreate,self::FleetModelUpdate,self::FleetModelDestroy,
            self::FleetMakeView,self::FleetMakeCreate,self::FleetMakeUpdate,self::FleetMakeDestroy,
            self::VehicleRegistryView,self::VehicleRegistryCreate,self::VehicleRegistryUpdate,self::VehicleRegistryDestroy,
            self::DriverManagementView,self::DriverManagementCreate,self::DriverManagementUpdate,self::DriverManagementDestroy
            => ModulesEnum::Fleet,


          //Property Management
            self::PropertyCategoryView,self::PropertyCategoryCreate,self::PropertyCategoryUpdate,self::PropertyCategoryDelete,
            self::PropertyTypeView,self::PropertyTypeCreate,self::PropertyTypeUpdate,self::PropertyTypeDelete,
            self::PropertyRegistryView,self::PropertyRegistryCreate,self::PropertyRegistryUpdate,self::PropertyRegistryDelete,
            self::PropertyStructuralView,self::PropertyStructuralCreate,self::PropertyStructuralUpdate,self::PropertyStructuralDelete,
            self::PropertyAttachmentsView,self::PropertyAttachmentsCreate,self::PropertyAttachmentsUpdate,self::PropertyAttachmentsDelete,
            self::TenantMentenanceCreate,self::TenantMentenanceUpdate,self::TenantMentenanceDelete,self::TenantMentenanceView,
            self::TenantClearanceCreate,self::TenantClearanceUpdate,self::TenantClearanceDelete,self::TenantClearanceView,
            self::PropertyNewLeaseCreate,self::PropertyNewLeaseUpdate,self::PropertyNewLeaseDelete,self::PropertyNewLeaseView,
            self::PropertyLeaseTerminationCreate,self::PropertyLeaseTerminationUpdate,self::PropertyLeaseTerminationDelete,self::PropertyLeaseTerminationView,
            self::PropertyLeaseScheduleCreate,self::PropertyLeaseScheduleUpdate,self::PropertyLeaseScheduleDelete,self::PropertyLeaseScheduleView,
            self::PropertyLeaseRenewalCreate,self::PropertyLeaseRenewalUpdate,self::PropertyLeaseRenewalDelete,self::PropertyLeaseRenewalView,
            self::PropertyInvoiceCreate,self::PropertyInvoiceUpdate,self::PropertyInvoiceDelete,self::PropertyInvoiceView,
            self::PropertyReceiptCreate,self::PropertyReceiptUpdate,self::PropertyReceiptDelete,self::PropertyReceiptView,
            self::PropertyMaintenanceRequestCreate,self::PropertyMaintenanceRequestUpdate,self::PropertyMaintenanceRequestDelete,self::PropertyMaintenanceRequestView,
            self::PropertyMaintenanceAssignCreate,self::PropertyMaintenanceAssignUpdate,self::PropertyMaintenanceAssignDelete,self::PropertyMaintenanceAssignView,
            self::PropertyMaintenanceWorkCompletionCreate,self::PropertyMaintenanceWorkCompletionUpdate,self::PropertyMaintenanceWorkCompletionDelete,self::PropertyMaintenanceWorkCompletionView
            => ModulesEnum::Property,
            default => throw new LogicException("Unhandled PermissionEnum case: {$this->value}"),

            ///////////////^*********** Budget and Analytics ******************/////////////////
                self::BudgetSetupView, self::BudgetSetupCreate, self::BudgetSetupUpdate, self::BudgetSetupDelete
                => ModulesEnum::BudgetLine,


            ////////////////////   Finance   ////////////////////////////
            self::FinanceCOAView, self::FinanceCOACreate, self::FinanceCOAUpdate, self::FinanceCOADelete,
            self::FinanceGeneralLedgerView, self::FinanceGeneralLedgerCreate, self::FinanceGeneralLedgerUpdate, self::FinanceGeneralLedgerDelete,
            self::FinanceAccountsPayableView, self::FinanceAccountsPayableCreate, self::FinanceAccountsPayableUpdate, self::FinanceAccountsPayableDelete,
            self::FinanceAccountsReceivableView, self::FinanceAccountsReceivableCreate, self::FinanceAccountsReceivableUpdate, self::FinanceAccountsReceivableDelete,
            self::FinanceTaxSettingView, self::FinanceTaxSettingCreate, self::FinanceTaxSettingUpdate, self::FinanceTaxSettingDelete,
            => ModulesEnum::Finance,
        };

    }

    public function subName(): string
    {
        return Str::of(array_reverse(explode('-', $this->value))[0])->snake(' ')->title()->toString();
    }

    public function title(): string
    {
        return match ($this) {
            self::MarketingPlannerRead, self::MarketingPlannerWrite, self::MarketingPlannerUpdate, self::MarketingPlannerDelete, self::MarketingPlannerApproval => 'Marketing Planner',
            self::MarketingListRead, self::MarketingListWrite, self::MarketingListUpdate, self::MarketingListDelete/*, self::MarketingListApproval*/ => 'Marketing List',
            self::ProductDevelopmentRead, self::ProductDevelopmentWrite, self::ProductDevelopmentUpdate, self::ProductDevelopmentDelete => 'Product Development',
            self::ScheduleRead, self::ScheduleWrite, self::ScheduleDelete, self::MeetingRooms => "Schedule (Call & Appointments)",
            self::EmailRead, self::EmailAssign, self::EmailDelete => "Emails",
            self::CallRead, self::CallWrite, self::CallUpdate, self::CallDelete => 'Calls',
            self::SocialRead, self::SocialWrite, self::SocialDelete => 'Social Media',
            self::CampaignRead, self::CampaignWrite, self::CampaignUpdate, self::CampaignDelete, self::CampaignApproval => 'Campaigns',
            self::RfqRead, self::RfqWrite, self::RfqUpdate, self::RfqDelete, self::RfqApproval => 'RFQ',
            self::TicketRead, self::TicketWrite, self::TicketUpdate, self::TicketDelete, self::TicketApproval => 'Tickets',
            self::TaskCreate, self::TaskDelegate => 'Tasks',
            self::DebtCollectionView, self::DebtCollectionAssignment, self::DebtCollectionAdmin, self::DebtNotificationView, self::DebtNotificationSend, self::DebtCollectionLists => 'Debt Collection',
            self::LeadRead, self::LeadWrite, self::LeadDelegate, self::LeadUpdate, self::LeadDelete, self::LeadViewAll, self::LeadsManager => 'Leads',
            self::ReviewsView, self::SurveyRead, self::SurveyWrite, self::SurveyDelete, self::SurveyApproval => 'Feedback',
            self::Competitor, self::CompetitorLLM => 'Competitor',
            self::Teams, self::Branches, self::Users, self::UsersMeeting, self::UsersMessaging, self::UsersSessions => 'Users & Roles',
            self::Ceo, self::Managers, self::MarketingManager => 'User Roles',
            self::BoardManage, self::BoardMeeting => 'Board Members',
            self::Integrations => 'Integrations',
            self::Members => 'Members',
            self::Roles => 'Roles',
            self::ListsView, self::ListsUpdate => 'System Codes',
            self::DepartmentNeedsRead, self::DepartmentNeedsWrite, self::DepartmentNeedsUpdate, self::DepartmentNeedsDelete, self::DepartmentNeedsApproval => 'Department Needs',
            self::Departments, self::EmployeesView, self::EmployeesCreate, self::EmployeesUpdate, self::EmployeesDelete => 'Employees',
            self::PlanConsolidationRead, self::PlanConsolidationWrite, self::PlanConsolidationUpdate, self::PlanConsolidationDelete, => 'Consolodidated Needs',
            self::ProcurementMethodRead, self::ProcurementMethodWrite => 'Procurement Method',
            self::PlanLineItemsRead, self::PlanLineItemsWrite, self::PlanLineItemsUpdate, self::PlanLineItemsDelete, => 'Plan Line Items',
            self::PlanMaintenanceRead, self::PlanMaintenanceWrite, self::PlanMaintenanceUpdate, self::PlanMaintenanceDelete, => 'Plan Maintenance',
            self::PlanManualInputRead, self::PlanManualInputWrite, self::PlanManualInputUpdate, self::PlanManualInputDelete, => 'Plan Manual Input',
            self::PlanEditRead, self::PlanEditWrite, self::PlanEditUpdate, self::PlanEditDelete, => 'Plan amendmend',
            //Requisition
            self::RequisitionRead, self::RequisitionWrite, self::RequisitionUpdate, self::RequisitionDelete, self::RequisitionApproval, self::RequisitionItemsRead, self::RequisitionItemsWrite, self::RequisitionItemsUpdate, self::RequisitionItemsDelete, self::RequisitionItemsApproval => 'Requisitions',


            //PurchaseOrder
            self::PurchaseOrderRead, self::PurchaseOrderWrite, self::PurchaseOrderUpdate, self::PurchaseOrderDelete, self::PurchaseOrderApproval => 'Purchase Order',

            //Tendering
            self::TenderRead, self::TenderWrite, self::TenderUpdate, self::TenderDelete, self::TenderApproval => 'Tenders',
            self::BidSubmissionRead, self::BidSubmissionWrite, self::BidSubmissionUpdate, self::BidSubmissionDelete, => 'Tender Bid Submission',
            self::TenderInvitationRead, self::TenderInvitationWrite, self::TenderInvitationUpdate, self::TenderInvitationDelete, => 'Tender Invitation',
            self::VendorClarificationsRead, self::VendorClarificationsWrite, self::VendorClarificationsUpdate, self::VendorClarificationsDelete, => 'Vendor Clarifications',
            //Inventory
            self::MasterListView, self::MasterListUpdate, self::MasterListCreate, self::MasterListDestroy => 'Item Master',
            self::ItemCategoryView, self::ItemCategoryUpdate, self::ItemCategoryCreate, self::ItemCategoryDestroy => 'Item Category',
            self::StockItemView, self::StockItemUpdate, self::StockItemCreate, self::StockItemDestroy => 'Stock Item',
            self::StoreView, self::StoreUpdate, self::StoreCreate, self::StoreDestroy => 'Store',
            self::InventoryTypeView, self::InventoryTypeUpdate, self::InventoryTypeCreate, self::InventoryTypeDestroy => 'Inventory Type',
            self::UOMView, self::UOMUpdate, self::UOMCreate, self::UOMDestroy => 'UOM',
            self::ItemTypeView, self::ItemTypeUpdate, self::ItemTypeCreate, self::ItemTypeDestroy => 'Item Type',
            self::InterBranchRequisitionView, self::InterBranchRequisitionUpdate, self::InterBranchRequisitionCreate, self::InterBranchRequisitionDestroy, self::InterBranchRequisitionApproval => 'InterBranch Requisition',
            self::PriceManagementView, self::PriceManagementUpdate, self::PriceManagementCreate, self::PriceManagementDestroy => 'Price Management',
            self::TransactionReceiptView, self::TransactionReceiptUpdate, self::TransactionReceiptCreate, self::TransactionReceiptDestroy => 'Receipt',
            self::StockTakeView, self::StockTakeCreate, self::StockTakeUpdate, self::StockTakeDestroy => 'Stock Take',
            self::TransactionTransferView, self::TransactionTransferUpdate, self::TransactionTransferCreate, self::TransactionTransferDestroy, self::TransactionTransferApproval => 'Transfer',
            self::StockAdjustmentView, self::StockAdjustmentUpdate, self::StockAdjustmentCreate, self::StockAdjustmentDestroy => 'Stock Adjustment',
            //self::InventoryHoldView, self::InventoryHoldUpdate, self::InventoryHoldCreate, self::InventoryHoldDestroy => 'Inventory Hold',
            self::InventoryHoldReviewView, self::InventoryHoldReviewUpdate, self::InventoryHoldReviewCreate, self::InventoryHoldReviewDestroy => 'Inventory Hold Review',
            self::UOMConversionView, self::UOMConversionUpdate, self::UOMConversionCreate, self::UOMConversionDestroy => 'UOM Conversion',

            //Fleet Management
            self::FleetModelView,self::FleetModelCreate,self::FleetModelUpdate,self::FleetModelDestroy => 'Fleet Model',
            self::FleetMakeView,self::FleetMakeCreate,self::FleetMakeUpdate,self::FleetMakeDestroy => 'Fleet Make',
            self::VehicleRegistryView,self::VehicleRegistryCreate,self::VehicleRegistryUpdate,self::VehicleRegistryDestroy => 'Vehicle Registry',
            self::DriverManagementView,self::DriverManagementCreate,self::DriverManagementUpdate,self::DriverManagementDestroy => 'Driver Management',


            ////////////////////////// Budget and Analytics //////////////////////////////
            self::BudgetSetupView, self::BudgetSetupCreate, self::BudgetSetupUpdate, self::BudgetSetupDelete => 'Budget Setup',

            //Property Management
            self::PropertyCategoryView,self::PropertyCategoryCreate,self::PropertyCategoryUpdate,self::PropertyCategoryDelete => 'Property Category',
            self::PropertyTypeView,self::PropertyTypeCreate,self::PropertyTypeUpdate,self::PropertyTypeDelete => 'Property Type',
            self::PropertyRegistryView, self::PropertyRegistryCreate, self::PropertyRegistryUpdate, self::PropertyRegistryDelete => 'Property Registry',
            self::PropertyStructuralView, self::PropertyStructuralCreate, self::PropertyStructuralUpdate, self::PropertyStructuralDelete => 'Property Structural Mapping',
            self::PropertyAttachmentsView, self::PropertyAttachmentsCreate, self::PropertyAttachmentsUpdate, self::PropertyAttachmentsDelete => 'Property Attachments ',      
            self::TenantMentenanceCreate,self::TenantMentenanceUpdate,self::TenantMentenanceDelete,self::TenantMentenanceView => 'Tenant Maintenance',
            self::TenantClearanceCreate,self::TenantClearanceUpdate,self::TenantClearanceDelete,self::TenantClearanceView=> 'Tenant Clearance',
            self::PropertyNewLeaseCreate,self::PropertyNewLeaseUpdate,self::PropertyNewLeaseDelete,self::PropertyNewLeaseView => 'Property New Lease',
            self::PropertyLeaseTerminationCreate,self::PropertyLeaseTerminationUpdate,self::PropertyLeaseTerminationDelete,self::PropertyLeaseTerminationView => 'Lease Termination',
            self::PropertyLeaseScheduleCreate,self::PropertyLeaseScheduleUpdate,self::PropertyLeaseScheduleDelete,self::PropertyLeaseScheduleView => 'Property Lease Schedule',
            self::PropertyLeaseRenewalCreate,self::PropertyLeaseRenewalUpdate,self::PropertyLeaseRenewalDelete,self::PropertyLeaseRenewalView => 'Property Lease Renewal', 
            self::PropertyInvoiceCreate,self::PropertyInvoiceUpdate,self::PropertyInvoiceDelete,self::PropertyInvoiceView => 'Property Invoice',
            self::PropertyReceiptCreate,self::PropertyReceiptUpdate,self::PropertyReceiptDelete,self::PropertyReceiptView => 'Property Receipt',
            self::PropertyMaintenanceRequestCreate,self::PropertyMaintenanceRequestUpdate,self::PropertyMaintenanceRequestDelete,self::PropertyMaintenanceRequestView => 'Property Maintenance Request',
            self::PropertyMaintenanceAssignCreate,self::PropertyMaintenanceAssignUpdate,self::PropertyMaintenanceAssignDelete,self::PropertyMaintenanceAssignView => 'Property Maintenance Assign',
            self::PropertyMaintenanceWorkCompletionCreate,self::PropertyMaintenanceWorkCompletionUpdate,self::PropertyMaintenanceWorkCompletionDelete,self::PropertyMaintenanceWorkCompletionView => 'Property Maintenance Work Completion',
            default => throw new LogicException("Unhandled PermissionEnum case: {$this->value}"),

            ///////////////////////  Finance   /////////////////////////
            self::FinanceCOAView, self::FinanceCOACreate, self::FinanceCOAUpdate, self::FinanceCOADelete => 'Chart of Accounts',
            self::FinanceGeneralLedgerView, self::FinanceGeneralLedgerCreate, self::FinanceGeneralLedgerUpdate, self::FinanceGeneralLedgerDelete => 'General Ledger',
            self::FinanceAccountsPayableView, self::FinanceAccountsPayableCreate, self::FinanceAccountsPayableUpdate, self::FinanceAccountsPayableDelete => 'Accounts Payable',
            self::FinanceAccountsReceivableView, self::FinanceAccountsReceivableCreate, self::FinanceAccountsReceivableUpdate, self::FinanceAccountsReceivableDelete => 'Accounts Receivable',
            self::FinanceTaxSettingView, self::FinanceTaxSettingCreate, self::FinanceTaxSettingUpdate, self::FinanceTaxSettingDelete => 'Tax Setting',
        };
    }
}
