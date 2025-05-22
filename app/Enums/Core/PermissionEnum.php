<?php

namespace App\Enums\Core;

use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\Communication\BulkNotification;
use App\Models\Communication\Call;
use App\Models\Communication\EmailConversation;
use App\Models\Core\Branch;
use App\Models\Core\CodeDetail;
use App\Models\Core\Task;
use App\Models\CRM\Campaign;
use App\Models\CRM\Lead;
use App\Models\CRM\MarketingList;
use App\Models\CRM\MarketingPlanner;
use App\Models\CRM\MeetingRoom;
use App\Models\CRM\Review;
use App\Models\CRM\Schedule;
use App\Models\CRM\Social;
use App\Models\CRM\Survey;
use App\Models\CRM\Ticket;
use App\Models\Procurement\DepartmentNeeds;
use App\Models\HRM\Department;
use App\Models\HRM\Employee;
use App\Models\Procurement\Order;
use App\Models\Procurement\RequisitionLines;
use App\Models\Procurement\Requisitions;
use App\Models\Procurement\RFQ;
use App\Models\Settings\APICredential;
use App\Models\ThirdParies\Board;
use App\Models\ThirdParies\Competitor;
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
    case RfqRead = 'rfqItem-read';
    case RfqWrite = 'rfqItem-create';
    case RfqUpdate = 'rfqItem-update';
    case RfqDelete = 'rfqItem-delete';
    case RfqApproval = 'rfqItem-approval';

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
    /*
    *
    * ========================================  Inventory  ========================================
    */
    case MasterListView = 'masterList-view';

    /*
     *
     * ========================================  Human Resource management  ========================================
     */
    case Departments = 'department';
    case EmployeesView = 'employee-read';
    case EmployeesCreate = 'employee-create';
    case EmployeesUpdate = 'employee-update';
    case EmployeesDelete = 'employee-delete';



    public static function display(): Collection
    {
        return collect([
            [self::TicketRead, self::TicketWrite, self::TicketUpdate, self::TicketDelete, self::TicketApproval,],
            [self::TaskCreate, self::TaskDelegate,],
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

            [self::EmployeesView, self::EmployeesCreate, self::EmployeesUpdate, self::EmployeesDelete, self::Departments],
            [self::RequisitionRead, self::RequisitionWrite, self::RequisitionUpdate, self::RequisitionDelete, self::RequisitionApproval, self::RequisitionItemsRead, self::RequisitionItemsWrite, self::RequisitionItemsUpdate, self::RequisitionItemsDelete, self::RequisitionItemsApproval],
            [self::PurchaseOrderRead, self::PurchaseOrderWrite, self::PurchaseOrderUpdate, self::PurchaseOrderDelete, self::PurchaseOrderApproval]


        ]);
    }

    public static function approvals(): Collection
    {
        return collect([self::MarketingPlannerApproval, /* self::MarketingListApproval,*/ self::TicketApproval, self::CampaignApproval, self::SurveyApproval, self::Ceo, self::MarketingManager,
            self::PurchaseOrderApproval, self::RequisitionApproval,self::RequisitionItemsApproval,self::DepartmentNeedsApproval]);
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

            //Requisition
            self::RequisitionRead, self::RequisitionWrite, self::RequisitionUpdate, self::RequisitionDelete, self::RequisitionApproval,
            self::RequisitionItemsRead, self::RequisitionItemsWrite, self::RequisitionItemsUpdate, self::RequisitionItemsDelete, self::RequisitionItemsApproval,
            self::PurchaseOrderRead, self::PurchaseOrderWrite, self::PurchaseOrderUpdate, self::PurchaseOrderDelete, self::PurchaseOrderApproval,
            self::RfqRead, self::RfqWrite, self::RfqUpdate, self::RfqApproval, self::RfqDelete,
             self::DepartmentNeedsRead, self::DepartmentNeedsWrite, self::DepartmentNeedsUpdate, self::DepartmentNeedsDelete, self::DepartmentNeedsApproval,
            => ModulesEnum::Procurement,
        
            self::Departments, self::EmployeesView, self::EmployeesCreate, self::EmployeesUpdate, self::EmployeesDelete => ModulesEnum::HRM,

            self::MasterListView => ModulesEnum::Inventory,
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

            //Requisition
            self::RequisitionRead, self::RequisitionWrite, self::RequisitionUpdate, self::RequisitionDelete, self::RequisitionApproval, self::RequisitionItemsRead, self::RequisitionItemsWrite, self::RequisitionItemsUpdate, self::RequisitionItemsDelete, self::RequisitionItemsApproval =>'Requisitions',


            //PurchaseOrder
            self::PurchaseOrderRead, self::PurchaseOrderWrite, self::PurchaseOrderUpdate, self::PurchaseOrderDelete, self::PurchaseOrderApproval => 'Purchase Order',
        };
    }
}
