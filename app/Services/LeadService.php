<?php

namespace App\Services;

use App\Enums\Core\RoleEnum;
use App\Enums\Employee\GenderEnum;
use App\Enums\LeadStatusEnum;
use App\Enums\LeadTypeEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\Communication\BulkNotification;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Locality;
use App\Models\CRM\Lead;
use App\Models\CRM\LeadUser;
use App\Services\HRM\UserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class LeadService
{
    public function __construct(public Lead $lead)
    {
    }

    /**
     * @throws Exception
     */
    public static function dt(Builder $query, array $with = []): JsonResponse
    {
        $query->lock('WITH(NOLOCK)');
        if (!empty($with)) {
            $query->with($with);
        }
        return Datatables::of($query->select('*'))
            ->editColumn('photo', function (Lead $lead) use ($with) {
                if (in_array('photo', $with, true)) {
                    return $lead->getImage('class="img-thumbnail" style="height: 70px;"');
                }
                return '';
            })->editColumn('Status', function (Lead $lead) {
                return $lead->Status->name;
            })->editColumn('Type', function (Lead $lead) {
                return $lead->Type->name;
            })->editColumn('industry', function (Lead $lead) use ($with) {
                if (in_array('industry', $with, true)) {
                    return $lead->industry?->Description;
                }
                return '';
            })->editColumn('location', function (Lead $lead) use ($with) {
                if (in_array('location', $with, true)) {
                    return $lead->location?->Name;
                }
                return '';
            })->editColumn('LastContacted', function (Lead $lead) {
                return $lead->LastContacted?->diffForHumans();
            })->editColumn('ModifiedOn', function (Lead $lead) {
                return $lead->ModifiedOn->format('F d, Y h:i A');
            })->editColumn('CreatedOn', function (Lead $lead) {
                return $lead->CreatedOn?->diffForHumans();
            })->editColumn('Name', function (Lead $lead) {
                return '<a href="#" data-click_url="' . route('leads.summary', $lead->LeadID) . '" data-summary_title="lead summary" class="click-summary-data">' . $lead->Name . '</a>';
            })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                'dbl_click_url' => function (Lead $lead) {
                    return route('leads.show', $lead->LeadID);
                },
            ])->rawColumns(['Name', 'photo'])->make();
    }

    public static function company(
        string   $name, string $email, string $phone, string $website, Carbon $last_contact, User $manager, User $actor,
        Locality $location, CodeDetail $industry = null, CodeDetail $source = null, CodeDetail $customerType = null, string $notes = ''
    ): self
    {
        $service = self::_create($name, '', $email, $phone, $website, '', $last_contact, LeadTypeEnum::Company, GenderEnum::Other, $actor, $notes, $location, $industry, $source, $customerType);
        if ($manager->Id !== $actor->Id) {
            return $service->assign($manager, $actor);
        }
        return $service;
    }

    private static function _create(
        string       $name, string $other_names, string $email, string $phone, string $website, string $job_title, Carbon $last_contact,
        LeadTypeEnum $type, GenderEnum $gender, User $actor, string $notes, Locality $location, CodeDetail $industry = null,
        CodeDetail   $source = null, CodeDetail $customerType = null
    ): self
    {
        $lead = new Lead();
        $lead->fill([
            "Name" => $name,
            "Email" => $email,
            "Phone" => $phone,
            "Website" => $website,
            "Gender" => $gender->value,
            "Status" => LeadStatusEnum::Warm->value,
            "RelationshipManagerID" => $actor->Id,
            "LocationID" => $location->ID,
            'CountryId' => $location->CountryId,
            "Industry" => $industry?->ID,
            "Source" => $source?->ID,
            "JobTitle" => $job_title,
            "OtherNames" => $other_names,
            "LastContacted" => $last_contact,
            "CustomerType" => $customerType?->ID,
            "Type" => $type->value,
            'Notes' => $notes,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        $service = (new self($lead));
        $service->activity($lead->Name . ' added by ' . $actor->UserID, now(), $actor);

        activity()->causedBy($actor)->performedOn($lead)->event('create')->log('created a new lead (L.' . $lead->LeadID . ')');

        return $service->addWatcher($actor, RoleEnum::Admin, $actor, false);
    }

    public function activity(string $description, Carbon $dated, User $actor): array
    {
        return ActivityService::lead($this->lead, $description, $actor, $dated);
    }

    public function addWatcher(User|Team $watcher, RoleEnum $role, User $actor, bool $notify = true): static
    {
        if ($watcher instanceof Team) {
            $leadUser = $this->lead->watchers()->lock('WITH(NOLOCK)')
                ->where('Party', Team::getPrimaryKey())->where('PartyID', $watcher->TeamID)->first();
            if (!$leadUser instanceof LeadUser) {
                $leadUser = new LeadUser();
                $leadUser->fill([
                    'LeadId' => $this->lead->LeadID,
                    'Party' => Team::getPrimaryKey(),
                    'CreatedBy' => $actor->Id,
                    'PartyID' => $watcher->TeamID,
                    'CreatedOn' => now(),
                ]);
            }
            $leadUser->fill([
                'Role' => $role->value,
                'ModifiedBy' => $actor->Id,
                'ModifiedOn' => now(),
            ])->save();

            if ($notify) {
                $users = $watcher->users()->lock('WITH(NOLOCK)')->select(['Email', 'Name'])->lock('WITH(NOLOCK)')->inRandomOrder()->limit(15)->get(['Email', 'Name']);
                $cc = $users->map(function ($user) {
                    return [$user->Name => $user->Email];
                });

                CRMEmailService::createTeam(
                    $watcher,
                    'Notification: Added as Watchers to a Lead',
                    '<p>You have been added as watchers to <a  href="' . route('leads.show', $this->lead->LeadID) . '">' . $this->lead->Name . ' (' . $this->lead->Type->name . ')</a>.</p>
                       <p>As watchers, you will receive updates and notifications about any changes, or progress related to this lead. </p>
                        <p>Please feel free to review the details and provide any necessary input to ensure a smooth pipeline.</p>',
                    SystemHelper::user(),
                    $cc->toArray()
                );
            }

            return $this;
        }


        $leadUser = $this->lead->watchers()->lock('WITH(NOLOCK)')
            ->where('Party', User::getPrimaryKey())->where('PartyID', $watcher->Id)->first();

        if (!$leadUser instanceof LeadUser) {
            $leadUser = new LeadUser();
            $leadUser->fill([
                'LeadId' => $this->lead->LeadID,
                'Party' => User::getPrimaryKey(),
                'CreatedBy' => $actor->Id,
                'PartyID' => $watcher->Id,
                'CreatedOn' => now(),
            ]);
        }
        $leadUser->fill([
            'Role' => $role->value,
            'ModifiedBy' => $actor->Id,
            'ModifiedOn' => now(),
        ])->save();

        if ($notify) {
            CRMEmailService::createUser(
                user: $watcher,
                subject: 'Notification: Added as a Watcher to a Lead ',
                body: '<p>You have been added as watcher to <a  href="' . route('leads.show', $this->lead->LeadID) . '">' . $this->lead->Name . ' (' . $this->lead->Type->name . ')</a>.</p>
                       <p>As watchers, you will receive updates and notifications about any changes, or progress related to this lead. </p>
                        <p>Please feel free to review the details and provide any necessary input to ensure a smooth pipeline.</p>',
                actor: SystemHelper::user()
            );
        }
        return $this;
    }

    public function assign(User $assignee, User $actor): static
    {
        $action = ($this->lead->RelationshipManagerID === $this->lead->CreatedBy) ? 'assigned' : 'reassigned';

        $this->lead->watchers()->lock('WITH(NOLOCK)')->where('t_LeadUsers.Party', User::getPrimaryKey())
            ->where('t_LeadUsers.PartyID', $this->lead->RelationshipManagerID)->update([
                'Role' => ($this->lead->RelationshipManagerID === $this->lead->CreatedBy) ? RoleEnum::Write->value : RoleEnum::Read->value,
                'ModifiedBy' => $actor->Id,
            ]);

        $this->addWatcher($assignee, RoleEnum::Admin, $actor, false);


        $this->lead->lock('WITH(NOLOCK)')->update([
            'RelationshipManagerID' => $assignee->Id,
        ]);

        $service = new UserService($assignee);

        $service->sendMessage(
            'You have been ' . $action . ' to a new lead (Name: ' . $this->lead->Name . ' ' . $this->lead->OtherNames . ', Phone: ' . $this->lead->Phone . '). Please review the lead details as soon as possible.',
            SystemHelper::user()
        )->sendEmail(
            subject: 'New Lead Assignment Notification',
            body: '<p>You have been ' . $action . ' a lead:</p>
             <p><strong>Name:</strong> ' . $this->lead->Name . ' ' . $this->lead->OtherNames . '</p>
             <p><strong>Phone:</strong> ' . $this->lead->Phone . '</p>
             <p><strong>Email:</strong> ' . $this->lead->Email . '</p>
             <p><strong>Type:</strong> ' . $this->lead->Type->name . '</p>
             <p>You can view the <a href="' . route('leads.show', $this->lead->LeadID) . '">lead details here</a> at your earliest convenience.</p>
             <p><em>Please do not reply to this email.</em></p>',
        );

        return $this->sendMessage(
            'Hello ' . $this->lead->Name . ', your account has been ' . $action . ' to ' . $assignee->Name . '. They will contact you soon. Thank you.',
            $actor,
            'account ' . $action . ' to ' . $assignee->UserID
        );
    }

    public function sendEmail(string $subject, string $body, User $actor): static
    {
        $email = $this->getEmail();
        if (is_null($email)) {
            return $this;
        }

        CrmEmailService::createLead($this->lead, $email, $subject, $body, $actor);
        return $this;
    }

    public function getEmail(): ?string
    {
        if (!empty($this->lead->Email) && (filter_var($this->lead->Email, FILTER_VALIDATE_EMAIL))) {
            return $this->lead->Email;
        }
        return null;
    }

    public function sendMessage(string $message, User $actor, string $description = null, bool $immediate = false, BulkNotification $bulkNotification = null): static
    {
        $service = SMSService::createLead($this->lead, $message, $actor);
        if (is_string($description) && !empty($description)) {
            $service->addActivity($service->sms->CreatedOn, $description);
        }
        if ($bulkNotification instanceof BulkNotification) {
            $service->setBulk($bulkNotification);
        }
        $service->send($immediate);
        return $this;
    }

    public static function individual(
        string     $name, string $other_names, string $email, string $phone, string $job_title, Carbon $last_contact,
        GenderEnum $gender, User $manager, User $actor, Locality $location, CodeDetail $industry = null,
        CodeDetail $source = null, CodeDetail $customerType = null, string $notes = ''
    ): self
    {
        $service = self::_create($name, $other_names, $email, $phone, '', $job_title, $last_contact, LeadTypeEnum::Individual, $gender, $actor, $notes, $location, $industry, $source, $customerType);
        if ($manager->Id !== $actor->Id) {
            return $service->assign($manager, $actor);
        }
        return $service;
    }

    public function won(User $actor): array
    {
        $this->lead->forceFill([
            'Status' => LeadStatusEnum::Won->value,
        ])->save();

        activity()->causedBy($actor)->performedOn($this->lead)->event('win')->log('Marked lead  (L' . $this->lead->LeadID . ')  as won.');

        return $this->activity($this->lead->Name . ' marked as won by ' . $actor->UserID, now(), $actor);
    }

    /**
     * @throws ErroredException
     */
    public function deleteWatcher(LeadUser $leadUser, User $actor): static
    {
        if ($this->lead->LeadID !== $leadUser->LeadId) {
            throw new ErroredException('This lead does not belong to you.');
        }

        if (($leadUser->PartyID === $this->lead->CreatedBy) && ($leadUser->Party === User::getPrimaryKey())) {//check creator
            throw new ErroredException('cannot remove creator.');
        }

        if (($leadUser->PartyID === $this->lead->RelationshipManagerID) && ($leadUser->Party === User::getPrimaryKey())) {//check assigned
            throw new ErroredException('cannot remove relationship manager.');
        }

        $service = new PartyService($leadUser->party);
        activity()->causedBy($actor)->performedOn($this->lead)->event('delete')->log('Removed ' . $service->getName() . ' as a lead (L' . $this->lead->LeadID . ') watcher.');

        $leadUser->forceFill([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id,
        ])->save();

        $service->sendEmail(
            'Notification: Removed as Watchers from Lead ' . $this->lead->Name,
            '<p>You have been removed as watchers from Lead ' . $this->lead->Name . '. As a result, you will no longer receive updates or notifications related to this lead.</p>
                <p>Thank you for your continued support and collaboration.</p>'
        );
        return $this;
    }
}
