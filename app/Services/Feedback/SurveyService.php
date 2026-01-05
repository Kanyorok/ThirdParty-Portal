<?php

namespace App\Services\Feedback;

use App\Enums\Core\PermissionEnum;
use App\Enums\Feedback\SurveyQuestionTypeEnum;
use App\Enums\Feedback\SurveyStatusEnum;
use App\Enums\WorkflowStatus;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\CRM\Survey;
use App\Models\CRM\SurveyQuestion;
use App\Services\HRM\UserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SurveyService
{
    public function __construct(public Survey $survey) {}

    public static function active(): ?SurveyService
    {
        $survey = Survey::query()->where('t_Surveys.StartOn', '<=', now())->where('t_Surveys.EndOn', '>=', now())
            ->where('t_Surveys.Status', SurveyStatusEnum::Active->value)->latest()->with(['questions'])->first();
        return ($survey instanceof Survey)
            ? new self($survey)
            : null;
    }

    public function trash(User $actor): void
    {
        $this->survey->forceFill([
            'DeletedBy' => $actor->Id,
            'DeletedOn' => now(),
        ])->save();
    }

    public function canApprove(User $actor): bool
    {
        return in_array($actor->Id, $this->survey->pendingWorkflows()->get('t_PendingWorkflows_static.UserId')->pluck('UserId')->toArray(), true);
    }

    public function addQuestion(SurveyQuestionTypeEnum $type, string $question, User $actor, string $help): static
    {
        $this->survey->questions()->create([
            'SurveyQuestionId' => $this->_questionId(),
            'Type' => $type->value,
            'Question' => $question,
            'Notes' => $help,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        return $this;
    }

    public static function create(string $label, Carbon $start, Carbon $end, User $actor, string $notes): SurveyService
    {
        $new = new Survey();
        $new->fill([
            'SurveyID' => Str::upper(self::_ID()),
            'Status' => SurveyStatusEnum::Draft->value,
            'CreatedBy' => $actor->Id,
        ]);

        return (new self($new))->update($label, $start, $end, $actor, $notes);
    }

    protected static function _ID(): string
    {
        $number = Survey::query()->withTrashed()->count();
        do {
            $number++;
            $slug = Str::slug('S' . Str::padLeft(($number), 4, '0'));
        } while (Survey::where('SurveyID', $slug)->withTrashed()->exists());

        return $slug;
    }

    public function update(string $label, Carbon $start, Carbon $end, User $actor, string $notes): static
    {
        $this->survey->fill([
            'Label' => $label,
            'Notes' => $notes,
            'StartOn' => $start,
            'EndOn' => $end,
            'ModifiedBy' => $actor->Id,
        ])->save();

        return $this;
    }

    protected function _questionId(): string
    {
        $number = $this->survey->questions()->count();
        do {
            $number++;

            $slug = Str::slug($this->survey->SurveyID . '-' . Str::padLeft(($number), 2, '0'));
        } while (SurveyQuestion::where('SurveyQuestionId', $slug)->withTrashed()->exists());

        return $slug;
    }

    public function submit(User $actor): static
    {
        $this->survey->forceFill([
            'Status' => SurveyStatusEnum::Approval->value,
        ])->save(['timestamps' => false]);

        //add workflow
        $this->survey->workflows()->create([
            'Stage' => SurveyStatusEnum::Draft->name,
            'Status' => WorkflowStatus::Submitted->value,
            'Notes' => 'User Submitted',
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        $users = User::query()->lock('WITH(NOLOCK)')->hasPermission(PermissionEnum::SurveyApproval->value)->get(["Id", "UserID", "Name", "Email"]);
        DB::transaction(function () use ($actor, $users) {
            foreach ($users as $user) {
                if (!$user instanceof User) {
                    continue;
                }
                if (in_array($user->UserID, [$actor->UserID, SystemHelper::ID], true)) { //skip sys and submitter
                    continue;
                }

                $this->survey->pendingWorkflows()->lock('WITH(NOLOCK)')->where('Stage', SurveyStatusEnum::Approval)->create([
                    'Stage' => SurveyStatusEnum::Approval,
                    'UserId' => $user->Id,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);


                (new UserService($user))->sendEmail(
                    subject: 'Survey submitted for review and approval',
                    body: '<p>Hello</p><p>The survey <b>' . $this->survey->Label . '</b> has been submitted for your review. Click the link below to review</p>
                    <p><a href="' . route('surveys.show', [$this->survey->SurveyID]) . '"> survey details</a></p>
                    <p>Kindly review and approve the survey at your earliest convenience.</p>'
                );
            }
        });


        activity()->causedBy($actor)->performedOn($this->survey)->event('submit')->log('Submitted ' . $this->survey->SurveyID . ' for approval.');

        return $this;
    }

    public function workflowApprove(User $actor): static
    {
        $this->survey->forceFill([
            'Status' => SurveyStatusEnum::Active->value,
        ])->save(['timestamps' => false]);

        $this->survey->pendingWorkflows()->where('Stage', SurveyStatusEnum::Approval)->update([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id,
        ]);

        $this->survey->workflows()->create([
            'Stage' => SurveyStatusEnum::Approval->name,
            'Status' => WorkflowStatus::Accepted->value,
            'Notes' => 'Survey Approval',
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        $owner = $this->survey->modified;
        if ($owner instanceof User) {
            (new UserService($owner))->sendEmail(
                'Update on Survey Submission',
                '<p>Hello</p><p>The survey <b>' . $this->survey->Label . '</b>  has been approved. Click the link below to view</p>
                <p><a href="' . route('surveys.show', [$this->survey->SurveyID]) . '"> survey details</a></p>
                <p>This survey will run on the set dates.</p>'
            );
        }

        activity()->causedBy($actor)->performedOn($this->survey)->event('approve')->log('Approved survey ' . $this->survey->SurveyID);

        return $this;
    }

    public function workflowReject(User $actor, string $reason): static
    {
        $this->survey->forceFill([
            'Status' => SurveyStatusEnum::Draft,
        ])->save(['timestamps' => false]);


        $this->survey->pendingWorkflows()->where('Stage', SurveyStatusEnum::Approval)->update([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id,
        ]);

        $this->survey->workflows()->create([
            'Stage' => SurveyStatusEnum::Approval->name,
            'Status' => WorkflowStatus::RejectReturn->value,
            'Notes' => $reason,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        $owner = $this->survey->modified;
        if ($owner instanceof User) {
            (new UserService($owner))->sendEmail(
                'Update on Survey Submission',
                '<p>Hello</p><p>The survey <b>' . $this->survey->Label . '</b> has <b style="color: #fa2f43">NOT</b> been approved. Click the link below to review</p>
                <p><a href="' . route('surveys.show', [$this->survey->SurveyID]) . '"> survey details</a></p>
                <p><b>Reason Given: </b>&nbsp;' . $reason . '</p>'
            );
        }

        activity()->causedBy($actor)->performedOn($this->survey)->event('reject')->log('Reject survey ' . $this->survey->SurveyID);

        return $this;
    }
}
