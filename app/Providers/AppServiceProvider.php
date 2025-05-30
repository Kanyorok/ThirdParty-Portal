<?php

namespace App\Providers;

use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\BR\Account;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\Communication\Call;
use App\Models\Communication\Comment;
use App\Models\Communication\Email;
use App\Models\Core\Branch;
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
use App\Policies\RolePolicy;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
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
            SchedulePlan::getPrimaryKey() => SchedulePlan::class,

        ]);

        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Branch::class, CrmBranchPolicy::class);
        Gate::policy(Requisitions::class, RequisitionPolicy::class);
        Gate::policy(RequisitionLine::class, RequisitionLinesPolicy::class);
        Gate::policy(ProductDevelopment::class, ProductDevelopmentPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(DepartmentNeeds::class, DepartmentNeedsPolicy::class);
        Gate::policy(ProcurementMethod::class, ProcurementMethodPolicy::class);
        Gate::policy(SchedulePlan::class, SchedulePlanPolicy::class);

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
