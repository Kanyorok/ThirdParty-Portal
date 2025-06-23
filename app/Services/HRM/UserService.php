<?php

namespace App\Services\HRM;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\PermissionEnum;
use App\Enums\EmailPriorityEnum;
use App\Enums\Employee\GenderEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\BR\BRUser;
use App\Models\Communication\BulkNotification;
use App\Models\Communication\Email;
use App\Models\Core\Branch;
use App\Models\HRM\Employee;
use App\Models\ThirdParies\Board;
use App\Services\BR\CBSService;
use App\Services\CRMEmailService;
use App\Services\SMSService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Throwable;
use Yajra\DataTables\DataTables;

class UserService
{
    public const string MODULE = 'USERS';

    public function __construct(public User $user)
    {
    }

    public static function ceos(bool $query = false): Builder|Collection
    {
        $q = User::query()->hasPermission(PermissionEnum::Ceo->value)->lock('WITH(NOLOCK)');
        return ($query) ? $q : $q->get();
    }

    public static function create(Employee $employee, User $actor): UserService
    {
        $user = $employee->user;
        if ($user instanceof User) {
            if ($user->trashed()) {
                $user->forceFill([
                    'Name' => $employee->full_name,
                    'Email' => $employee->Email,
                    'Phone' => $employee->Phone,
                    'Password' => Str::random(10),
                    'ModifiedBy' => $actor->Id,
                    'DeletedOn' => null,
                    'DeletedBy' => null,
                ])->save();
            }
            return new self($user);
        }
        $user = User::create([
            'UserID' => self::_ID($employee->FirstName, $employee->LastName),
            'Name' => $employee->full_name,
            'Email' => $employee->Email,
            'Phone' => $employee->Phone,
            'ImageId' => $employee->ImageId,
            'EmployeeId' => $employee->Id,
            'Linked' => false,
            'Password' => Str::random(10),
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->performedOn($user)->event('create')->log('Created user account ' . $user->UserID . ' for employee ' . $employee->EmployeeID);

        return new self($user);
    }

    protected static function _ID(string $FirstName, string $Surname): string
    {
        $baseId = Str::of($FirstName)->trim()->substr(0, 1) . Str::of($Surname)->trim()->slug('')->upper()->toString();
        $number = 0;
        do {
            $userId = $number === 0 ? Str::of($baseId) : Str::of($baseId . $number);
            $number++;
        } while (User::query()->where('UserID', $userId->slug('')->upper()->toString())->withTrashed()->exists());

        return $userId->slug('')->upper()->toString();
    }

    /**
     * @throws Exception
     */
    public static function dt(Builder|BelongsToMany $query, array $with = [], array $extra = []): JsonResponse
    {
        if (!empty($with)) {
            $query->with($with);
        }
        return Datatables::of($query->where('t_Users.UserID', '!=', SystemHelper::ID)->lock('WITH(NOLOCK)')->select('*'))
            ->addColumn('action', function (User $user) use ($extra) {
                if (array_key_exists('action_team', $extra)) {
                    return '<button type="button"  data-action="' . route('team-users.destroy', [$extra['action_team'], $user->UserID]) . '" data-name="' . $user->Name . '" class="btn btn-danger btn-sm modal-trash-team-users"><i class="fas fa-trash"></i></button>';
                }
                return '<a  href="' . route('users.show', [$user->UserID]) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> details</button>';
            })->editColumn('pivot', function (User $user) use ($extra) {
                if (!in_array('pivot_date', $extra, true)) {
                    return '';
                }
                try {
                    return Carbon::parse($user->pivot->CreatedOn)->format('M d, Y h:i A');
                } catch (Exception) {
                }
                return $user->pivot->CreatedOn;
            })->editColumn('Name', function (User $user) {
                $str = ($user->Linked) ? '(one account)' : '';
                return $user->Name . $str;
            })->editColumn('photo', function (User $user) use ($with) {
                return (in_array('photo', $with, true)) ?
                    $user->getImage('class="img-thumbnail" style="height: 70px;width: 70px; max-width: inherit;"')
                    : '';
            })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                'dbl_click_url' => function (User $user) {
                    return route('users.show', [$user->UserID]);
                },
            ])->rawColumns(['action', 'photo'])->make();
    }

    public function sendMessage(string $message, User $actor, bool $immediate = false, BulkNotification $bulkNotification = null): static
    {
        if (SystemHelper::isSystem($this->user)) {
            return $this;
        }

        $service = SMSService::createUser($this->user, $message, $actor);
        if ($bulkNotification instanceof BulkNotification) {
            $service->setBulk($bulkNotification);
        }
        $service->send($immediate);

        return $this;
    }

    public function isMarketingManager(): bool
    {
        return self::marketingManagers(true)->where('Id', $this->user->Id)->exists();
    }

    public static function marketingManagers(bool $query = false): Builder|Collection
    {
        $q = User::query()->hasPermission(PermissionEnum::MarketingManager->value)->lock('WITH(NOLOCK)');
        return ($query) ? $q : $q->get();
    }

    public function setRole(Role $role, User $actor): static
    {
        $this->user->syncRoles($role->name);

        activity()->causedBy($actor)->performedOn($this->user)->event('update')->log('Updated user ' . $this->user->UserID . ' role to ' . $role->name);

        return $this;
    }

