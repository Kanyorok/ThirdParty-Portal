<?php

namespace App\Providers;

use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\BR\Account;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\Budget\BudgetDriver;
use App\Models\Budget\BudgetDriverMaster;
use App\Models\Budget\BudgetGLAccount;
use App\Models\Budget\BudgetLine;
use App\Models\Budget\BudgetLinesGLAccount;
use App\Models\Budget\BudgetProduct;
use App\Models\Budget\BudgetProductType;
use App\Models\Communication\Call;
use App\Models\Communication\Comment;
use App\Models\Communication\Email;
use App\Models\Core\Branch;
use App\Models\Core\CategoryMaster;
use App\Models\Core\Report;
use App\Models\Core\Task;
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
use App\Models\HRM\Department;
use App\Models\HRM\Employee;
use App\Models\Procurement\DepartmentNeeds;
use App\Models\Procurement\Order;
use App\Models\Procurement\RequisitionLine;
use App\Models\Procurement\ProcurementMethod;
use App\Models\Procurement\RequisitionLines;
use App\Models\Procurement\Requisitions;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQLine;
use App\Models\Procurement\SchedulePlan;
use App\Models\PropertyManagement\PropertyCategory;
use App\Models\PropertyManagement\PropertyType;
use App\Models\Settings\APICredential;
use App\Models\ThirdParies\Board;
use App\Models\ThirdParies\Competitor;
use App\Policies\CrmBranchPolicy;
use App\Policies\Procurement\DepartmentNeedsPolicy;
use App\Policies\Procurement\OrderPolicy;
use App\Policies\Procurement\ProcurementMethodPolicy;
use App\Policies\Procurement\RequisitionLinesPolicy;
use App\Policies\Procurement\RequisitionPolicy;
use App\Policies\Procurement\SchedulePlanPolicy;
use App\Policies\ProductDevelopmentPolicy;
use App\Policies\PropertyManagement\PropertyCategoryPolicy;
use App\Policies\PropertyManagement\PropertyTypePolicy;
use App\Policies\RolePolicy;
use App\Policies\Procurement\ProcurementPlanMaintainPolicy;
use App\Policies\Procurement\PlanManualInputPolicy;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;
use App\Policies\Inventory\ItemMasterListPolicy;
use App\Policies\Inventory\ItemCategoryPolicy;
use App\Policies\Inventory\ItemTypePolicy;
use App\Policies\Inventory\StockItemPolicy;
use App\Policies\Inventory\InventoryTypePolicy;
use App\Policies\Inventory\StorePolicy;
use App\Policies\Inventory\UnitOfMeasurePolicy;
use App\Policies\Inventory\InterBranchRequisitionPolicy;
use App\Policies\Inventory\PriceManagementPolicy;
use App\Models\Inventory\ItemType;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\Store;
use App\Models\Inventory\InventoryType;
use App\Models\Inventory\PriceManagement;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItems;

class AppServiceProvider extends ServiceProvider
{
    
