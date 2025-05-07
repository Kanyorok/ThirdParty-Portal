<?php

namespace App\Enums\Core;

use App\Models\APICredential;
use App\Models\Board;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\BulkNotification;
use App\Models\Call;
use App\Models\Campaign;
use App\Models\CodeDetail;
use App\Models\Competitor;
use App\Models\CrmBranch;
use App\Models\EmailConversation;
use App\Models\Lead;
use App\Models\MarketingList;
use App\Models\MarketingPlanner;
use App\Models\MeetingRoom;
use App\Models\Procurement\Order;
use App\Models\Procurement\RequisitionLines;
use App\Models\Procurement\Requisitions;
use App\Models\Procurement\RFQ;
use App\Models\Review;
use App\Models\Schedule;
use App\Models\Social;
use App\Models\Survey;
use App\Models\Task;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use App\Traits\UsefulEnumTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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

    //Procurement
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
    case RfWrite = 'rfqItem-create';
    case RfqUpdate = 'rfqItem-update';
    case RfqDelete = 'rfqItem-delete';
    case RfqApproval = 'rfqItem-approval';

    //RequisitionItems
    case PurchaseOrderRead = 'purchaseOrder-read';
    case PurchaseOrderWrite = 'purchaseOrder-create';
    case PurchaseOrderUpdate = 'purchaseOrder-update';
    case PurchaseOrderDelete = 'purchaseOrder-delete';
    case PurchaseOrderApproval = 'purchaseOrder-approval';


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

        ]);
    }

    public static function approvals(): Collection
    {
        return collect([self::MarketingPlannerApproval, /* self::MarketingListApproval,*/ self::TicketApproval, self::CampaignApproval, self::SurveyApproval, self::Ceo, self::MarketingManager]);
    }

    public function module(): string
    {
        return match ($this) {
            self::MarketingPlannerRead, self::MarketingPlannerWrite, self::MarketingPlannerUpdate, self::MarketingPlannerDelete, self::MarketingPlannerApproval => MarketingPlanner::getPrimaryKey(),
            self::MarketingListRead, self::MarketingListWrite, self::MarketingListUpdate, self::MarketingListDelete, self::DebtCollectionLists/*, self::MarketingListApproval*/ => MarketingList::getPrimaryKey(),
            self::ProductDevelopmentRead, self::ProductDevelopmentWrite, self::ProductDevelopmentUpdate, self::ProductDevelopmentDelete => '',
            self::EmailRead, self::EmailAssign, self::EmailDelete => EmailConversation::getPrimaryKey(),
            self::ScheduleRead, self::ScheduleWrite, self::ScheduleDelete => Schedule::getPrimaryKey(),
            self::CallRead, self::CallWrite, self::CallUpdate, self::CallDelete => Call::getPrimaryKey(),
            self::CampaignRead, self::CampaignWrite, self::CampaignUpdate, self::CampaignDelete, self::CampaignApproval => Campaign::getPrimaryKey(),
            self::TicketRead, self::TicketWrite, self::TicketUpdate, self::TicketDelete, self::TicketApproval => Ticket::getPrimaryKey(),
            self::TaskCreate, self::TaskDelegate => Task::getPrimaryKey(),
            self::LeadRead, self::LeadWrite, self::LeadDelegate, self::LeadUpdate, self::LeadDelete, self::LeadViewAll, self::LeadsManager => Lead::getPrimaryKey(),
            self::SocialRead, self::SocialWrite, self::SocialDelete => Social::getPrimaryKey(),
            self::ReviewsView => Review::getPrimaryKey(),
            self::DebtCollectionView, self::DebtCollectionAssignment, self::DebtCollectionAdmin => DebtProduct::getPrimaryKey(),
            self::DebtNotificationView, self::DebtNotificationSend => BulkNotification::getPrimaryKey(),
            self::SurveyRead, self::SurveyWrite, self::SurveyDelete, self::SurveyApproval => Survey::getPrimaryKey(),
            self::Competitor, self::CompetitorLLM => Competitor::getPrimaryKey(),
            self::Teams => Team::getPrimaryKey(),
            self::Branches => CrmBranch::getPrimaryKey(),
            self::Users, self::UsersMeeting, self::UsersMessaging, self::UsersSessions => User::getPrimaryKey(),
            self::Members => Client::getPrimaryKey(),
            self::BoardManage, self::BoardMeeting => Board::getPrimaryKey(),
            self::Integrations => APICredential::getPrimaryKey(),
            self::MeetingRooms => MeetingRoom::getPrimaryKey(),
            self::Roles, self::Ceo, self::Managers, self::MarketingManager => Role::class,
            self::ListsView, self::ListsUpdate => CodeDetail::getPrimaryKey(),
            //Requisition
            self::RequisitionRead, self::RequisitionWrite, self::RequisitionUpdate, self::RequisitionDelete, self::RequisitionApproval => Requisitions::getPrimaryKey(),
            self::RequisitionItemsRead, self::RequisitionItemsWrite, self::RequisitionItemsUpdate, self::RequisitionItemsDelete, self::RequisitionItemsApproval => RequisitionLines::getPrimaryKey(),

            //PurchaseOrder
            self::PurchaseOrderRead, self::PurchaseOrderWrite, self::PurchaseOrderUpdate, self::PurchaseOrderDelete, self::PurchaseOrderApproval => Order::getPrimaryKey(),


            self::RfqRead, self::RfqWrite, self::RfqUpdate, self::RfqApproval => RFQ::getPrimaryKey(),

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
        };
    }
}