    public function syncBR(bool $pullImages = false): static
    {
        $br_user = $this->brUser();
        if (is_null($br_user)) {
            return $this;
        }

        $this->user->update([
            'Linked' => true,
            'Password' => $br_user->Password,
            'ClientID' => $br_user->ClientID,
        ]);

        if ($pullImages) {
            $str = (new CBSService())->getClientImage($this->user->ClientID);
            if (!empty($str)) {
                $this->user->setFromContent(base64_decode($str), SystemHelper::user(), ExtensionsEnum::Jpeg->getMimeType(), $this->user->ClientID . '.' . ExtensionsEnum::Jpeg->value, 'ImageId');
            }
        }

        return $this;
    }

    protected function brUser(): ?BRUser
    {
        $user = BRUser::where('OperatorID', $this->user->UserID)->first();
        return ($user instanceof BRUser) ? $user : null;
    }

    public function update(string $UserID, string $Name, string $Email, string $Phone, GenderEnum $Gender, User $actor, string $Signature = '', string $Notes = '', Branch $branch = null): static
    {
        $email_change = ($this->user->Email === $Email) ? null : $this->user->Email;
        $this->user->update([
            'UserID' => $UserID,
            'Name' => $Name,
            'Email' => $Email,
            'Phone' => $Phone,
            'Gender' => $Gender->value,
            'BranchId' => ($branch instanceof Branch) ? $branch->BranchID : $this->user->BranchId,
            'Notes' => $Notes,
            'Email_Signature' => $Signature,
            'ModifiedBy' => $actor->Id,
            'ModifiedOn' => now(),
        ]);
        activity()->causedBy($this->user)->performedOn($this->user)->event('update')->log('Update user account');

        if (!is_null($email_change)) {
            $this->sendEmail(
                'Email Changed in Crm',
                '<div><p>Hello ' . $this->user->Name . ' </p><p>Your email has been changed from <b>' . $email_change . '</b> to <b>' . $this->user->Email . '</b> </p><p>if this was a mistake, contact support</p></div>',
                [[$Name => $email_change]]
            );
        }
        return $this;
    }

    public function sendEmail(string $subject, string $body, array $cc = [], bool|null $immediate = false, EmailPriorityEnum $priorityEnum = EmailPriorityEnum::Normal, Email $email = null): ?CRMEmailService
    {
        if (SystemHelper::isSystem($this->user)) {
            return null;
        }
        $service = CRMEmailService::createUser($this->user, $subject, $body, SystemHelper::user(), $cc, $priorityEnum, $email);
        if (is_null($immediate)) {
            return $service;
        }

        return $service->send($immediate);
    }

    public function welcomeEmail(): static
    {
        if (now()->subDays(2)->startOfDay()->greaterThan($this->user->CreatedOn)) {
            return $this;
        }
        $body = '<div><p>Hello ' . $this->user->Name . '</p>';
        $body .= ($this->user->Linked) ?
            '<p>An account has been created for you in ' . config('app.name') . ' use your Core banking password to <a href="' . route('login') . '">login</a></p>' :
            '<p>An account has been created for you in ' . config('app.name') . ' use your UserID is <b>' . $this->user->UserID . '</b> </p> <p>click this link to <a href="' . $this->createResetURL() . '"> create your password</a></p>';
        $body .= '</div>';

        $this->sendEmail('New account created in ' . config('app.name'), $body, priorityEnum: EmailPriorityEnum::Important);
        return $this;
    }

    private function createResetURL(): string
    {
        return url(route('password.reset', [
            'token' => Password::createToken($this->user),
            'email' => $this->user->Email,
        ], false));
    }

    public function hideUsers(\Illuminate\Database\Query\Builder|Builder $query, string $ClientID = 'ClientID'): \Illuminate\Database\Query\Builder|Builder
    {
        if ($this->isManager()) {
            return $query;
        }
        return $query->where(function ($query) use ($ClientID) {
            $query->whereNotIn($ClientID, User::whereNotNull('ClientID')->where('ClientID', '!=', $this->user->ClientID)->select('t_Users.ClientID'))
                ->whereNotIn($ClientID, Board::query()->select('t_BoardMembers.ClientID'));
        });
    }

    public function isManager(): bool
    {
        return $this->user->can(PermissionEnum::Managers->value);
    }

    public function sendPasswordResetNotification(): static
    {
        $this->sendEmail(
            'Reset Password Notification',
            '<p>You are receiving this email because we received a password reset request for your account.</p><a  href="' . $this->createResetURL() . '">Reset Password</a>
                    <p>This password reset link will expire in 60 minutes. <br> If you did not request a password reset, no further action is required.</p>'
        );
        return $this;
    }

    /**
     * @throws ErroredException
     */
    public function trash(User $actor): void
    {
        try {
            DB::transaction(function () use ($actor) {
                $this->user->forceFill([
                    'DeletedOn' => now(),
                    'DeletedBy' => $actor->Id,
                ])->save(['timestamps' => false]);

                activity()->causedBy($actor)->performedOn($this->user)->event('delete')->log('Deleted user account ' . $this->user->UserID);

                $this->sendEmail('account deleted', '<p>Hello ' . $this->user->Name . '<br>Your account has just been deleted <br> If you have any questions or concerns, feel free to reach out to our support team </p>');

            });
        } catch (Throwable|ErroredException $e) {
            Log::error('Error delete user ' . $e->getMessage());
            throw new ErroredException();
        }

    }
}