    public function register(): void
    {
        //
    }

   
    public function boot(): void
    {
        Relation::morphMap([
            Account::getPrimaryKey() => Account::class,
            APICredential::getPrimaryKey() => APICredential::class,
            Board::getPrimaryKey() => Board::class,
            Call::getPrimaryKey() => Call::class,
            Campaign::getPrimaryKey() => Campaign::class,
            CampaignParty::getPrimaryKey() => CampaignParty::class,
            Client::getPrimaryKey() => Client::class,
            Branch::getPrimaryKey() => Branch::class,
            Email::getPrimaryKey() => Email::class,
            Comment::getPrimaryKey() => Comment::class,
            Competitor::getPrimaryKey() => Competitor::class,
            Contact::getPrimaryKey() => Contact::class,
            DebtProduct::getPrimaryKey() => DebtProduct::class,
            Department::getPrimaryKey() => Department::class,
            Discussion::getPrimaryKey() => Discussion::class,
            Employee::getPrimaryKey() => Employee::class,
            Lead::getPrimaryKey() => Lead::class,
            MarketingPlanner::getPrimaryKey() => MarketingPlanner::class,
            Meeting::getPrimaryKey() => Meeting::class,
            Notes::getPrimaryKey() => Notes::class,
            ProductDevelopment::getPrimaryKey() => ProductDevelopment::class,
            Report::getPrimaryKey() => Report::class,
            RFQ::getPrimaryKey() => RFQ::class,
            RFQLine::getPrimaryKey() => RFQLine::class,
            Review::getPrimaryKey() => Review::class,
            Schedule::getPrimaryKey() => Schedule::class,
            Social::getPrimaryKey() => Social::class,
            Survey::getPrimaryKey() => Survey::class,
            Task::getPrimaryKey() => Task::class,
            Team::getPrimaryKey() => Team::class,
            Ticket::getPrimaryKey() => Ticket::class,
            User::getPrimaryKey() => User::class,
            Requisitions::getPrimaryKey() => Requisitions::class,
            RequisitionLine::getPrimaryKey() => RequisitionLine::class,
            Order::getPrimaryKey() => Order::class,
            DepartmentNeeds::getPrimaryKey() => DepartmentNeeds::class,
            ProcurementMethod::getPrimaryKey() => ProcurementMethod::class,
            ItemMasterList::getPrimaryKey() => ItemMasterList::class,
            ItemCategories::getPrimaryKey() => ItemCategories::class,
            ItemType::getPrimaryKey() => ItemType::class,
            InventoryType::getPrimaryKey() => InventoryType::class,
            StockItem::getPrimaryKey() => StockItem::class,
            Store::getPrimaryKey() => Store::class,
            UnitOfMeasure::getPrimaryKey() => UnitOfMeasure::class,
            PriceManagement::getPrimaryKey() => PriceManagement::class,
            SchedulePlan::getPrimaryKey() => SchedulePlan::class,
            InterBranchRequisition::getPrimaryKey() => InterBranchRequisition::class,
            ConsolidatedProcurementPlan::getPrimaryKey() => ConsolidatedProcurementPlan::class,
            PlanLineItems::getPrimaryKey() => PlanLineItems::class,
          
            ///////// Budget and Analytics /////////
            BudgetLinesGLAccount::getPrimaryKey()=>BudgetLinesGLAccount::class,
            BudgetGLAccount::getPrimaryKey()=>BudgetGLAccount::class,
            BudgetLine::getPrimaryKey()=>BudgetLine::class,
            BudgetProduct::getPrimaryKey()=>BudgetProduct::class,
            BudgetProductType::getPrimaryKey()=>BudgetProductType::class,
            BudgetDriver::getPrimaryKey()=>BudgetDriver::class,
            BudgetDriverMaster::getPrimaryKey()=>BudgetDriverMaster::class,

          
            CategoryMaster::getPrimaryKey() => CategoryMaster::class,
            PropertyType::getPrimaryKey() => PropertyType::class,
        ]);

        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Branch::class, CrmBranchPolicy::class);
        Gate::policy(Requisitions::class, RequisitionPolicy::class);
        Gate::policy(RequisitionLine::class, RequisitionLinesPolicy::class);
        Gate::policy(ProductDevelopment::class, ProductDevelopmentPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(DepartmentNeeds::class, DepartmentNeedsPolicy::class);
        Gate::policy(ProcurementMethod::class, ProcurementMethodPolicy::class);
        Gate::policy(ItemMasterList::class, ItemMasterListPolicy::class);
        Gate::policy(ItemCategories::class, ItemCategoryPolicy::class);
        Gate::policy(ItemType::class, ItemTypePolicy::class);
        Gate::policy(StockItem::class, StockItemPolicy::class);
        Gate::policy(InventoryType::class, InventoryTypePolicy::class);
        Gate::policy(Store::class, StorePolicy::class);
        Gate::policy(UnitOfMeasure::class, UnitOfMeasurePolicy::class);
        Gate::policy(PriceManagement::class, PriceManagementPolicy::class);
        Gate::policy(ConsolidatedProcurementPlan::class, ProcurementPlanMaintainPolicy::class);
        Gate::policy(SchedulePlan::class, SchedulePlanPolicy::class);
        Gate::policy(PlanLineItems::class, PlanManualInputPolicy::class);
        Gate::policy(CategoryMaster::class, PropertyCategoryPolicy::class);
        Gate::policy(PropertyType::class, PropertyTypePolicy::class);


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
    }
}
