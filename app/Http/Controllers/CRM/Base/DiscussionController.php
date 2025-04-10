<?php

namespace App\Http\Controllers\CRM\Base;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\Discussion;
use App\Models\Meeting;
use App\Services\PartyService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class DiscussionController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->only('show');
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return Datatables::of(Discussion::query()->lock('WITH(NOLOCK)')->with(['party'])->select('*'))->addIndexColumn()
                ->addColumn('action', function (Discussion $discussion) {
                    return '<button type="button" data-click_url="' . route('discussions.show', [$discussion->DiscussionID]) . '" data-summary_title="discussion summary" class="btn btn-info btn-sm click-summary-data"><i class="fas fa-eye"></i> details</button>';
                })->editColumn('party', function (Discussion $discussion) {
                    return (new PartyService($discussion->party))->getDTRow();
                })->editColumn('SourceType', function (Discussion $discussion) {
                    return match ($discussion->SourceType) {
                        Call::getPrimaryKey() => 'Call',
                        Meeting::getPrimaryKey() => 'Meeting',
                        default => '? ?',
                    };
                })->editColumn('CreatedOn', function (Discussion $discussion) {
                    return $discussion->CreatedOn?->format('F d, Y h:i A');
                })->editColumn('Discussion', function (Discussion $discussion) {
                    return Str::limit($discussion->Discussion);
                })->rawColumns(['action', 'party'])->make();
        }

        return view('crm.base.discussions.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Discussion $discussion): View
    {
        //  dd($discussion->discussionUser()->select('UserID')->pluck('UserID')->toArray());
        /*$users = User::query()
            ->whereIn('Id', $discussion->discussionUser()->select('UserID'))
            ->lock('WITH(NOLOCK)')->get();*/

        return view('crm.base.discussions.show')->with('clientDiscussion', $discussion)
            ->with('users', $discussion->users)
            ->with('party', $discussion->party);
    }
}
