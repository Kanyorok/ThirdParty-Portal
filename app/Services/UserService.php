<?php

namespace App\Services;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\PermissionEnum;
use App\Enums\EmailPriorityEnum;
use App\Enums\GenderEnum;
use App\Helpers\SystemHelper;
use App\Models\Board;
use App\Models\BR\Branch;
use App\Models\BR\BRUser;
use App\Models\BulkNotification;
use App\Models\CrmEmail;
use App\Models\User;
use App\Services\BR\CBSService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class UserService
{
    public const string MODULE = 'USERS';

    public function __construct(public User $user)
    {
    }

    public function isManager(): bool
    {
        return $this->user->can(PermissionEnum::Managers->value);
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

    public static function ceos(bool $query = false): Builder|Collection
    {
        $q = User::query()->hasPermission(PermissionEnum::Ceo->value)->lock('WITH(NOLOCK)');
        return ($query) ? $q : $q->get();
    }

    public static function create(Branch $branch, string $UserID, string $Name, string $Email, string $Phone, GenderEnum $Gender, User $actor, string $Notes = '', string $Signature = ''): UserService
    {
        return new UserService(User::create([
            'UserID' => $UserID,
            'Name' => $Name,
            'Email' => $Email,
            'Phone' => $Phone,
            'Gender' => $Gender->value,
            'Linked' => false,
            'Notes' => $Notes,
            'Password' => Str::random(),
            'Email_Signature' => $Signature,
            'BranchId' => $branch->OurBranchID,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]));
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
            })->editColumn('branch.BranchName', function (User $user) use ($with) {
                if (in_array('branch', $with, true)) {
                    if ($user->branch instanceof Branch) {
                        return $user->branch->BranchName;
                    }
                    $branch = Branch::query()->where('OurBranchID', $user->BranchId)->first();
                    if ($branch instanceof Branch) {
                        return $branch->BranchName;
                    }
                }
                return '';
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
                $str = ($user->Linked) ? ' - linked' : ' - crm';
                return $user->Name . $str;
            })->editColumn('Gender', function (User $user) {
                return $user->Gender->name;
            })->editColumn('photo', function (User $user) use ($with) {
                return (in_array('photo', $with, true)) ?
                    $user->getImage('class="img-thumbnail" style="height: 70px; max-width: inherit;"')
                    : '';
            })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                                                                                                   'dbl_click_url' => function (User $user) {
                                                                                                    return route('users.show', [$user->UserID]);
                                                                                                   },
                                                                                                  ])->rawColumns(['action', 'photo'])->make();
    }

    public function setRole(Role $role): static
    {
        $this->user->syncRoles($role->name);
        return $this;
    }

    public function syncBR(bool $pullImages = false): static
    {
        $br_user = $this->brUser();
        if (is_null($br_user)) {
            return $this;
        }

        $this->user->update([
                             'Linked'   => true,
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

    public function update(string $UserID, string $Name, string $Email, string $Phone, GenderEnum $Gender, User $actor, string $Signature = '', string $Notes = '', Branch $branch = null, string $ClientID = null): static
    {
        $email_change = ($this->user->Email === $Email) ? null : $this->user->Email;
        $this->user->update([
                             'UserID'          => $UserID,
                             'Name'            => $Name,
                             'Email'           => $Email,
                             'Phone'           => $Phone,
                             'ClientID'        => $ClientID,
                             'Gender'          => $Gender->value,
                             'BranchId'        => ($branch instanceof Branch) ? $branch->OurBranchID : $this->user->BranchId,
                             'Notes'           => $Notes,
                             'Email_Signature' => $Signature,
                             'ModifiedBy'      => $actor->Id,
                             'ModifiedOn'      => now(),
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

    public function sendEmail(string $subject, string $body, array $cc = [], bool|null $immediate = false, EmailPriorityEnum $priorityEnum = EmailPriorityEnum::Normal, CrmEmail $email = null): ?CRMEmailService
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
    private function createResetURL(): string
    {
        return url(route('password.reset', [
                                            'token' => Password::createToken($this->user),
                                            'email' => $this->user->Email,
                                           ], false));
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

    public function trash(User $actor): void
    {

        $this->user->forceFill([
                                'DeletedOn' => now(),
                                'DeletedBy' => $actor->Id,
                               ])->save(['timestamps' => false]);

        $this->sendEmail('account deleted', '<p>Hello ' . $this->user->Name . '<br>Your account has just been deleted <br> If you have any questions or concerns, feel free to reach out to our support team </p>');
    }
}
