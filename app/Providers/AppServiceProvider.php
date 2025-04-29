<?php

namespace App\Providers;

use App\Models\APICredential;
use App\Models\Board;
use App\Models\BR\Account;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\Call;
use App\Models\Campaign;
use App\Models\CampaignParty;
use App\Models\Comment;
use App\Models\Competitor;
use App\Models\Contact;
use App\Models\CrmBranch;
use App\Models\CrmEmail;
use App\Models\Discussion;
use App\Models\Lead;
use App\Models\MarketingPlanner;
use App\Models\Meeting;
use App\Models\Notes;
use App\Models\Procurement\RequisitionLines;
use App\Models\Procurement\Requisitions;
use App\Models\ProductDevelopment;
use App\Models\Review;
use App\Models\Schedule;
use App\Models\Social;
use App\Models\Survey;
use App\Models\Task;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use App\Policies\CrmBranchPolicy;
use App\Policies\Procurement\RequisitionLinesPolicy;
use App\Policies\Procurement\RequisitionPolicy;
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
            CrmBranch::getPrimaryKey() => CrmBranch::class,
            CrmEmail::getPrimaryKey() => CrmEmail::class,
            Comment::getPrimaryKey() => Comment::class,
            Competitor::getPrimaryKey() => Competitor::class,
            Contact::getPrimaryKey() => Contact::class,
            DebtProduct::getPrimaryKey() => DebtProduct::class,
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
        ]);

        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(CrmBranch::class, CrmBranchPolicy::class);
        Gate::policy(Requisitions::class, RequisitionPolicy::class);
        Gate::policy(RequisitionLines::class, RequisitionLinesPolicy::class);
        Gate::policy(ProductDevelopment::class, ProductDevelopmentPolicy::class);

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
