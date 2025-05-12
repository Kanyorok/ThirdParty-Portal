<?php

namespace App\Listeners\Marketing;

use App\Enums\Core\PermissionEnum;
use App\Enums\Marketing\PlannerStatus;
use App\Enums\Marketing\PlannerTypeEnum;
use App\Enums\WorkflowStatus;
use App\Events\Marketing\PlannerSubmitEvent;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\MarketingPlanner;
use App\Models\PendingWorkflow;
use App\Models\User;
use App\Services\HRM\UserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlannerSubmittedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PlannerSubmitEvent $event): void
    {
        if ($event->planner->Type->value === PlannerTypeEnum::MasterPlanner->value) {
            $Workflows = collect();
            $date = now();

            foreach ($event->planner->plans as $plan) {
                $Workflows->add([
                                 'Source'     => MarketingPlanner::getPrimaryKey(),
                                 'SourceID'   => $plan->Id,
                                 'Stage'      => PlannerStatus::MarketingManager->name,
                                 'Status'     => WorkflowStatus::Accepted->value,
                                 'Notes'      => 'Marketing Manager Approval',
                                 'CreatedBy'  => $event->actor->Id,
                                 'ModifiedBy' => $event->actor->Id,
                                 'CreatedOn'  => $date,
                                 'ModifiedOn' => $date,
                                ]);
            }
            if ($Workflows->count() === 0) {
                throw new ErroredException('marketing plan has no plans');
            }
            //add workflow to all.
            DB::table('t_Workflows')->insert($Workflows->toArray());

            $event->planner->workflows()->create([
                                                  'Stage'      => PlannerStatus::MarketingManager->name,
                                                  'Status'     => WorkflowStatus::Accepted->value,
                                                  'Notes'      => 'Submitted Master Plan',
                                                  'CreatedBy'  => $event->actor->Id,
                                                  'ModifiedBy' => $event->actor->Id,
                                                 ]);
            $users = User::query()->lock('WITH(NOLOCK)')->hasPermission(PermissionEnum::Ceo->value)->get(["Id", "UserID", "Name", "Email"]);
            foreach ($users as $user) {
                if (!$user instanceof User) {
                    continue;
                }
                if (in_array($user->UserID, [$event->actor->UserID, SystemHelper::ID], true)) {//skip sys and submitter
                    continue;
                }

                $event->planner->pendingWorkflows()->create([
                                                             'Stage'      => PlannerStatus::Ceo->name,
                                                             'UserId'     => $user->Id,
                                                             'CreatedBy'  => $event->actor->Id,
                                                             'ModifiedBy' => $event->actor->Id,
                                                            ]);

                //$event->_sendMail($user);
                (new UserService($user))->sendEmail(
                    subject: 'Marketing Plan submitted for review and approval',
                    body: '<p>Hello</p><p>The plan <b>' . Str::upper($event->planner->PlannerID) . '</b> has been submitted for your review. Click the link below to review</p>
                    <p><a href="' . route('marketing-planner.show', [$event->planner->PlannerID]) . '"> planner details</a></p>
                    <p>Kindly review and approve the survey at your earliest convenience.</p>'
                );
            }

            activity()->causedBy($event->actor)->performedOn($event->planner)->event('submit')->log('Submitted master marketing plan ' . $event->planner->PlannerID);

            return;
        }

        if ($event->planner->Type->value === PlannerTypeEnum::BranchPlanner->value) {
            $pending = $event->planner->pendingWorkflows()->where('Stage', PlannerStatus::MarketingManager->name)->where('UserId', $event->actor->Id)->first();
            if ($pending instanceof PendingWorkflow) {
                $pending->forceFill([
                                     'DeletedOn' => now(),
                                     'DeletedBy' => $event->actor->Id,
                                    ])->save();
            }

            $event->planner->workflows()->create([
                                                  'Stage'      => PlannerStatus::MarketingManager->name,
                                                  'Status'     => WorkflowStatus::Accepted->value,
                                                  'Notes'      => 'Marketing Manager Approval',
                                                  'CreatedBy'  => $event->actor->Id,
                                                  'ModifiedBy' => $event->actor->Id,
                                                 ]);

            /*i $owner = $event->planner->owner;

         f ($owner instanceof User) {
              (new UserService($owner))->sendEmail('Update on Marketing Plan Submission',
                  '<p>Hello</p><p>The marketing plan <b>' . $event->planner->PlannerID . '</b>  has been approved at this time. Click the link below to review</p>
                  <p><a href="' . route('marketing-planner.show', [$event->planner->PlannerID]) . '">' . $event->planner->PlannerID . ' details</a></p>
                  <p>This plan is now active.</p>');
          }*/

            activity()->causedBy($event->actor)->performedOn($event->planner)->event('approve')->log('Approved marketing plan ' . $event->planner->PlannerID);

            return;
        }
    }
}
