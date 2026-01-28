<?php

namespace App\Http\Controllers\CRM\Email;

use App\Enums\EmailStatusEnum;
use App\Enums\EmailTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Communication\EmailConversation;
use App\Services\CRMEmailService;
use App\Services\PartyService;
use App\Services\StaticListsService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use RuntimeException;
use Yajra\DataTables\DataTables;

class EmailConversationController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = EmailConversation::query()->with(['party', 'email'])->withCount(['emails', 'emails as unread_emails_count' => function (Builder $builder) {
                $builder->where('t_Emails.Type', EmailTypeEnum::Incoming)->where('t_Emails.Status', EmailStatusEnum::Unread);
            },
            ]);
            if ($request->searchByType === 'UNREAD') {
                $query->whereHas('emails', function (Builder $builder) {
                    $builder->where('t_Emails.Type', EmailTypeEnum::Incoming)->where('t_Emails.Status', EmailStatusEnum::Unread);
                });
            } elseif ($request->searchByType === 'INBOX') {
                $query->whereHas('emails', function (Builder $builder) {
                    $builder->where('t_Emails.Type', EmailTypeEnum::Incoming)->whereIn('t_Emails.Status', [EmailStatusEnum::Unread, EmailStatusEnum::Read]);
                });
            } else {
                $query = collect();
            }

            return Datatables::of($query)->addIndexColumn()
                ->addColumn('action', function (EmailConversation $conversation) {
                    return '<a href="' . route('email-conversations.show', $conversation->Id) . '" class="btn btn-secondary btn-pill btn-sm"><i class="fas fa-eye"></i></a>';
                })->editColumn('party', function (EmailConversation $conversation) {
                    return (new PartyService($conversation->party))->simplified(
                        true,
                        true,
                        unknown: (new CRMEmailService($conversation->email))->getParty()
                    );
                })->editColumn('ModifiedOn', function (EmailConversation $conversation) {
                    return $conversation->ModifiedOn?->format('M d, Y H:i');
                })->editColumn('emails_count', function ($conversation) {
                    return (int)$conversation->emails_count;
                })->editColumn('email.Subject', function (EmailConversation $conversation) {
                    return $conversation->email?->Subject;
                })->setRowClass(function ($conversation) {
                    if ($conversation->unread_emails_count > 0) {
                        return 'mouse_pointer user-select-none click-email-details fw-bold';
                    }

                    return 'mouse_pointer user-select-none click-email-details';
                })->setRowData([
                    'click_url' => function (EmailConversation $conversation) {
                        return route('email-conversations.summary', [$conversation->Id]);
                    },
                    'summary_title' => 'email summary',
                ])->rawColumns(['party', 'action'])->make();
        }

        return view('crm.emails.conversations.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($conversation_id)
    {
        $conversation = EmailConversation::query()->where('t_EmailsConversations.Id', $conversation_id)->first();
        if (! $conversation instanceof EmailConversation) {
            throw new RuntimeException('Conversation does not exist');
        }

        $StaticLists = StaticListsService::getList([StaticListsService::TicketCategories]);

        return view('crm.emails.conversations.show', compact('conversation'))
            ->with('TicketCategories', $StaticLists->where('CodeID', StaticListsService::TicketCategories))
            ->with('party', $conversation->party);
    }

    /**
     * Display the specified resource.
     */
    public function summary($conversation_id)
    {
        $conversation = EmailConversation::query()->where('t_EmailsConversations.Id', $conversation_id)->first();
        if (! $conversation instanceof EmailConversation) {
            throw new RuntimeException('Conversation does not exist');
        }

        return view('crm.emails.conversations.summary', compact('conversation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmailConversation $emailConversation)
    {
    }

    /**
     * Mark all Emails as read.
     */
    public function update(Request $request, EmailConversation $emailConversation)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailConversation $emailConversation)
    {
    }
}
