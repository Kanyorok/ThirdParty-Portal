<?php

namespace App\Listeners\DebtCollection;

use App\Enums\Core\ExtensionsEnum;
use App\Events\DebtCollection\BulkNotificationEvent;
use App\Http\Controllers\CRM\Board\BoardNotificationController;
use App\Http\Requests\DebtCollection\LoanQueryRequest;
use App\Models\Board;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\Committee;
use App\Models\Team;
use App\Models\User;
use App\Services\BoardService;
use App\Services\BR\LoanService;
use App\Services\HRM\UserService;
use App\Services\TeamService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class BulkSendListener implements ShouldQueue
{
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
    public function handle(BulkNotificationEvent $event): void
    {
        $view = null;
        switch ($event->bulkNotification->Module) {
            case LoanService::MODULE:
                $this->_loans($event);
                $view = 'debt-collection.notifications.document';
                break;
            case BoardNotificationController::MODULE:
                $this->_board($event);
                $view = 'board.notifications.document';
                break;
            case UserService::MODULE:
                $this->_users($event);
                $view = 'settings.users.notifications.document';
                break;
            case TeamService::MODULE:
                $this->_team($event);
                $view = 'settings.users.notifications.document';
                break;
        }

        $event->bulkNotification->update([
                                          'CompleteOn' => now(),
                                         ]);

        if (is_string($view)) {
            (new UserService($event->bulkNotification->creator))->sendEmail(
                'Bulk Notification: ' . Str::upper($event->bulkNotification->Label),
                body: '<div><p>Dear ' . $event->bulkNotification->creator->Name . ',</p>
                        <p>Please find attached the detailed report for the Bulk SMS Notification titled <strong>' . e(Str::upper($event->bulkNotification->Label)) . '</strong>.</p></div>',
                immediate: null
            )?->addAttachmentContent(
                Content: PDF::loadView($view, ['title' => "Bulk Notification: " . Str::upper($event->bulkNotification->Label), 'notifications' => $event->bulkNotification])->setPaper('a4', 'landscape')->output(),
                MimeType: ExtensionsEnum::Pdf->getMimeType(),
                Name: Str::upper($event->bulkNotification->Label) . '.pdf',
                actor: $event->bulkNotification->creator
            )->send(true);
        }
    }

    protected function _team(BulkNotificationEvent $event): void
    {
        $team = $event->attributes['team'];
        if ($team instanceof Team) {
            $content = Str::replace('#team', $team->Name, $event->bulkNotification->Content);
            foreach ($team->users()->select(['t_Users.Name', 't_Users.Phone', 't_Users.UserID', 't_Users.Id'])->get() as $user) {
                if ($user instanceof User) {
                    (new UserService($user))->sendMessage($content, $event->actor, true, $event->bulkNotification);
                }
            }
        }
    }

    protected function _users(BulkNotificationEvent $event): void
    {
        $content = $event->bulkNotification->Content;
        foreach (User::query()->select(['t_Users.Name', 't_Users.Phone', 't_Users.UserID', 't_Users.Id'])->get() as $user) {
            if ($user instanceof User) {
                (new UserService($user))->sendMessage($content, $event->actor, true, $event->bulkNotification);
            }
        }
    }

    protected function _board(BulkNotificationEvent $event): void
    {
        $committee = $event->attributes['committee'];
        if ($committee instanceof Committee) {
            $content = Str::replace('#committee', $committee->Name, $event->bulkNotification->Content);
            foreach ($committee->members as $member) {
                if ($member instanceof Board) {
                    (new BoardService($member))->sendMessage($content, $event->actor, true, $event->bulkNotification);
                }
            }
        }
    }

    protected function _loans(BulkNotificationEvent $event): void
    {
        $loans = LoanQueryRequest::filterDebts(DebtProduct::query()->where('processDate', $event->dated), $event->attributes)->lock('WITH(NOLOCK)')->get();
        foreach ($loans as $loan) {
            if (!$loan instanceof DebtProduct && !$loan->client instanceof Client) {
                continue;
            }
            (new LoanService($loan))->message($event->bulkNotification->Content, $event->actor, true, $event->bulkNotification);
        }
    }
}
