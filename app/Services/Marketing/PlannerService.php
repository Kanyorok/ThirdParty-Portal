<?php

namespace App\Services\Marketing;

use App\Enums\Core\PermissionEnum;
use App\Enums\Marketing\PlannerStatus;
use App\Enums\Marketing\PlannerTypeEnum;
use App\Enums\WorkflowStatus;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Branch;
use App\Models\CRM\Approval\PendingWorkflow;
use App\Models\CRM\Approval\Workflow;
use App\Models\CRM\MarketingPlanner;
use App\Models\CRM\MarketingPlannerActivity;
use App\Services\HRM\UserService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlannerService
{
    public function __construct(public MarketingPlanner $planner)
    {
    }

    public static function createMaster(string $Name, Collection $plansIds, string $Notes, User $actor): PlannerService
    {
        $planner = new MarketingPlanner();
        $planner->fill([
            'PlannerID' => self::_ID(),
            'Name' => $Name,
            'Notes' => $Notes,
            'Status' => PlannerStatus::Draft->value,
            'Type' => PlannerTypeEnum::MasterPlanner->value,
            'OwnerId' => $actor->Id,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        MarketingPlanner::query()->whereIn('Id', $plansIds->toArray())->update([
            'MasterPlannerId' => $planner->Id,
            'Status' => PlannerStatus::Merged->value,
        ]);

        MarketingPlannerActivity::query()->whereIn('PlannerId', $plansIds->toArray())->update([
            'MasterPlannerId' => $planner->Id,
        ]);

        return (new self($planner->refresh()));
    }

    /**
     * @throws ErroredException
     */
    public function ceoWorkflowReject(User $actor, string $reason): static
    {
        if (! $this->canApprove($actor) && ! $actor->can(PermissionEnum::Ceo->value)) {
            throw new ErroredException('cannot reject, no permission');
        }

        $this->planner->fill([
            'Status' => ($this->planner->Type->value === PlannerTypeEnum::MasterPlanner->value) ? PlannerStatus::Draft->value : PlannerStatus::MarketingManager->value,
        ])->save(['timestamps' => false]);


        $pending = $this->planner->pendingWorkflows()->where('UserId', $actor->Id)->first();
        if ($pending instanceof PendingWorkflow) {
            $marketingManager = $pending->creator;
        } else {
            $marketingManager = null;
        }
        $this->planner->pendingWorkflows()->update([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id,
        ]);

        $this->planner->workflows()->create([
            'Stage' => PlannerStatus::Ceo->name,
            'Status' => WorkflowStatus::RejectReturn->value,
            'Notes' => $reason,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        if ($marketingManager instanceof User) {
            (new UserService($marketingManager))->sendEmail(
                'Update on Marketing Plan Submission',
                '<p>Hello</p><p>The marketing plan <b>' . $this->planner->PlannerID . '</b> has NOT been approved at this time. Click the link below to review</p>
            <p><a href="' . route('marketing-planner.show', [$this->planner->PlannerID]) . '">' . $this->planner->PlannerID . ' details</a></p>
            <p><b>Reason Given: </b>&nbsp;' . $reason . '</p>'
            );
        }


        activity()->causedBy($actor)->performedOn($this->planner)->event('reject')->log('Reject marketing plan ' . $this->planner->PlannerID);

        return $this;
    }

    public function canApprove(User $actor): bool
    {
        return in_array($actor->Id, $this->planner->pendingWorkflows()->get('t_PendingWorkflows_static.UserId')->pluck('UserId')->toArray(), true) /*|| $actor->can(PermissionEnum::MarketingPlannerApproval->value)*/ ;
    }

    public function update(CodeDetail $Mode, string $Name, string $Notes, User $actor): static
    {
        $this->planner->update([
            'Name' => $Name,
            'Notes' => $Notes,
            'Status' => PlannerStatus::Draft->value,
            'Modes' => $Mode->ID,
            'ModifiedBy' => $actor->Id,
        ]);

        return $this;
    }

    public static function create(Branch $branch, CodeDetail $Mode, string $Name, string $Notes, User $actor): PlannerService
    {
        $planner = new MarketingPlanner();
        $planner->fill([
            'PlannerID' => self::_ID(),
            'Name' => $Name,
            'BranchId' => $branch->Id,
            'Notes' => $Notes,
            'Status' => PlannerStatus::Draft->value,
            'Type' => PlannerTypeEnum::BranchPlanner->value,
            'Modes' => $Mode->ID,
            'OwnerId' => $actor->Id,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        return (new self($planner->refresh()));
    }

    private static function _ID(): string
    {
        $number = MarketingPlanner::query()->withTrashed()->count();
        do {
            $number++;
            $slug = Str::slug('P' . Str::padLeft(($number), 5, '0'));
        } while (MarketingPlanner::where('PlannerID', $slug)->withTrashed()->exists());

        return $slug;
    }

    /**
     * @throws ErroredException
     */
    public function ceoWorkflowApprove(User $actor): static
    {
        if (! $this->canApprove($actor) && ! $actor->can(PermissionEnum::Ceo->value)) {
            throw new ErroredException('cannot reject, no permission');
        }

        $this->planner->fill([
            'Status' => PlannerStatus::Active->value,
        ])->save(['timestamps' => false]);

        $pending = $this->planner->pendingWorkflows()->where('UserId', $actor->Id)->first();
        if ($pending instanceof PendingWorkflow) {
            $marketingManager = $pending->creator;
        } else {
            $marketingManager = null;
        }

        $this->planner->pendingWorkflows()->update([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id,
        ]);

        if ($marketingManager instanceof User) {
            (new UserService($marketingManager))->sendEmail(
                'Marketing Plan has been approval',
                '<p>Hello</p><p>A marketing plan <b>' . $this->planner->PlannerID . '</b> has been prepared and submitted for your review. Click the link below to review</p>
                <p><a href="' . route('marketing-planner.show', [$this->planner->PlannerID]) . '"> planner details</a></p><p>Kindly review and approve the plan at your earliest convenience.</p>'
            );
        }

        activity()->causedBy($actor)->performedOn($this->planner)->event('approve')->log('Approved marketing plan ' . $this->planner->PlannerID);

        return $this;
    }

    /**
     * @throws ErroredException
     */
    public function branchWorkflowReject(User $actor, string $reason): static
    {
        if (! $this->canApprove($actor)) {
            throw new ErroredException('cannot reject, no permission');
        }

        $this->planner->fill([//change status to BM &&
            'Status' => PlannerStatus::Draft->value,
        ])->save(['timestamps' => false]);

        $pending = $this->planner->pendingWorkflows()->where('Stage', PlannerStatus::BranchManager->name)->where('UserId', $actor->Id)->first();
        if ($pending instanceof PendingWorkflow) {
            $pending->forceFill([
                'DeletedOn' => now(),
                'DeletedBy' => $actor->Id,
            ])->save();
        }

        $this->planner->workflows()->create([
            'Stage' => PlannerStatus::BranchManager->name,
            'Status' => WorkflowStatus::RejectReturn->value,
            'Notes' => $reason,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        $owner = $this->planner->owner;
        if ($owner instanceof User) {
            (new UserService($owner))->sendEmail(
                'Update on Marketing Plan Submission',
                '<p>Hello</p><p>The marketing plan <b>' . $this->planner->PlannerID . '</b>  has not been approved at this time. Click the link below to review</p>
                <p><a href="' . route('marketing-planner.show', [$this->planner->PlannerID]) . '">' . $this->planner->PlannerID . ' details</a></p>
                <p><b>Reason Given: </b>&nbsp;' . $reason . '</p>'
            );
        }

        activity()->causedBy($actor)->performedOn($this->planner)->event('reject')->log('Reject marketing plan ' . $this->planner->PlannerID);

        return $this;
    }

    /**
     * @throws ErroredException
     */
    public function managerWorkflowApprove(User $actor): static
    {
        if (! $this->canApprove($actor) && ! (new UserService($actor))->isMarketingManager($actor->branch)) {
            throw new ErroredException('cannot approve, no permission');
        }

        $this->planner->fill([
            'Status' => PlannerStatus::Ceo->value,
        ])->save(['timestamps' => false]);


        if ($this->planner->Type->value === PlannerTypeEnum::MasterPlanner->value) {
            $Workflows = collect();
            $planIDs = collect();
            $date = now();

            foreach ($this->planner->plans as $plan) {
                $Workflows->add([
                    'Source' => MarketingPlanner::getPrimaryKey(),
                    'SourceID' => $plan->Id,
                    'Stage' => PlannerStatus::MarketingManager->name,
                    'Status' => WorkflowStatus::Accepted->value,
                    'Notes' => 'Marketing Manager Approval',
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                    'CreatedOn' => $date,
                    'ModifiedOn' => $date,
                ]);
                $planIDs->add($plan->Id);
            }
            if ($Workflows->count() === 0) {
                throw new ErroredException('marketing plan has no activities');
            }
            //add workflow to all.
            DB::table('t_Workflows_static')->insert($Workflows->toArray());

            PendingWorkflow::query()->where('t_PendingWorkflows_static.Source', MarketingPlanner::getPrimaryKey())->whereIn('t_PendingWorkflows_static.SourceID', $planIDs->toArray())->update([
                'DeletedOn' => now(),
                'DeletedBy' => $actor->Id,
            ]);

            $this->planner->workflows()->create([
                'Stage' => PlannerStatus::MarketingManager->name,
                'Status' => WorkflowStatus::Accepted->value,
                'Notes' => 'Submitted Master Plan',
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
            ]);

            $this->_notifyCeo($actor);

            activity()->causedBy($actor)->performedOn($this->planner)->event('submit')->log('Submitted master marketing plan ' . $this->planner->PlannerID);

            return $this;
        }

        if ($this->planner->Type->value === PlannerTypeEnum::BranchPlanner->value) {
            $this->planner->pendingWorkflows()->where('Stage', PlannerStatus::MarketingManager->name)->update([
                'DeletedOn' => now(),
                'DeletedBy' => $actor->Id,
            ]);

            $this->planner->workflows()->create([
                'Stage' => PlannerStatus::MarketingManager->name,
                'Status' => WorkflowStatus::Accepted->value,
                'Notes' => 'Marketing Manager Approval',
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
            ]);

            $this->_notifyCeo($actor);

            activity()->causedBy($actor)->performedOn($this->planner)->event('approve')->log('Approved marketing plan ' . $this->planner->PlannerID);

            return $this;
        }

        throw new ErroredException('unable to approve marketing plan');
    }

    private function _notifyCeo(User $actor): void
    {
        $users = UserService::ceos(true)->whereNotIn(
            't_Users.Id',
            $this->planner->workflows()
            ->whereIn('Status', [WorkflowStatus::Submitted->value, WorkflowStatus::Accepted->value])->select('CreatedBy')
        )->get(["Id", "UserID", "Name", "Email"]);
        foreach ($users as $user) {
            if (! $user instanceof User) {
                continue;
            }
            if (in_array($user->UserID, [$actor->UserID, SystemHelper::ID], true)) {//skip sys and submitter
                continue;
            }

            $this->planner->pendingWorkflows()->create([
                'Stage' => PlannerStatus::Ceo->name,
                'UserId' => $user->Id,
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
            ]);

            //$this->_sendMail($user);
            (new UserService($user))->sendEmail(
                subject: 'Marketing Plan submitted for review and approval',
                body: '<p>Hello</p><p>The plan <b>' . Str::upper($this->planner->PlannerID) . '</b> has been submitted for your review. Click the link below to review</p>
                    <p><a href="' . route('marketing-planner.show', [$this->planner->PlannerID]) . '"> planner details</a></p>
                    <p>Kindly review and approve the plan at your earliest convenience.</p>'
            );
        }
    }

    /**
     * @throws ErroredException
     */
    public function managerWorkflowReject(User $actor, string $reason): static
    {
        if (! $this->canApprove($actor)) {
            throw new ErroredException('cannot reject, no permission');
        }

        $this->planner->fill([//change status to BM &&
            'Status' => PlannerStatus::Draft->value,
        ])->save(['timestamps' => false]);

        $pending = $this->planner->pendingWorkflows()->where('Stage', PlannerStatus::MarketingManager->name)->where('UserId', $actor->Id)->first();
        if ($pending instanceof PendingWorkflow) {
            $pending->forceFill([
                'DeletedOn' => now(),
                'DeletedBy' => $actor->Id,
            ])->save();
        }

        $this->planner->workflows()->create([
            'Stage' => PlannerStatus::MarketingManager->name,
            'Status' => WorkflowStatus::RejectReturn->value,
            'Notes' => $reason,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        $owner = $this->planner->owner;
        if ($owner instanceof User) {
            (new UserService($owner))->sendEmail(
                'Update on Marketing Plan Submission',
                '<p>Hello</p><p>The marketing plan <b>' . $this->planner->PlannerID . '</b>  has not been approved at this time. Click the link below to review</p>
                <p><a href="' . route('marketing-planner.show', [$this->planner->PlannerID]) . '">' . $this->planner->PlannerID . ' details</a></p>
                <p><b>Reason Given: </b>&nbsp;' . $reason . '</p>'
            );
        }

        activity()->causedBy($actor)->performedOn($this->planner)->event('reject')->log('Reject marketing plan ' . $this->planner->PlannerID);

        return $this;
    }

    /**
     * Submit for approval
     * @throws ErroredException
     */
    public function submit(Branch $branch, User $actor): static
    {
        $this->syncDates();

        if ((! $branch->manager instanceof User) && (! $branch->operation instanceof User)) {
            throw new ErroredException('cannot submit, no branch manager');
        }
        // add to pending workflow
        if ($branch->manager instanceof User) {
            PendingWorkflow::create([
                'Source' => MarketingPlanner::getPrimaryKey(),
                'SourceID' => $this->planner->Id,
                'Stage' => PlannerStatus::BranchManager->name,
                'UserId' => $branch->manager->Id,
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
            ]);
            if ($branch->manager->Id === $actor->Id) {
                return $this->branchWorkflowApprove($actor);
            }
            (new UserService($branch->manager))->sendEmail(
                'Marketing Plan Submission for Your Review and Approval',
                '<p>Hello</p><p>A marketing plan <b>' . $this->planner->PlannerID . '</b> has been prepared and submitted for your review. Click the link below to review</p>
                    <p><a href="' . route('marketing-planner.show', [$this->planner->PlannerID]) . '">' . $this->planner->PlannerID . ' details</a></p><p>Kindly review and approve the plan at your earliest convenience.</p>'
            );
        }

        if ($branch->operation instanceof User) {
            PendingWorkflow::create([
                'Source' => MarketingPlanner::getPrimaryKey(),
                'SourceID' => $this->planner->Id,
                'Stage' => PlannerStatus::BranchManager->name,
                'UserId' => $branch->operation->Id,
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
            ]);

            if ($branch->operation->Id === $actor->Id) {
                return $this->branchWorkflowApprove($actor);
            }

            (new UserService($branch->operation))->sendEmail(
                'Marketing Plan Submission for Your Review and Approval',
                '<p>Hello</p><p>A marketing plan <b>' . $this->planner->PlannerID . '</b> has been prepared and submitted for your review. Click the link below to review</p>
                    <p><a href="' . route('marketing-planner.show', [$this->planner->PlannerID]) . '">' . $this->planner->PlannerID . ' details</a></p><p>Kindly review and approve the plan at your earliest convenience.</p>'
            );
        }


        $this->planner->fill([//change status to BM &&
            'Status' => PlannerStatus::BranchManager->value,
        ])->save(['timestamps' => false]);
        //add workflow
        Workflow::create([
            'Source' => MarketingPlanner::getPrimaryKey(),
            'SourceID' => $this->planner->Id,
            'Stage' => PlannerStatus::Draft->name,
            'Status' => WorkflowStatus::Submitted->value,
            'Notes' => 'User Submitted',
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->performedOn($this->planner)->event('submit')->log('Submitted ' . $this->planner->PlannerID . ' for approval.');

        return $this;
    }

    public function syncDates(): static
    {
        $start = null;
        $end = null;
        $startActivity = $this->planner->activities()->oldest('StartOn')->first('StartOn');
        if ($startActivity instanceof MarketingPlannerActivity) {
            $start = $startActivity->StartOn;
        }
        $endActivity = $this->planner->activities()->latest('EndOn')->first('EndOn');
        if ($endActivity instanceof MarketingPlannerActivity) {
            $end = $endActivity->EndOn;
        }
        $this->planner->fill([
            'StartOn' => $start,
            'EndOn' => $end,
        ])->save(['timestamps' => false]);

        return $this;
    }

    /**
     * @throws ErroredException
     */
    public function branchWorkflowApprove(User $actor): static
    {
        if (! $this->canApprove($actor)) {
            throw new ErroredException('cannot approve, no permission');
        }

        $this->planner->fill([//change status to BM &&
            'Status' => PlannerStatus::MarketingManager->value,
        ])->save(['timestamps' => false]);

        $this->planner->pendingWorkflows()->where('Stage', PlannerStatus::BranchManager->name)/*->where('UserId', $actor->Id)*/ ->update([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id,
        ]);

        $this->planner->workflows()->create([
            'Stage' => PlannerStatus::BranchManager->name,
            'Status' => WorkflowStatus::Accepted->value,
            'Notes' => 'Branch Manager Approval',
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);
        $marketingManagers = UserService::marketingManagers(true)->whereNotIn(
            't_Users.Id',
            $this->planner->workflows()->whereIn('Status', [WorkflowStatus::Submitted->value, WorkflowStatus::Accepted->value])->select('CreatedBy')
        )->get(["Id", "UserID", "Name", "Email"]);
        foreach ($marketingManagers as $marketingManager) {
            $this->planner->pendingWorkflows()->create([
                'Stage' => PlannerStatus::MarketingManager->name,
                'UserId' => $marketingManager->Id,
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
            ]);

            (new UserService($marketingManager))->sendEmail(
                'Marketing Plan Submission for Your Review and Approval',
                '<p>Hello</p><p>A marketing plan <b>' . $this->planner->PlannerID . '</b> has been prepared and submitted for your review. Click the link below to review</p>
                    <p><a href="' . route('marketing-planner.show', [$this->planner->PlannerID]) . '">' . $this->planner->PlannerID . ' details</a></p><p>Kindly review and approve the plan at your earliest convenience.</p>'
            );
        }

        activity()->causedBy($actor)->performedOn($this->planner)->event('approve')->log('Approved marketing plan ' . $this->planner->PlannerID);

        return $this;
    }

    /**
     * @throws ErroredException
     */
    public function trash(User $actor): void
    {
        if ($this->planner->Type->value === PlannerTypeEnum::BranchPlanner->value) {
            $this->planner->forceFill([
                'DeletedBy' => $actor->id,
                'DeletedOn' => now(),
            ])->save();
            activity()->causedBy($actor)->performedOn($this->planner)->event('delete')->log('Deleted draft  plan ' . $this->planner->PlannerID);

            return;
        }

        if ($this->planner->Type->value === PlannerTypeEnum::MasterPlanner->value) {
            $this->planner->forceFill([
                'DeletedBy' => $actor->id,
                'DeletedOn' => now(),
            ])->save();

            $this->planner->plans()->update([
                'MasterPlannerId' => null,
                'Status' => PlannerStatus::MarketingManager->value,
            ]);

            $this->planner->activities()->update(['MasterPlannerId' => null]);

            activity()->causedBy($actor)->performedOn($this->planner)->event('delete')->log('Deleted draft master  plan ' . $this->planner->PlannerID);

            return;
        }

        throw new ErroredException('unknown planer type');
    }

    public function addActivity(string $Name, string $Location, Carbon $start, Carbon $end, float $budget, string $Notes, User $actor, Collection $users, Branch $branch = null, string $Materials = ''): static
    {
        $activity = new MarketingPlannerActivity();
        $activity->fill([
            'PlannerActivityID' => $this->_activityID(),
            'Name' => $Name,
            'PlannerId' => $this->planner->Id,
            'MasterPlannerId' => ($this->planner->Type->value === PlannerTypeEnum::MasterPlanner->value) ? $this->planner->Id : null,
            'Location' => $Location,
            'Notes' => $Notes,
            'Materials' => $Materials,
            'BranchId' => ($branch instanceof Branch) ? $branch->Id : $this->planner->BranchId,
            'StartOn' => $start,
            'EndOn' => $end,
            'Budget' => $budget,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        $activity->refresh();


        $activity->users()->syncWithPivotValues($users->pluck('Id')->toArray(), ['CreatedBy' => $actor->Id, 'ModifiedBy' => $actor->Id], false);

        return $this->syncDates();
    }

    private function _activityID(): string
    {
        $number = $this->planner->activities()->count();
        do {
            $number++;
            $slug = Str::slug($this->planner->PlannerID . '-' . Str::padLeft(($number), 2, '0'));
        } while (MarketingPlannerActivity::where('PlannerActivityID', $slug)->withTrashed()->exists());

        return $slug;
    }

    public function updateActivity(MarketingPlannerActivity $activity, string $Name, string $Location, Carbon $start, Carbon $end, float $budget, string $Notes, User $actor, Collection $users, string $branchId): static
    {
        $activity->fill([
            'Name' => $Name,
            'Location' => $Location,
            'Notes' => $Notes,
            'BranchId' => $branchId,
            'StartOn' => $start,
            'EndOn' => $end,
            'Budget' => $budget,
            'ModifiedBy' => $actor->Id,
        ])->save();

        $activity->users()->syncWithPivotValues($users->pluck('Id')->toArray(), ['CreatedBy' => $actor->Id, 'ModifiedBy' => $actor->Id], true);

        return $this->syncDates();
    }
}
