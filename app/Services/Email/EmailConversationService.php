<?php

namespace App\Services\Email;

use App\Enums\Core\RoleEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\EmailConversation;
use App\Models\EmailConversationUser;
use App\Models\LeadUser;
use App\Models\Team;
use App\Models\User;
use App\Services\CRMEmailService;
use App\Services\PartyService;

class EmailConversationService
{
    public function __construct(public EmailConversation $emailConversation)
    {
    }

    /**
     * @throws ErroredException
     */
    public function deleteWatcher(EmailConversationUser $conversationUser, User $actor): static
    {
        if ($this->emailConversation->Id !== $conversationUser->EmailConversationId) {
            throw new ErroredException('This conversation does not belong to you.');
        }


        $service = new PartyService($conversationUser->party);
        activity()->causedBy($actor)->performedOn($this->emailConversation)->event('delete')->log('Retracted ' . $service->getName() . ' permission for email conversation (' . $this->emailConversation->Id . ').');

        $conversationUser->forceFill([
                                      'DeletedOn' => now(),
                                      'DeletedBy' => $actor->Id,
                                     ])->save();

        $service->sendEmail(
            'Notification: Removed  from email Conversation ' . $this->emailConversation->Id,
            '<p>You have been removed from email conversation ' . $this->emailConversation->Id . '. As a result, you will no longer receive updates or notifications related to this email conversation.</p>
                <p>Thank you for your continued support and collaboration.</p>'
        );
        return $this;
    }

    public function addWatcher(User|Team $watcher, RoleEnum $role, User $actor, bool $notify = true): static
    {
        if ($watcher instanceof Team) {
            $conversationUser = $this->emailConversation->watchers()->lock('WITH(NOLOCK)')
                ->where('Party', Team::getPrimaryKey())->where('PartyID', $watcher->TeamID)->first();
            if (!$conversationUser instanceof EmailConversationUser) {
                $conversationUser = new EmailConversationUser();
                $conversationUser->fill([
                                         'EmailConversationId' => $this->emailConversation->Id,
                                         'Party'               => Team::getPrimaryKey(),
                                         'CreatedBy'           => $actor->Id,
                                         'PartyID'             => $watcher->TeamID,
                                         'CreatedOn'           => now(),
                                        ]);
            }
            $conversationUser->fill([
                                     'Role'       => $role->value,
                                     'ModifiedBy' => $actor->Id,
                                     'ModifiedOn' => now(),
                                    ])->save();

            if ($notify) {
                $users = $watcher->users()->lock('WITH(NOLOCK)')->select(['Email', 'Name'])->lock('WITH(NOLOCK)')->inRandomOrder()->limit(20)->get(['Email', 'Name']);
                $cc = $users->map(function ($user) {
                    return [$user->Name => $user->Email];
                });

                CRMEmailService::createTeam(
                    $watcher,
                    'Notification: Added to an  email Conversation',
                    '<p>You have been added to email conversation  <a  href="' . route('email-conversations.show', $this->emailConversation->Id) . '">' . $this->emailConversation->Id . '</a>.</p>
                       <p>Please feel free to join the conversation. You can read the email thread, view attachments and reply to emails in this conversation </p>
                        <p>if this was a mistake, contact system admin.</p>',
                    SystemHelper::user(),
                    $cc->toArray()
                );
            }

            return $this;
        }


        $conversationUser = $this->emailConversation->watchers()->lock('WITH(NOLOCK)')
            ->where('Party', User::getPrimaryKey())->where('PartyID', $watcher->Id)->first();

        if (!$conversationUser instanceof EmailConversationUser) {
            $conversationUser = new EmailConversationUser();
            $conversationUser->fill([
                                     'EmailConversationId' => $this->emailConversation->Id,
                                     'Party'               => User::getPrimaryKey(),
                                     'CreatedBy'           => $actor->Id,
                                     'PartyID'             => $watcher->Id,
                                     'CreatedOn'           => now(),
                                    ]);
        }
        $conversationUser->fill([
                                 'Role'       => $role->value,
                                 'ModifiedBy' => $actor->Id,
                                 'ModifiedOn' => now(),
                                ])->save();

        if ($notify) {
            CRMEmailService::createUser(
                user: $watcher,
                subject: 'Notification: Added as a Watcher to a Lead ',
                body: '<p>You have been added to email conversation <a  href="' . route('email-conversations.show', $this->emailConversation->Id) . '">' . $this->emailConversation->Id . '</a>.</p>
                       <p>Please feel free to join the conversation. You can read the email thread, view attachments and reply to emails in this conversation </p>
                        <p>if this was a mistake, contact system admin.</p>',
                actor: SystemHelper::user()
            );
        }
        return $this;
    }
}
