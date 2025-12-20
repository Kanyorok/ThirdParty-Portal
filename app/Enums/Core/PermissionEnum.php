<?php

namespace App\Enums\Core;

use App\Traits\UsefulEnumTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
    case Users = 'users'; //set branch manager.
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

        //Supplier
    case SupplierRead = 'supplier-read';
    case SupplierWrite = 'supplier-create';
    case SupplierUpdate = 'supplier-update';
    case SupplierDelete = 'supplier-delete';
    case SupplierApprove = 'supplier-approve';

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
    case PlanConsolidationApproval = 'planconsolidation-approval';

        //Procument Plan- Plan Maintain
    case PlanMaintenanceRead = 'planmaintenance-read';
    case PlanMaintenanceWrite = 'planmaintenance-write';
    case PlanMaintenanceUpdate = 'planmaintenance-update';
    case PlanMaintenanceDelete = 'planmaintenance-delete';
    case PlanMaintenanceApproval = 'planmaintenance-approval';

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
    case StockAdjustmentApproval = 'stockAdjustment-approval';


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


        //Property Structure
    case PropertyStructuralCreate = 'propertystructural-create';
    case PropertyStructuralUpdate = 'propertystructural-update';
    case PropertyStructuralDelete = 'propertystructural-delete';
    case PropertyStructuralView = 'propertystructural-view';


        //Pricing and Rates
    case PropertyRateAndPricingCreate = 'propertyrateandpricing-create';
    case PropertyRateAndPricingUpdate = 'propertyrateandpricing-update';
    case PropertyRateAndPricingDelete = 'propertyrateandpricing-delete';
    case PropertyRateAndPricingView = 'propertyrateandpricing-view';


        //Property Attachments
    case PropertyAttachmentsCreate = 'propertyattachments-create';
    case PropertyAttachmentsUpdate = 'propertyattachments-update';
    case PropertyAttachmentsDelete = 'propertyattachments-delete';
    case PropertyAttachmentsView = 'propertyattachments-view';



        //Tenant Mentenance
    case TenantMaintenanceCreate = 'tenantmaintenance-create';
    case TenantMaintenanceUpdate = 'tenantmaintenance-update';
    case TenantMaintenanceDelete = 'tenantmaintenance-delete';
    case TenantMaintenanceView = 'tenantmaintenance-view';

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
    case PropertyLeaseSchedulePrint = 'propertyleaseschedule-print';

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
    case PropertyReceiptPrint = 'propertyreceipt-print';

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
     * ========================================  Insurance  ========================================
     */


        //Bancassurance Referral
    case BancassuranceReferralCreate = 'bancassurancereferral-create';
    case BancassuranceReferralUpdate = 'bancassurancereferral-update';
    case BancassuranceReferralDelete = 'bancassurancereferral-delete';
    case BancassuranceReferralView = 'bancassurancereferral-view';

        //Bancassurance Policy
    case BancassurancePolicyCreate = 'bancassurancepolicy-create';
    case BancassurancePolicyView = 'bancassurancepolicy-view';
    case BancassurancePolicyUpdate = 'bancassurancepolicy-update';
    case BancassurancePolicyDelete = 'bancassurancepolicy-delete';


        // Bancassurance Customers
    case BancassuranceCustomersCreate = 'bancassurancecustomers-create';
    case BancassuranceCustomersView = 'bancassurancecustomers-view';
    case BancassuranceCustomersUpdate = 'bancassurancecustomers-update';
    case BancassuranceCustomersDelete = 'bancassurancecustomers-delete';

        // Bancassurance Customers Contacts
    case BancassuranceCustomersContactsCreate = 'bancassurancecustomerscontacts-create';
    case BancassuranceCustomersContactsView = 'bancassurancecustomerscontacts-view';
    case BancassuranceCustomersContactsUpdate = 'bancassurancecustomerscontacts-update';
    case BancassuranceCustomersContactsDelete = 'bancassurancecustomerscontacts-delete';


        // Bancassurance Customers Beneficiaries
    case BancassuranceCustomersBeneficiariesCreate = 'bancassurancecustomersbeneficiaries-create';
    case BancassuranceCustomersBeneficiariesView = 'bancassurancecustomersbeneficiaries-view';
    case BancassuranceCustomersBeneficiariesUpdate = 'bancassurancecustomersbeneficiaries-update';
    case BancassuranceCustomersBeneficiariesDelete = 'bancassurancecustomersbeneficiaries-delete';

        // Bancassurance Premium Payments
    case BancassurancePremiumPaymentsView = 'bancassurancepremiumpayments-view';
    case BancassurancePremiumPaymentsCreate = 'bancassurancepremiumpayments-create';
    case BancassurancePremiumPaymentsUpdate = 'bancassurancepremiumpayments-update';
    case BancassurancePremiumPaymentsDelete = 'bancassurancepremiumpayments-delete';

        //Bancasurance Underwriting
    case BancassuranceUnderwritingView = 'bancassuranceunderwriting-view';
    case BancassuranceUnderwritingCreate = 'bancassuranceunderwriting-create';
    case BancassuranceUnderwritingUpdate = 'bancassuranceunderwriting-update';
    case BancassuranceUnderwritingDelete = 'bancassuranceunderwriting-delete';


        //Bancasurance Claim
    case BancassuranceClaimView = 'bancassuranceclaim-view';
    case BancassuranceClaimCreate = 'bancassuranceclaim-create';
    case BancassuranceClaimDelete = 'bancassuranceclaim-delete';
    case BancassuranceClaimUpdate = 'bancassuranceclaim-update';

        //Bancasurance payment
    case BancassurancePaymentView = 'bancassurancepayment-view';
    case BancassurancePaymentCreate = 'bancassurancepayment-create';
    case BancassurancePaymentDelete = 'bancassurancepayment-delete';
    case BancassurancePaymentUpdate = 'bancassurancepayment-update';

        // Medical Fund Management
    case MedicalFundView = 'medicalfund-view';
    case MedicalFundCreate = 'medicalfund-create';
    case MedicalFundUpdate = 'medicalfund-update';
    case MedicalFundDelete = 'medicalfund-delete';

        // Medical Fund Contribution
    case MedicalFundContributionView = 'medicalfundcontribution-view';
    case MedicalFundContributionCreate = 'medicalfundcontribution-create';
    case MedicalFundContributionUpdate = 'medicalfundcontribution-update';
    case MedicalFundContributionDelete = 'medicalfundcontribution-delete';

        // Medical Fund Beneficiary
    case MedicalFundBeneficiaryView = 'medicalfundbeneficiary-view';
    case MedicalFundBeneficiaryCreate = 'medicalfundbeneficiary-create';
    case MedicalFundBeneficiaryUpdate = 'medicalfundbeneficiary-update';
    case MedicalFundBeneficiaryDelete = 'medicalfundbeneficiary-delete';


        // Insurance Provider
    case InsuranceProviderView = 'insuranceprovider-view';
    case InsuranceProviderCreate = 'insuranceprovider-create';
    case InsuranceProviderUpdate = 'insuranceprovider-update';
    case InsuranceProviderDelete = 'insuranceprovider-delete';

        // Insurance Product
    case InsuranceProductView = 'insuranceproduct-view';
    case InsuranceProductCreate = 'insuranceproduct-create';
    case InsuranceProductUpdate = 'insuranceproduct-update';
    case InsuranceProductDelete = 'insuranceproduct-delete';


        // Insurance Product Rider
    case InsuranceProductRiderView = 'insuranceproductrider-view';
    case InsuranceProductRiderCreate = 'insuranceproductrider-create';
    case InsuranceProductRiderUpdate = 'insuranceproductrider-update';
    case InsuranceProductRiderDelete = 'insuranceproductrider-delete';

        // Insurance Pricing Rule
    case InsurancePricingRuleView = 'insurancepricingrule-view';
    case InsurancePricingRuleCreate = 'insurancepricingrule-create';
    case InsurancePricingRuleUpdate = 'insurancepricingrule-update';
    case InsurancePricingRuleDelete = 'insurancepricingrule-delete';

        //Insurance Claim Closure
    case InsuranceClaimClosureView = 'insuranceclaimclosure-view';
    case InsuranceClaimClosureCreate = 'insuranceclaimclosure-create';
    case InsuranceClaimClosureUpdate = 'insuranceclaimclosure-update';
    case InsuranceClaimClosureDelete = 'insuranceclaimclosure-delete';

        // Commission Rule
    case CommissionRuleView = 'commissionrule-view';
    case CommissionRuleCreate = 'commissionrule-create';
    case CommissionRuleUpdate = 'commissionrule-update';
    case CommissionRuleDelete = 'commissionrule-delete';




        /*
     *
     * ========================================  Human Resource management  ========================================
     */
    case Departments = 'department';
    case EmployeesView = 'employee-read';
    case EmployeesCreate = 'employee-create';
    case EmployeesUpdate = 'employee-update';
    case EmployeesDelete = 'employee-delete';


    /** ========================================  Budget and Analytics  ========================================*/
    case BudgetSetupView = 'budgetSetup-view';
    case BudgetSetupCreate = 'budgetSetup-create';
    case BudgetSetupUpdate = 'budgetSetup-update';
    case BudgetSetupDelete = 'budgetSetup-delete';

    case BudgetActivityView = 'budgetActivity-view';
    case BudgetActivityCreate = 'budgetActivity-create';
    case BudgetActivityUpdate = 'budgetActivity-update';
    case BudgetActivityDelete = 'budgetActivity-delete';

    case NewBudgetView = 'newBudget-view';
    case NewBudgetCreate = 'newBudget-create';
    case NewBudgetUpdate = 'newBudget-update';
    case NewBudgetDelete = 'newBudget-delete';

    case BudgetProjectionView = 'budgetProjection-view';
    case BudgetProjectionCreate = 'budgetProjection-create';
    case BudgetProjectionUpdate = 'budgetProjection-update';
    case BudgetProjectionDelete = 'budgetProjection-delete';

    case BudgetEntryByLineView = 'budgetEntryByLine-view';
    case BudgetEntryByLineCreate = 'budgetEntryByLine-create';
    case BudgetEntryByLineUpdate = 'budgetEntryByLine-update';
    case BudgetEntryByLineDelete = 'budgetEntryByLine-delete';

    case BudgetReallocationView = 'budgetReallocation-view';
    case BudgetReallocationCreate = 'budgetReallocation-create';
    case BudgetReallocationUpdate = 'budgetReallocation-update';
    case BudgetReallocationDelete = 'budgetReallocation-delete';

    case BudgetConsolidationView = 'budgetConsolidation-view';
    case ApproveNewBudget = 'approveNewBudget';
    case ApproveReallocation = 'approveReallocation';


    /** ======================================== Document Management System ========================================*/

    case DMSView = 'dms-view';
    case DMSBulkUpload = 'dms-bulkUpload';

    case DMSLegalHoldCreate = 'dmsLegalHold-create';
    case DMSLegalHoldView = 'dmsLegalHold-view';
    case DMSLegalHoldRelease = 'dmsLegalHold-release';


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

        //GL Mapping
    case FinanceGLMappingView = 'financeGLMapping-view';
    case FinanceGLMappingCreate = 'financeGLMapping-create';
    case FinanceGLMappingUpdate = 'financeGLMapping-update';
    case FinanceGLMappingDelete = 'financeGLMapping-delete';

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

        //Credit Note
    case CreditNoteView = 'creditNote-view';
    case CreditNoteCreate = 'creditNote-create';
    case CreditNoteUpdate = 'creditNote-update';
    case CreditNoteDelete = 'creditNote-delete';

        //Payment Voucher
    case PaymentVoucherView = 'paymentVoucher-view';
    case PaymentVoucherCreate = 'paymentVoucher-create';
    case PaymentVoucherUpdate = 'paymentVoucher-update';
    case PaymentVoucherDelete = 'paymentVoucher-delete';

        //Payment Processing
    case PaymentProcessingView = 'paymentProcessing-view';
    case PaymentProcessingCreate = 'paymentProcessing-create';
    case PaymentProcessingUpdate = 'paymentProcessing-update';
    case PaymentProcessingDelete = 'paymentProcessing-delete';

        //Receivables - Receipt Posting
    case ReceiptPostingView = 'receiptPosting-view';
    case ReceiptPostingCreate = 'receiptPosting-create';
    case ReceiptPostingUpdate = 'receiptPosting-update';
    case ReceiptPostingDelete = 'receiptPosting-delete';

        //Receivables - Debit Note
    case DebitNoteView = 'debitNote-view';
    case DebitNoteCreate = 'debitNote-create';
    case DebitNoteUpdate = 'debitNote-update';
    case DebitNoteDelete = 'debitNote-delete';

        //Tax Setting
    case FinanceTaxSettingView = 'financeTaxSetting-view';
    case FinanceTaxSettingCreate = 'financeTaxSetting-create';
    case FinanceTaxSettingUpdate = 'financeTaxSetting-update';
    case FinanceTaxSettingDelete = 'financeTaxSetting-delete';

        /*
     *
     * ========================================  Main Settings  ========================================
     */

        //Credit Management
    case FinanceCreditManagementView = 'financeCreditManagement-view';
    case FinanceCreditManagementCreate = 'financeCreditManagement-create';
    case FinanceCreditManagementUpdate = 'financeCreditManagement-update';
    case FinanceCreditManagementDelete = 'financeCreditManagement-delete';

        //Posting
    case FinanceJournalPosting = 'financeJournal-financeJournal';
    case FinanceAPInvoicePosting = 'financeAPInvoice-financeAPInvoice';
    case FinanceARInvoicePosting = 'financeARInvoice-financeARInvoice'; // AR Account Receivables
    case FinanceCreditNotePosting = 'financeCreditNote-financeCreditNote';
    case FinanceDebitNotePosting = 'financeDebitNote-financeDebitNote';
    case FinanceVoucherPosting = 'financeVoucher-financeVoucher';
    case FinancePaymentProcessingPosting = 'financePaymentProcessing-financePaymentProcessing';
    case FinanceReceiptPosting = 'financeReceipt-financeReceipt';


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

    case FleetVehicleView = 'fleetVehicle-view';
    case FleetVehicleCreate = 'fleetVehicle-create';
    case FleetVehicleUpdate = 'fleetVehicle-update';
    case FleetVehicleDestroy = 'fleetVehicle-destroy';


    case FleetInsuranceTrackerView = 'fleetInsuranceTracker-view';
    case FleetInsuranceTrackerCreate = 'fleetInsuranceTracker-create';
    case FleetInsuranceTrackerUpdate = 'fleetInsuranceTracker-update';
    case FleetInsuranceTrackerDestroy = 'fleetInsuranceTracker-destroy';

    case FleetInspectionScheduleView = 'fleetInspectionSchedule-view';
    case FleetInspectionScheduleCreate = 'fleetInspectionSchedule-create';
    case FleetInspectionScheduleUpdate = 'fleetInspectionSchedule-update';
    case FleetInspectionScheduleDestroy = 'fleetInspectionSchedule-destroy';

    case FleetDriverView = 'fleetDriver-view';
    case FleetDriverCreate = 'fleetDriver-create';
    case FleetDriverUpdate = 'fleetDriver-update';
    case FleetDriverDestroy = 'fleetDriver-destroy';

    case ContractedDriverView = 'contractedDriver-view';
    case ContractedDriverCreate = 'contractedDriver-create';
    case ContractedDriverUpdate = 'contractedDriver-update';
    case ContractedDriverDestroy = 'contractedDriver-destroy';

    case FleetTripLogView = 'fleetTripLog-view';
    case FleetTripLogCreate = 'fleetTripLog-create';
    case FleetTripLogUpdate = 'fleetTripLog-update';
    case FleetTripLogDestroy = 'fleetTripLog-destroy';

    case FleetRoutePlanView = 'fleetRoutePlan-view';
    case FleetRoutePlanCreate = 'fleetRoutePlan-create';
    case FleetRoutePlanUpdate = 'fleetRoutePlan-update';
    case FleetRoutePlanDestroy = 'fleetRoutePlan-destroy';

    case FleetVehicleRequestView = 'fleetVehicleRequest-view';
    case FleetVehicleRequestCreate = 'fleetVehicleRequest-create';
    case FleetVehicleRequestUpdate = 'fleetVehicleRequest-update';
    case FleetVehicleRequestDestroy = 'fleetVehicleRequest-destroy';
    case FleetVehicleRequestApproval = 'fleetVehicleRequest-approval';

    case FleetMaintenanceScheduleView = 'fleetMaintenanceSchedule-view';
    case FleetMaintenanceScheduleCreate = 'fleetMaintenanceSchedule-create';
    case FleetMaintenanceScheduleUpdate = 'fleetMaintenanceSchedule-update';
    case FleetMaintenanceScheduleDestroy = 'fleetMaintenanceSchedule-destroy';
    case FleetMaintenanceScheduleCancel = 'fleetMaintenanceSchedule-cancel';

    case FleetRepairLogView = 'fleetRepairLog-view';
    case FleetRepairLogCreate = 'fleetRepairLog-create';
    case FleetRepairLogUpdate = 'fleetRepairLog-update';
    case FleetRepairLogDestroy = 'fleetRepairLog-destroy';

    case FleetServiceAlertView = 'fleetServiceAlert-view';
    case FleetServiceAlertAcknowledge = 'fleetServiceAlert-acknowledge';

    case VehicleInspectionView = 'vehicleInspection-view';
    case VehicleInspectionCreate = 'vehicleInspection-create';
    case VehicleInspectionUpdate = 'vehicleInspection-update';
    case VehicleInspectionDestroy = 'vehicleInspection-destroy';



        /*
*
* ========================================  Legal  ========================================
*/
        //Document Registry and Contracts Creation
    case ContractView = 'contract-view';
    case ContractCreate = 'contract-create';
    case ContractUpdate = 'contract-update';
    case ContractDelete = 'contract-delete';

        //Disputes and Ltigations
    case DisputeLitigationView = 'disputelitigation-view';
    case DisputeLitigationCreate = 'disputelitigation-create';
    case DisputeLitigationUpdate = 'disputelitigation-update';
    case DisputeLitigationDelete = 'disputelitigation-delete';

        //LegalObligations
    case LegalObligationView = 'legalobligation-view';
    case LegalObligationCreate = 'legalobligation-create';
    case LegalObligationUpdate = 'legalobligation-update';
    case LegalObligationDelete = 'legalobligation-delete';

        //Legal Search
    case LegalSearchView = 'legalsearch-view';
    case LegalSearchCreate = 'legalsearch-create';
    case LegalSearchUpdate = 'legalsearch-update';
    case LegalSearchDelete = 'legalsearch-delete';

        //LoanSecurity
    case LoanSecurityView = 'loansecurity-view';
    case LoanSecurityCreate = 'loansecurity-create';
    case LoanSecurityUpdate = 'loansecurity-update';
    case LoanSecurityDelete = 'loansecurity-delete';

        //IntellectualProperty
    case IntellectualPropertyView = 'intellectualproperty-view';
    case IntellectualPropertyCreate = 'intellectualproperty-create';
    case IntellectualPropertyUpdate = 'intellectualproperty-update';
    case IntellectualPropertyDelete = 'intellectualproperty-delete';





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

            [self::DMSView, self::DMSBulkUpload, self::DMSLegalHoldCreate, self::DMSLegalHoldView, self::DMSLegalHoldRelease],

            [self::DepartmentNeedsRead, self::DepartmentNeedsWrite, self::DepartmentNeedsUpdate, self::DepartmentNeedsDelete, self::DepartmentNeedsApproval,],
            [self::ProcurementMethodRead, self::ProcurementMethodWrite,],


            [self::EmployeesView, self::EmployeesCreate, self::EmployeesUpdate, self::EmployeesDelete, self::Departments],
            [self::SupplierRead, self::SupplierWrite, self::SupplierUpdate, self::SupplierDelete, self::SupplierApprove],
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

            [self::StockAdjustmentView, self::StockAdjustmentUpdate, self::StockAdjustmentCreate, self::StockAdjustmentDestroy, self::StockAdjustmentApproval],
            [self::InventoryHoldReviewView, self::InventoryHoldReviewUpdate, self::InventoryHoldReviewCreate, self::InventoryHoldReviewDestroy],
            [self::UOMConversionView, self::UOMConversionUpdate, self::UOMConversionCreate, self::UOMConversionDestroy],

            [self::PlanConsolidationRead, self::PlanConsolidationWrite, self::PlanConsolidationUpdate, self::PlanConsolidationDelete],
            [self::PlanLineItemsRead, self::PlanLineItemsWrite, self::PlanLineItemsUpdate, self::PlanLineItemsDelete],
            [self::BidSubmissionRead, self::BidSubmissionWrite, self::BidSubmissionUpdate, self::BidSubmissionDelete],
            [self::TenderInvitationRead, self::TenderInvitationWrite, self::TenderInvitationUpdate, self::TenderInvitationDelete],
            [self::VendorClarificationsRead, self::VendorClarificationsWrite, self::VendorClarificationsUpdate, self::VendorClarificationsDelete],
            [self::PlanMaintenanceRead, self::PlanMaintenanceWrite, self::PlanMaintenanceUpdate, self::PlanMaintenanceDelete, self::PlanMaintenanceApproval],
            [self::PlanManualInputRead, self::PlanManualInputWrite, self::PlanManualInputUpdate, self::PlanManualInputDelete],
            [self::PlanEditRead, self::PlanEditWrite, self::PlanEditUpdate, self::PlanEditDelete],
            [self::StockTakeView, self::StockTakeCreate, self::StockTakeUpdate, self::StockTakeDestroy],

            /////////////////////////// Budget and Analytics  ///////////////////////
            [self::BudgetSetupView, self::BudgetSetupCreate, self::BudgetSetupUpdate, self::BudgetSetupDelete],
            [self::BudgetActivityView, self::BudgetActivityCreate, self::BudgetActivityUpdate, self::BudgetActivityDelete],
            [self::NewBudgetView, self::NewBudgetCreate, self::NewBudgetUpdate, self::NewBudgetDelete],
            [self::BudgetProjectionView, self::BudgetProjectionCreate, self::BudgetProjectionUpdate, self::BudgetProjectionDelete],
            [self::BudgetEntryByLineView, self::BudgetEntryByLineCreate, self::BudgetEntryByLineUpdate, self::BudgetEntryByLineDelete],
            [self::BudgetReallocationView, self::BudgetReallocationCreate, self::BudgetReallocationUpdate, self::BudgetReallocationDelete],
            [self::BudgetConsolidationView],
            [self::ApproveNewBudget],
            [self::ApproveReallocation],


            ///////////////////////  Finance  /////////////////////////////////////
            [self::FinanceCOAView, self::FinanceCOACreate, self::FinanceCOAUpdate, self::FinanceCOADelete],
            [self::FinanceGeneralLedgerView, self::FinanceGeneralLedgerCreate, self::FinanceGeneralLedgerUpdate, self::FinanceGeneralLedgerDelete],
            [self::FinanceGLMappingView, self::FinanceGLMappingCreate, self::FinanceGLMappingUpdate, self::FinanceGLMappingDelete],
            [self::FinanceAccountsPayableView, self::FinanceAccountsPayableCreate, self::FinanceAccountsPayableUpdate, self::FinanceAccountsPayableDelete],
            [self::FinanceAccountsReceivableView, self::FinanceAccountsReceivableCreate, self::FinanceAccountsReceivableUpdate, self::FinanceAccountsReceivableDelete],
            [self::CreditNoteView, self::CreditNoteCreate, self::CreditNoteUpdate, self::CreditNoteDelete],
            [self::PaymentVoucherView, self::PaymentVoucherCreate, self::PaymentVoucherUpdate, self::PaymentVoucherDelete],
            [self::PaymentProcessingView, self::PaymentProcessingCreate, self::PaymentProcessingUpdate, self::PaymentProcessingDelete],
            [self::ReceiptPostingView, self::ReceiptPostingCreate, self::ReceiptPostingUpdate, self::ReceiptPostingDelete],
            [self::DebitNoteView, self::DebitNoteCreate, self::DebitNoteUpdate, self::DebitNoteDelete],
            [self::FinanceTaxSettingView, self::FinanceTaxSettingCreate, self::FinanceTaxSettingUpdate, self::FinanceTaxSettingDelete],
            [self::FinanceCreditManagementView, self::FinanceCreditManagementCreate, self::FinanceCreditManagementUpdate, self::FinanceCreditManagementDelete],
            [self::FinanceJournalPosting, self::FinanceAPInvoicePosting, self::FinanceARInvoicePosting, self::FinanceCreditNotePosting, self::FinanceDebitNotePosting, self::FinanceVoucherPosting, self::FinancePaymentProcessingPosting, self::FinanceReceiptPosting],

            ///////////////////////  Settings  /////////////////////////////////////
            // [self::WorkflowlimitManagerialLevel, self::WorkflowlimitOperationalLevel, self::WorkflowlimitView, self::WorkflowlimitCreate, self::WorkflowlimitUpdate, self::WorkflowlimitDelete],      

            ///////////////////////  Fleet Management  /////////////////////////////////////
            [self::FleetModelView, self::FleetModelCreate, self::FleetModelUpdate, self::FleetModelDestroy],
            [self::FleetMakeView, self::FleetMakeCreate, self::FleetMakeUpdate, self::FleetMakeDestroy],


            //Property Management
            [self::PropertyCategoryView, self::PropertyCategoryCreate, self::PropertyCategoryUpdate, self::PropertyCategoryDelete],
            [self::PropertyTypeView, self::PropertyTypeCreate, self::PropertyTypeUpdate, self::PropertyTypeDelete],
            [self::PropertyRegistryView, self::PropertyRegistryCreate, self::PropertyRegistryUpdate, self::PropertyRegistryDelete],
            [self::PropertyStructuralView, self::PropertyStructuralCreate, self::PropertyStructuralUpdate, self::PropertyStructuralDelete],
            [self::PropertyAttachmentsView, self::PropertyAttachmentsCreate, self::PropertyAttachmentsUpdate, self::PropertyAttachmentsDelete],
            [self::TenantMaintenanceCreate, self::TenantMaintenanceUpdate, self::TenantMaintenanceDelete, self::TenantMaintenanceView],
            [self::TenantClearanceCreate, self::TenantClearanceUpdate, self::TenantClearanceDelete, self::TenantClearanceView],
            [self::PropertyNewLeaseCreate, self::PropertyNewLeaseUpdate, self::PropertyNewLeaseDelete, self::PropertyNewLeaseView],
            [self::PropertyLeaseTerminationCreate, self::PropertyLeaseTerminationUpdate, self::PropertyLeaseTerminationDelete, self::PropertyLeaseTerminationView],
            [self::PropertyLeaseScheduleCreate, self::PropertyLeaseScheduleUpdate, self::PropertyLeaseScheduleDelete, self::PropertyLeaseScheduleView, self::PropertyLeaseSchedulePrint],
            [self::PropertyLeaseRenewalCreate, self::PropertyLeaseRenewalUpdate, self::PropertyLeaseRenewalDelete, self::PropertyLeaseRenewalView],
            [self::PropertyInvoiceCreate, self::PropertyInvoiceUpdate, self::PropertyInvoiceDelete, self::PropertyInvoiceView],
            [self::PropertyReceiptCreate, self::PropertyReceiptUpdate, self::PropertyReceiptDelete, self::PropertyReceiptView, self::PropertyReceiptPrint],
            [self::PropertyMaintenanceRequestCreate, self::PropertyMaintenanceRequestUpdate, self::PropertyMaintenanceRequestDelete, self::PropertyMaintenanceRequestView],
            [self::PropertyMaintenanceAssignCreate, self::PropertyMaintenanceAssignUpdate, self::PropertyMaintenanceAssignDelete, self::PropertyMaintenanceAssignView],
            [self::PropertyMaintenanceWorkCompletionCreate, self::PropertyMaintenanceWorkCompletionUpdate, self::PropertyMaintenanceWorkCompletionDelete, self::PropertyMaintenanceWorkCompletionView],
            [self::PropertyRateAndPricingCreate, self::PropertyRateAndPricingUpdate, self::PropertyRateAndPricingDelete, self::PropertyRateAndPricingView],


            //Fleet
            [self::FleetModelView, self::FleetModelCreate, self::FleetModelUpdate, self::FleetModelDestroy],
            [self::FleetMakeView, self::FleetMakeCreate, self::FleetMakeUpdate, self::FleetMakeDestroy],
            [self::FleetVehicleView, self::FleetVehicleCreate, self::FleetVehicleUpdate, self::FleetVehicleDestroy],
            [self::FleetInsuranceTrackerView, self::FleetInsuranceTrackerCreate, self::FleetInsuranceTrackerUpdate, self::FleetInsuranceTrackerDestroy],
            [self::FleetInspectionScheduleView, self::FleetInspectionScheduleCreate, self::FleetInspectionScheduleUpdate, self::FleetInspectionScheduleDestroy],
            [self::FleetDriverView, self::FleetDriverCreate, self::FleetDriverUpdate, self::FleetDriverDestroy],
            [self::FleetVehicleRequestView, self::FleetVehicleRequestApproval, self::FleetVehicleRequestCreate, self::FleetVehicleRequestUpdate, self::FleetVehicleRequestDestroy],
            [self::ContractedDriverView, self::ContractedDriverCreate, self::ContractedDriverUpdate, self::ContractedDriverDestroy],
            [self::FleetMaintenanceScheduleView, self::FleetMaintenanceScheduleCreate, self::FleetMaintenanceScheduleUpdate, self::FleetMaintenanceScheduleDestroy, self::FleetMaintenanceScheduleCancel],
            [self::FleetRepairLogView, self::FleetRepairLogCreate, self::FleetRepairLogUpdate, self::FleetRepairLogDestroy],
            [self::FleetTripLogView, self::FleetTripLogCreate, self::FleetTripLogUpdate, self::FleetTripLogDestroy],
            [self::FleetRoutePlanView, self::FleetRoutePlanCreate, self::FleetRoutePlanUpdate, self::FleetRoutePlanDestroy],
            [self::FleetServiceAlertView, self::FleetServiceAlertAcknowledge],
            [self::VehicleInspectionView, self::VehicleInspectionCreate, self::VehicleInspectionUpdate, self::VehicleInspectionDestroy],


            //Bank Assurance
            [self::BancassuranceReferralCreate, self::BancassuranceReferralView, self::BancassuranceReferralUpdate, self::BancassuranceReferralDelete],
            [self::BancassurancePolicyCreate, self::BancassurancePolicyView, self::BancassurancePolicyUpdate, self::BancassurancePolicyDelete],
            [self::BancassuranceCustomersCreate, self::BancassuranceCustomersView, self::BancassuranceCustomersUpdate, self::BancassuranceCustomersDelete],
            [self::BancassuranceCustomersContactsCreate, self::BancassuranceCustomersContactsView, self::BancassuranceCustomersContactsUpdate, self::BancassuranceCustomersContactsDelete],
            [self::BancassuranceCustomersBeneficiariesCreate, self::BancassuranceCustomersBeneficiariesView, self::BancassuranceCustomersBeneficiariesUpdate, self::BancassuranceCustomersBeneficiariesDelete],
            [self::BancassurancePremiumPaymentsView, self::BancassurancePremiumPaymentsCreate, self::BancassurancePremiumPaymentsUpdate, self::BancassurancePremiumPaymentsDelete],
            [self::BancassuranceUnderwritingView, self::BancassuranceUnderwritingCreate, self::BancassuranceUnderwritingUpdate, self::BancassuranceUnderwritingDelete],
            [self::BancassuranceClaimView, self::BancassuranceClaimUpdate, self::BancassuranceClaimCreate, self::BancassuranceClaimDelete],
            [self::MedicalFundView, self::MedicalFundCreate, self::MedicalFundUpdate, self::MedicalFundDelete],
            [self::MedicalFundContributionView, self::MedicalFundContributionCreate, self::MedicalFundContributionUpdate, self::MedicalFundContributionDelete],
            [self::MedicalFundBeneficiaryView, self::MedicalFundBeneficiaryCreate, self::MedicalFundBeneficiaryUpdate, self::MedicalFundBeneficiaryDelete],
            [self::BancassurancePaymentView, self::BancassurancePaymentCreate, self::BancassurancePaymentDelete, self::BancassurancePaymentUpdate],
            [self::InsuranceProviderView, self::InsuranceProviderCreate, self::InsuranceProviderUpdate, self::InsuranceProviderDelete],
            [self::InsuranceProductView, self::InsuranceProductCreate, self::InsuranceProductUpdate, self::InsuranceProductDelete],
            [self::InsuranceProductRiderView, self::InsuranceProductRiderCreate, self::InsuranceProductRiderUpdate, self::InsuranceProductRiderDelete],
            [self::InsurancePricingRuleView, self::InsurancePricingRuleCreate, self::InsurancePricingRuleUpdate, self::InsurancePricingRuleDelete],
            [self::InsuranceClaimClosureView, self::InsuranceClaimClosureCreate, self::InsuranceClaimClosureUpdate, self::InsuranceClaimClosureDelete],
            [self::CommissionRuleView, self::CommissionRuleCreate, self::CommissionRuleUpdate, self::CommissionRuleDelete],

            ///////////////////////  Fleet Management  /////////////////////////////////////
            [self::FleetModelView, self::FleetModelCreate, self::FleetModelUpdate, self::FleetModelDestroy],
            [self::FleetMakeView, self::FleetMakeCreate, self::FleetMakeUpdate, self::FleetMakeDestroy],

            ///////////////////////  Legal  ///////////////////////////////////////
            [self::ContractView, self::ContractCreate, self::ContractUpdate, self::ContractDelete],
            [self::DisputeLitigationView, self::DisputeLitigationCreate, self::DisputeLitigationUpdate, self::DisputeLitigationDelete],
            [self::LegalObligationView, self::LegalObligationCreate, self::LegalObligationUpdate, self::LegalObligationDelete],
            [self::LegalSearchView, self::LegalSearchCreate, self::LegalSearchUpdate, self::LegalSearchDelete],
            [self::LoanSecurityView, self::LoanSecurityCreate, self::LoanSecurityUpdate, self::LoanSecurityDelete],
            [self::IntellectualPropertyView, self::IntellectualPropertyCreate, self::IntellectualPropertyUpdate, self::IntellectualPropertyDelete],


        ]);
    }

    public static function approvals(): Collection
    {
        return collect([
            self::MarketingPlannerApproval, /* self::MarketingListApproval,*/
            self::TicketApproval,
            self::CampaignApproval,
            self::SurveyApproval,
            self::Ceo,
            self::MarketingManager,
            self::PurchaseOrderApproval,
            self::RequisitionApproval,
            self::RequisitionItemsApproval,
            self::DepartmentNeedsApproval,
            self::TenderApproval,
            self::SupplierApprove,
            self::PurchaseOrderApproval,
            self::RequisitionApproval,
            self::RequisitionItemsApproval
        ]);
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
            // self::WorkflowlimitManagerialLevel, self::WorkflowlimitOperationalLevel, self::WorkflowlimitView, self::WorkflowlimitCreate, self::WorkflowlimitUpdate, self::WorkflowlimitDelete
            => ModulesEnum::Settings,

            self::DMSView, self::DMSBulkUpload, self::DMSLegalHoldCreate, self::DMSLegalHoldView, self::DMSLegalHoldRelease => ModulesEnum::DMS,

            //Procurement
            self::RequisitionRead, self::RequisitionWrite, self::RequisitionUpdate, self::RequisitionDelete, self::RequisitionApproval,
            self::RequisitionItemsRead, self::RequisitionItemsWrite, self::RequisitionItemsUpdate, self::RequisitionItemsDelete, self::RequisitionItemsApproval,
            self::PurchaseOrderRead, self::PurchaseOrderWrite, self::PurchaseOrderUpdate, self::PurchaseOrderDelete, self::PurchaseOrderApproval,
            self::RfqRead, self::RfqWrite, self::RfqUpdate, self::RfqApproval, self::RfqDelete,
            self::DepartmentNeedsRead, self::DepartmentNeedsWrite, self::DepartmentNeedsUpdate, self::DepartmentNeedsDelete, self::DepartmentNeedsApproval,
            self::PlanConsolidationRead, self::PlanConsolidationApproval, self::PlanConsolidationWrite, self::PlanConsolidationUpdate, self::PlanConsolidationDelete,
            self::ProcurementMethodRead, self::ProcurementMethodWrite,
            self::TenderRead, self::TenderWrite, self::TenderUpdate, self::TenderDelete, self::TenderApproval,
            self::SupplierRead, self::SupplierWrite, self::SupplierUpdate, self::SupplierDelete, self::SupplierApprove,
            self::PlanLineItemsRead, self::PlanLineItemsWrite, self::PlanLineItemsUpdate, self::PlanLineItemsDelete,
            self::BidSubmissionRead, self::BidSubmissionWrite, self::BidSubmissionUpdate, self::BidSubmissionDelete,
            self::TenderInvitationRead, self::TenderInvitationWrite, self::TenderInvitationUpdate, self::TenderInvitationDelete,
            self::VendorClarificationsRead, self::VendorClarificationsWrite, self::VendorClarificationsUpdate, self::VendorClarificationsDelete,
            self::PlanMaintenanceRead, self::PlanMaintenanceWrite, self::PlanMaintenanceUpdate, self::PlanMaintenanceDelete, self::PlanMaintenanceApproval,
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
            self::StockAdjustmentView, self::StockAdjustmentUpdate, self::StockAdjustmentCreate, self::StockAdjustmentDestroy, self::StockAdjustmentApproval => ModulesEnum::Inventory,
            //Fleet Management
            self::FleetModelView, self::FleetModelCreate, self::FleetModelUpdate, self::FleetModelDestroy,
            self::FleetMakeView, self::FleetMakeCreate, self::FleetMakeUpdate, self::FleetMakeDestroy,
            self::FleetVehicleView, self::FleetVehicleCreate, self::FleetVehicleUpdate, self::FleetVehicleDestroy,
            self::FleetInsuranceTrackerView, self::FleetInsuranceTrackerCreate, self::FleetInsuranceTrackerUpdate, self::FleetInsuranceTrackerDestroy,
            self::FleetInspectionScheduleView, self::FleetInspectionScheduleCreate, self::FleetInspectionScheduleUpdate, self::FleetInspectionScheduleDestroy,
            self::FleetDriverView, self::FleetDriverCreate, self::FleetDriverUpdate, self::FleetDriverDestroy,
            self::FleetVehicleRequestView, self::FleetVehicleRequestApproval, self::FleetVehicleRequestCreate, self::FleetVehicleRequestUpdate, self::FleetVehicleRequestDestroy,
            self::FleetMaintenanceScheduleView, self::FleetMaintenanceScheduleCreate, self::FleetMaintenanceScheduleUpdate, self::FleetMaintenanceScheduleDestroy, self::FleetMaintenanceScheduleCancel,
            self::FleetRepairLogView, self::FleetRepairLogCreate, self::FleetRepairLogUpdate, self::FleetRepairLogDestroy,
            self::ContractedDriverView, self::ContractedDriverCreate, self::ContractedDriverUpdate, self::ContractedDriverDestroy,
            self::FleetServiceAlertView, self::FleetServiceAlertAcknowledge,
            self::FleetRoutePlanView, self::FleetRoutePlanCreate, self::FleetRoutePlanUpdate, self::FleetRoutePlanDestroy,
            self::FleetTripLogView, self::FleetTripLogCreate, self::FleetTripLogUpdate, self::FleetTripLogDestroy,
            self::VehicleInspectionView, self::VehicleInspectionCreate, self::VehicleInspectionUpdate, self::VehicleInspectionDestroy,
            => ModulesEnum::Fleet,


            //Property Management
            self::PropertyCategoryView, self::PropertyCategoryCreate, self::PropertyCategoryUpdate, self::PropertyCategoryDelete,
            self::PropertyTypeView, self::PropertyTypeCreate, self::PropertyTypeUpdate, self::PropertyTypeDelete,
            self::PropertyRegistryView, self::PropertyRegistryCreate, self::PropertyRegistryUpdate, self::PropertyRegistryDelete,
            self::PropertyStructuralView, self::PropertyStructuralCreate, self::PropertyStructuralUpdate, self::PropertyStructuralDelete,
            self::PropertyAttachmentsView, self::PropertyAttachmentsCreate, self::PropertyAttachmentsUpdate, self::PropertyAttachmentsDelete,
            self::TenantMaintenanceCreate, self::TenantMaintenanceUpdate, self::TenantMaintenanceDelete, self::TenantMaintenanceView,
            self::TenantClearanceCreate, self::TenantClearanceUpdate, self::TenantClearanceDelete, self::TenantClearanceView,
            self::PropertyNewLeaseCreate, self::PropertyNewLeaseUpdate, self::PropertyNewLeaseDelete, self::PropertyNewLeaseView,
            self::PropertyLeaseTerminationCreate, self::PropertyLeaseTerminationUpdate, self::PropertyLeaseTerminationDelete, self::PropertyLeaseTerminationView,
            self::PropertyLeaseScheduleCreate, self::PropertyLeaseScheduleUpdate, self::PropertyLeaseScheduleDelete, self::PropertyLeaseScheduleView, self::PropertyLeaseSchedulePrint,
            self::PropertyLeaseRenewalCreate, self::PropertyLeaseRenewalUpdate, self::PropertyLeaseRenewalDelete, self::PropertyLeaseRenewalView,
            self::PropertyInvoiceCreate, self::PropertyInvoiceUpdate, self::PropertyInvoiceDelete, self::PropertyInvoiceView,
            self::PropertyReceiptCreate, self::PropertyReceiptUpdate, self::PropertyReceiptDelete, self::PropertyReceiptView, self::PropertyReceiptPrint,
            self::PropertyMaintenanceRequestCreate, self::PropertyMaintenanceRequestUpdate, self::PropertyMaintenanceRequestDelete, self::PropertyMaintenanceRequestView,
            self::PropertyMaintenanceAssignCreate, self::PropertyMaintenanceAssignUpdate, self::PropertyMaintenanceAssignDelete, self::PropertyMaintenanceAssignView,
            self::PropertyMaintenanceWorkCompletionCreate, self::PropertyMaintenanceWorkCompletionUpdate, self::PropertyMaintenanceWorkCompletionDelete, self::PropertyMaintenanceWorkCompletionView,
            self::PropertyRateAndPricingCreate, self::PropertyRateAndPricingUpdate, self::PropertyRateAndPricingDelete, self::PropertyRateAndPricingView
            => ModulesEnum::Property,
            // default => throw new LogicException("Unhandled PermissionEnum case: {$this->value}"),

            //Insurance
            self::BancassuranceReferralCreate, self::BancassuranceReferralView, self::BancassuranceReferralUpdate, self::BancassuranceReferralDelete,
            self::BancassurancePolicyCreate, self::BancassurancePolicyView, self::BancassurancePolicyUpdate, self::BancassurancePolicyDelete,
            self::BancassuranceCustomersCreate, self::BancassuranceCustomersView, self::BancassuranceCustomersUpdate, self::BancassuranceCustomersDelete,
            self::BancassuranceCustomersContactsCreate, self::BancassuranceCustomersContactsView, self::BancassuranceCustomersContactsUpdate, self::BancassuranceCustomersContactsDelete,
            self::BancassuranceCustomersBeneficiariesCreate, self::BancassuranceCustomersBeneficiariesView, self::BancassuranceCustomersBeneficiariesUpdate, self::BancassuranceCustomersBeneficiariesDelete,
            self::BancassurancePremiumPaymentsView, self::BancassurancePremiumPaymentsCreate, self::BancassurancePremiumPaymentsUpdate, self::BancassurancePremiumPaymentsDelete,
            self::InsuranceProviderView, self::InsuranceProviderCreate, self::InsuranceProviderUpdate, self::InsuranceProviderDelete,
            self::InsuranceProductView, self::InsuranceProductCreate, self::InsuranceProductUpdate, self::InsuranceProductDelete,
            self::InsuranceProductRiderView, self::InsuranceProductRiderCreate, self::InsuranceProductRiderUpdate, self::InsuranceProductRiderDelete,
            self::InsurancePricingRuleView, self::InsurancePricingRuleCreate, self::InsurancePricingRuleUpdate, self::InsurancePricingRuleDelete,
            self::BancassuranceUnderwritingView, self::BancassuranceUnderwritingCreate, self::BancassuranceUnderwritingUpdate, self::BancassuranceUnderwritingDelete,
            self::BancassuranceClaimView, self::BancassuranceClaimUpdate, self::BancassuranceClaimCreate, self::BancassuranceClaimDelete,
            self::MedicalFundView, self::MedicalFundCreate, self::MedicalFundUpdate, self::MedicalFundDelete,
            self::MedicalFundContributionView, self::MedicalFundContributionCreate, self::MedicalFundContributionUpdate, self::MedicalFundContributionDelete,
            self::MedicalFundBeneficiaryView, self::MedicalFundBeneficiaryCreate, self::MedicalFundBeneficiaryUpdate, self::MedicalFundBeneficiaryDelete,
            self::BancassurancePaymentView, self::BancassurancePaymentCreate, self::BancassurancePaymentDelete, self::BancassurancePaymentUpdate,
            self::InsuranceClaimClosureView, self::InsuranceClaimClosureCreate, self::InsuranceClaimClosureUpdate, self::InsuranceClaimClosureDelete,
            self::CommissionRuleView, self::CommissionRuleCreate, self::CommissionRuleUpdate, self::CommissionRuleDelete
            => ModulesEnum::Insurance,



            ///////////////^*********** Budget and Analytics ******************/////////////////
            self::BudgetSetupView, self::BudgetSetupCreate, self::BudgetSetupUpdate, self::BudgetSetupDelete,
            self::BudgetActivityView, self::BudgetActivityCreate, self::BudgetActivityUpdate, self::BudgetActivityDelete,
            self::NewBudgetView, self::NewBudgetCreate, self::NewBudgetUpdate, self::NewBudgetDelete,
            self::BudgetProjectionView, self::BudgetProjectionCreate, self::BudgetProjectionUpdate, self::BudgetProjectionDelete,
            self::BudgetEntryByLineView, self::BudgetEntryByLineCreate, self::BudgetEntryByLineUpdate, self::BudgetEntryByLineDelete,
            self::BudgetReallocationView, self::BudgetReallocationCreate, self::BudgetReallocationUpdate, self::BudgetReallocationDelete,
            self::BudgetConsolidationView,
            self::ApproveNewBudget,
            self::ApproveReallocation
            => ModulesEnum::BudgetLine,


            ////////////////////   Finance   ////////////////////////////
            self::FinanceCOAView, self::FinanceCOACreate, self::FinanceCOAUpdate, self::FinanceCOADelete,
            self::FinanceGeneralLedgerView, self::FinanceGeneralLedgerCreate, self::FinanceGeneralLedgerUpdate, self::FinanceGeneralLedgerDelete,
            self::FinanceGLMappingView, self::FinanceGLMappingCreate, self::FinanceGLMappingUpdate, self::FinanceGLMappingDelete,
            self::FinanceAccountsPayableView, self::FinanceAccountsPayableCreate, self::FinanceAccountsPayableUpdate, self::FinanceAccountsPayableDelete,
            self::FinanceAccountsReceivableView, self::FinanceAccountsReceivableCreate, self::FinanceAccountsReceivableUpdate, self::FinanceAccountsReceivableDelete,
            self::CreditNoteView, self::CreditNoteCreate, self::CreditNoteUpdate, self::CreditNoteDelete,
            self::PaymentVoucherView, self::PaymentVoucherCreate, self::PaymentVoucherUpdate, self::PaymentVoucherDelete,
            self::PaymentProcessingView, self::PaymentProcessingCreate, self::PaymentProcessingUpdate, self::PaymentProcessingDelete,
            self::ReceiptPostingView, self::ReceiptPostingCreate, self::ReceiptPostingUpdate, self::ReceiptPostingDelete,
            self::DebitNoteView, self::DebitNoteCreate, self::DebitNoteUpdate, self::DebitNoteDelete,
            self::FinanceTaxSettingView, self::FinanceTaxSettingCreate, self::FinanceTaxSettingUpdate, self::FinanceTaxSettingDelete,
            self::FinanceCreditManagementView, self::FinanceCreditManagementCreate, self::FinanceCreditManagementUpdate, self::FinanceCreditManagementDelete,
            self::FinanceJournalPosting, self::FinanceAPInvoicePosting, self::FinanceARInvoicePosting, self::FinanceCreditNotePosting, self::FinanceDebitNotePosting, self::FinanceVoucherPosting, self::FinancePaymentProcessingPosting, self::FinanceReceiptPosting,
            => ModulesEnum::Finance,


            ////////////////////   Legal   ////////////////////////////
            self::ContractView, self::ContractCreate, self::ContractUpdate, self::ContractDelete,
            self::DisputeLitigationView, self::DisputeLitigationCreate, self::DisputeLitigationUpdate, self::DisputeLitigationDelete,
            self::LegalObligationView, self::LegalObligationCreate, self::LegalObligationUpdate, self::LegalObligationDelete,
            self::LegalSearchView, self::LegalSearchCreate, self::LegalSearchUpdate, self::LegalSearchDelete,
            self::LoanSecurityView, self::LoanSecurityCreate, self::LoanSecurityUpdate, self::LoanSecurityDelete,
            self::IntellectualPropertyView, self::IntellectualPropertyCreate, self::IntellectualPropertyUpdate, self::IntellectualPropertyDelete,
            => ModulesEnum::Legal,
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
            self::PlanConsolidationRead, self::PlanConsolidationWrite, self::PlanConsolidationUpdate, self::PlanConsolidationDelete => 'Consolodidated Needs',
            self::ProcurementMethodRead, self::ProcurementMethodWrite => 'Procurement Method',
            self::PlanLineItemsRead, self::PlanLineItemsWrite, self::PlanLineItemsUpdate, self::PlanLineItemsDelete, => 'Plan Line Items',
            self::PlanMaintenanceRead, self::PlanMaintenanceWrite, self::PlanMaintenanceUpdate, self::PlanMaintenanceDelete, self::PlanMaintenanceApproval => 'Plan Maintenance',
            self::PlanManualInputRead, self::PlanManualInputWrite, self::PlanManualInputUpdate, self::PlanManualInputDelete, => 'Plan Manual Input',
            self::PlanEditRead, self::PlanEditWrite, self::PlanEditUpdate, self::PlanEditDelete, => 'Plan amendment',
            //Requisition
            self::RequisitionRead, self::RequisitionWrite, self::RequisitionUpdate, self::RequisitionDelete, self::RequisitionApproval, self::RequisitionItemsRead, self::RequisitionItemsWrite, self::RequisitionItemsUpdate, self::RequisitionItemsDelete, self::RequisitionItemsApproval => 'Requisitions',


            //PurchaseOrder
            self::PurchaseOrderRead, self::PurchaseOrderWrite, self::PurchaseOrderUpdate, self::PurchaseOrderDelete, self::PurchaseOrderApproval => 'Purchase Order',

            //Tendering
            self::TenderRead, self::TenderWrite, self::TenderUpdate, self::TenderDelete, self::TenderApproval => 'Tenders',
            self::SupplierRead, self::SupplierWrite, self::SupplierUpdate, self::SupplierDelete, self::SupplierApprove => 'Suppliers',
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

            self::StockAdjustmentView, self::StockAdjustmentUpdate, self::StockAdjustmentCreate, self::StockAdjustmentDestroy, self::StockAdjustmentApproval => 'Stock Adjustment',
            //self::InventoryHoldView, self::InventoryHoldUpdate, self::InventoryHoldCreate, self::InventoryHoldDestroy => 'Inventory Hold',
            self::InventoryHoldReviewView, self::InventoryHoldReviewUpdate, self::InventoryHoldReviewCreate, self::InventoryHoldReviewDestroy => 'Inventory Hold Review',
            self::UOMConversionView, self::UOMConversionUpdate, self::UOMConversionCreate, self::UOMConversionDestroy => 'UOM Conversion',

            //Fleet Management
            self::FleetModelView, self::FleetModelCreate, self::FleetModelUpdate, self::FleetModelDestroy => 'Fleet Model',
            self::FleetMakeView, self::FleetMakeCreate, self::FleetMakeUpdate, self::FleetMakeDestroy => 'Fleet Make',
            self::FleetVehicleView, self::FleetVehicleCreate, self::FleetVehicleUpdate, self::FleetVehicleDestroy => 'Vehicle Management',
            self::FleetInsuranceTrackerView, self::FleetInsuranceTrackerCreate, self::FleetInsuranceTrackerUpdate, self::FleetInsuranceTrackerDestroy => 'Fleet Insurance Tracker',
            self::FleetInspectionScheduleView, self::FleetInspectionScheduleCreate, self::FleetInspectionScheduleUpdate, self::FleetInspectionScheduleDestroy => 'Vehicle Inspection',
            self::FleetDriverView, self::FleetDriverCreate, self::FleetDriverUpdate, self::FleetDriverDestroy => 'Fleet Driver',
            self::FleetTripLogView, self::FleetTripLogCreate, self::FleetTripLogUpdate, self::FleetTripLogDestroy => 'Fleet Trip Log',
            self::FleetRoutePlanView, self::FleetRoutePlanCreate, self::FleetRoutePlanUpdate, self::FleetRoutePlanDestroy => 'Trip Route Plan',
            self::FleetVehicleRequestView, self::FleetVehicleRequestApproval, self::FleetVehicleRequestCreate, self::FleetVehicleRequestUpdate, self::FleetVehicleRequestDestroy => 'Fleet Vehicle Request',
            self::FleetMaintenanceScheduleView, self::FleetMaintenanceScheduleCreate, self::FleetMaintenanceScheduleUpdate, self::FleetMaintenanceScheduleDestroy, self::FleetMaintenanceScheduleCancel => 'Fleet Maintenance Schedule',
            self::ContractedDriverView, self::ContractedDriverCreate, self::ContractedDriverUpdate, self::ContractedDriverDestroy => 'Contracted Driver',
            self::FleetRepairLogView, self::FleetRepairLogCreate, self::FleetRepairLogUpdate, self::FleetRepairLogDestroy => 'Fleet Repair Log',
            self::VehicleInspectionView, self::VehicleInspectionCreate, self::VehicleInspectionUpdate, self::VehicleInspectionDestroy => 'Vehicle Inspection ( Pre & Post Trip)',
            self::FleetServiceAlertView, self::FleetServiceAlertAcknowledge => 'Fleet Service Alerts',

            ////////////////////////// Budget and Analytics //////////////////////////////
            self::BudgetSetupView, self::BudgetSetupCreate, self::BudgetSetupUpdate, self::BudgetSetupDelete => 'Budget Setup',
            self::BudgetActivityView, self::BudgetActivityCreate, self::BudgetActivityUpdate, self::BudgetActivityDelete => 'Budget Activity',
            self::NewBudgetView, self::NewBudgetCreate, self::NewBudgetUpdate, self::NewBudgetDelete => 'New Budget',
            self::BudgetProjectionView, self::BudgetProjectionCreate, self::BudgetProjectionUpdate, self::BudgetProjectionDelete => 'Budget Projection',
            self::BudgetEntryByLineView, self::BudgetEntryByLineCreate, self::BudgetEntryByLineUpdate, self::BudgetEntryByLineDelete => 'Budget Entry By Line',
            self::BudgetReallocationView, self::BudgetReallocationCreate, self::BudgetReallocationUpdate, self::BudgetReallocationDelete => 'Budget Reallocation',
            self::BudgetConsolidationView => 'Budget Consolidation',
            self::ApproveNewBudget => 'Approve New Budget',
            self::ApproveReallocation => 'Approve Reallocation',

            //Property Management
            self::PropertyCategoryView, self::PropertyCategoryCreate, self::PropertyCategoryUpdate, self::PropertyCategoryDelete => 'Property Category',
            self::PropertyTypeView, self::PropertyTypeCreate, self::PropertyTypeUpdate, self::PropertyTypeDelete => 'Property Type',
            self::PropertyRegistryView, self::PropertyRegistryCreate, self::PropertyRegistryUpdate, self::PropertyRegistryDelete => 'Property Registry',
            self::PropertyStructuralView, self::PropertyStructuralCreate, self::PropertyStructuralUpdate, self::PropertyStructuralDelete => 'Property Structural Mapping',
            self::PropertyAttachmentsView, self::PropertyAttachmentsCreate, self::PropertyAttachmentsUpdate, self::PropertyAttachmentsDelete => 'Property Attachments ',
            self::TenantMaintenanceCreate, self::TenantMaintenanceUpdate, self::TenantMaintenanceDelete, self::TenantMaintenanceView => 'Tenant Maintenance',
            self::TenantClearanceCreate, self::TenantClearanceUpdate, self::TenantClearanceDelete, self::TenantClearanceView => 'Tenant Clearance',
            self::PropertyNewLeaseCreate, self::PropertyNewLeaseUpdate, self::PropertyNewLeaseDelete, self::PropertyNewLeaseView => 'Property New Lease',
            self::PropertyLeaseTerminationCreate, self::PropertyLeaseTerminationUpdate, self::PropertyLeaseTerminationDelete, self::PropertyLeaseTerminationView => 'Lease Termination',
            self::PropertyLeaseScheduleCreate, self::PropertyLeaseScheduleUpdate, self::PropertyLeaseScheduleDelete, self::PropertyLeaseScheduleView, self::PropertyLeaseSchedulePrint => 'Property Lease Schedule',
            self::PropertyLeaseRenewalCreate, self::PropertyLeaseRenewalUpdate, self::PropertyLeaseRenewalDelete, self::PropertyLeaseRenewalView => 'Property Lease Renewal',
            self::PropertyInvoiceCreate, self::PropertyInvoiceUpdate, self::PropertyInvoiceDelete, self::PropertyInvoiceView => 'Property Invoice',
            self::PropertyReceiptCreate, self::PropertyReceiptUpdate, self::PropertyReceiptDelete, self::PropertyReceiptView, self::PropertyReceiptPrint => 'Property Receipt',
            self::PropertyMaintenanceRequestCreate, self::PropertyMaintenanceRequestUpdate, self::PropertyMaintenanceRequestDelete, self::PropertyMaintenanceRequestView => 'Property Maintenance Request',
            self::PropertyMaintenanceAssignCreate, self::PropertyMaintenanceAssignUpdate, self::PropertyMaintenanceAssignDelete, self::PropertyMaintenanceAssignView => 'Property Maintenance Assign',
            self::PropertyMaintenanceWorkCompletionCreate, self::PropertyMaintenanceWorkCompletionUpdate, self::PropertyMaintenanceWorkCompletionDelete, self::PropertyMaintenanceWorkCompletionView => 'Property Maintenance Work Completion',
            self::PropertyRateAndPricingCreate, self::PropertyRateAndPricingUpdate, self::PropertyRateAndPricingDelete, self::PropertyRateAndPricingView => 'Property Rate And Pricing',

            //dms
            self::DMSView, self::DMSBulkUpload, self::DMSLegalHoldCreate, self::DMSLegalHoldView, self::DMSLegalHoldRelease => 'Document Management System',

            //Insurance
            self::BancassuranceReferralCreate, self::BancassuranceReferralView, self::BancassuranceReferralUpdate, self::BancassuranceReferralDelete => 'Referral',
            self::BancassurancePolicyCreate, self::BancassurancePolicyView, self::BancassurancePolicyUpdate, self::BancassurancePolicyDelete => 'Insurance Policy',
            self::BancassuranceCustomersCreate, self::BancassuranceCustomersView, self::BancassuranceCustomersUpdate, self::BancassuranceCustomersDelete => 'Insurance Customers',
            self::BancassuranceCustomersContactsCreate, self::BancassuranceCustomersContactsView, self::BancassuranceCustomersContactsUpdate, self::BancassuranceCustomersContactsDelete => 'Insurance Customers Contacts',
            self::BancassuranceCustomersBeneficiariesCreate, self::BancassuranceCustomersBeneficiariesView, self::BancassuranceCustomersBeneficiariesUpdate, self::BancassuranceCustomersBeneficiariesDelete => 'Insurance Customers Beneficiaries',
            self::BancassurancePremiumPaymentsView, self::BancassurancePremiumPaymentsCreate, self::BancassurancePremiumPaymentsUpdate, self::BancassurancePremiumPaymentsDelete => 'Insurance Premium Payments',
            self::BancassuranceUnderwritingView, self::BancassuranceUnderwritingCreate, self::BancassuranceUnderwritingUpdate, self::BancassuranceUnderwritingDelete => 'Insurance Under Writting',
            self::BancassuranceClaimView, self::BancassuranceClaimUpdate, self::BancassuranceClaimCreate, self::BancassuranceClaimDelete => 'Insurance Claim',
            self::BancassurancePaymentView, self::BancassurancePaymentCreate, self::BancassurancePaymentDelete, self::BancassurancePaymentUpdate => 'Insurance Payment',
            self::MedicalFundView, self::MedicalFundCreate, self::MedicalFundUpdate, self::MedicalFundDelete => 'Medical Fund',
            self::MedicalFundContributionView, self::MedicalFundContributionCreate, self::MedicalFundContributionUpdate, self::MedicalFundContributionDelete => 'Medical Fund Contribution',
            self::MedicalFundBeneficiaryView, self::MedicalFundBeneficiaryCreate, self::MedicalFundBeneficiaryUpdate, self::MedicalFundBeneficiaryDelete => 'Medical Fund Beneficiary',
            self::InsuranceProviderView, self::InsuranceProviderCreate, self::InsuranceProviderUpdate, self::InsuranceProviderDelete => 'Insurance Provider',
            self::InsuranceProductView, self::InsuranceProductCreate, self::InsuranceProductUpdate, self::InsuranceProductDelete => 'Insurance Product',
            self::InsuranceProductRiderView, self::InsuranceProductRiderCreate, self::InsuranceProductRiderUpdate, self::InsuranceProductRiderDelete => 'Insurance Product Rider',
            self::InsurancePricingRuleView, self::InsurancePricingRuleCreate, self::InsurancePricingRuleUpdate, self::InsurancePricingRuleDelete => ' Insurance Pricing Rule',
            self::CommissionRuleView, self::CommissionRuleCreate, self::CommissionRuleUpdate, self::CommissionRuleDelete => 'Commission Rules',
            self::InsuranceClaimClosureView, self::InsuranceClaimClosureCreate, self::InsuranceClaimClosureUpdate, self::InsuranceClaimClosureDelete => 'Insurance Claim Closure',

            ///////////////////////  Finance   /////////////////////////
            self::FinanceCOAView, self::FinanceCOACreate, self::FinanceCOAUpdate, self::FinanceCOADelete => 'Chart of Accounts',
            self::FinanceGeneralLedgerView, self::FinanceGeneralLedgerCreate, self::FinanceGeneralLedgerUpdate, self::FinanceGeneralLedgerDelete => 'General Ledger',
            self::FinanceGLMappingView, self::FinanceGLMappingCreate, self::FinanceGLMappingUpdate, self::FinanceGLMappingDelete => 'GL Mapping',
            self::FinanceAccountsPayableView, self::FinanceAccountsPayableCreate, self::FinanceAccountsPayableUpdate, self::FinanceAccountsPayableDelete => 'Accounts Payable',
            self::FinanceAccountsReceivableView, self::FinanceAccountsReceivableCreate, self::FinanceAccountsReceivableUpdate, self::FinanceAccountsReceivableDelete => 'Accounts Receivable',
            self::CreditNoteView, self::CreditNoteCreate, self::CreditNoteUpdate, self::CreditNoteDelete => 'Credit Note',
            self::PaymentVoucherView, self::PaymentVoucherCreate, self::PaymentVoucherUpdate, self::PaymentVoucherDelete => 'Payment Voucher',
            self::PaymentProcessingView, self::PaymentProcessingCreate, self::PaymentProcessingUpdate, self::PaymentProcessingDelete => 'Payment Processing',
            self::ReceiptPostingView, self::ReceiptPostingCreate, self::ReceiptPostingUpdate, self::ReceiptPostingDelete => 'Receipt Posting',
            self::DebitNoteView, self::DebitNoteCreate, self::DebitNoteUpdate, self::DebitNoteDelete => 'Debit Note',
            self::FinanceTaxSettingView, self::FinanceTaxSettingCreate, self::FinanceTaxSettingUpdate, self::FinanceTaxSettingDelete => 'Tax Setting',
            self::FinanceCreditManagementView, self::FinanceCreditManagementCreate, self::FinanceCreditManagementUpdate, self::FinanceCreditManagementDelete => 'Credit Management',
            self::FinanceJournalPosting, self::FinanceAPInvoicePosting, self::FinanceARInvoicePosting, self::FinanceCreditNotePosting, self::FinanceDebitNotePosting, self::FinanceVoucherPosting, self::FinancePaymentProcessingPosting, self::FinanceReceiptPosting => 'Transaction Postings',

            ////////////////////////  Legal   ///////////////////////////
            self::ContractView, self::ContractCreate, self::ContractUpdate, self::ContractDelete => 'Legal Contract',
            self::DisputeLitigationView, self::DisputeLitigationCreate, self::DisputeLitigationUpdate, self::DisputeLitigationDelete => 'Dispute Litigation',
            self::LegalObligationView, self::LegalObligationCreate, self::LegalObligationUpdate, self::LegalObligationDelete => 'Legal Obligation',
            self::LegalSearchView, self::LegalSearchCreate, self::LegalSearchUpdate, self::LegalSearchDelete => 'Legal Search',
            self::LoanSecurityView, self::LoanSecurityCreate, self::LoanSecurityUpdate, self::LoanSecurityDelete => 'Loan Security',
            self::IntellectualPropertyView, self::IntellectualPropertyCreate, self::IntellectualPropertyUpdate, self::IntellectualPropertyDelete => 'Intellectual Propert',

            default      => 'Unknown Permission'
        };
    }
}
