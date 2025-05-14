<?php

namespace App\Services\DebtCollection;

use App\Enums\Core\PermissionEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\BR\DebtProduct;
use App\Models\DebtRecovery\LoanAssignment;
use App\Services\HRM\UserService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class LoanService
{
    public function __construct(public DebtProduct $loan)
    {
    }

    public static function getAssignUser(string $AccountID = null): ?User
    {
        if (!is_null($AccountID)) {//this part is used to reassign a previously assigned email to this guy.
            $loanAssignment = LoanAssignment::where('t_LoanAssignments.AccountID', $AccountID)->latest('Id')->first();
            if ($loanAssignment instanceof LoanAssignment) {
                $user = $loanAssignment->user;
                if ($user instanceof User && $user->can(PermissionEnum::DebtCollectionAssignment)) {
                    return $user;
                }
            }
        }

        $user = User::query()->lock('WITH(NOLOCK)')->hasPermission(PermissionEnum::DebtCollectionAssignment->value)
            ->withCount([
                         'loansAssigned' => function (Builder $builder) {
                                    $builder->whereNull('t_LoanAssignments.EndOn');
                         },
                        ])->orderBy("loans_assigned_count", 'asc')->first();

        return ($user instanceof User) ? $user : null;
    }

    /**
     * @throws ErroredException
     */
    public function assign(User $assignee, User $actor, bool $notify = true): static
    {
        if ($this->loan->assignment()->whereNull('EndOn')->where('UserId', $assignee->Id)->exists()) {
            throw new ErroredException('Loan already assigned to this user.');
        }

        $this->loan->assignment()->whereNull('EndOn')->update([
                                                               'EndOn'      => Carbon::now(),
                                                               'ModifiedBy' => $actor->Id,
                                                               'Notes'      => 'REASSIGNED',
                                                              ]);

        $this->loan->assignment()->create([
                                           'StartOn'    => Carbon::now(),
                                           'UserId'     => $assignee->Id,
                                           'CreatedBy'  => $actor->Id,
                                           'ModifiedBy' => $actor->Id,
                                          ]);

        if ($notify) {
            (new UserService($assignee))->sendEmail(
                'Loan Assignment Notification',
                body: '<p>Dear ' . $assignee->Name . ',</p>
                <p>You have been assigned a new loan account with the following details:</p>
                <p><strong>Loan :</strong> ' . $this->loan->AccountID . '</p>
                <p>You can view the <a href="' . route('debt-collection.show', [$this->loan->AccountID]) . '">loan details here</a>.</p>
                <p>Please log in to your account and take the necessary actions.</p>
                <p>This is an automated message. Please do not reply to this email.</p>'
            );
        }

        return $this;
    }
}
