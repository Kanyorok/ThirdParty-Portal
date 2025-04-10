<?php

namespace App\Http\Controllers\CRM\Leads;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Base\SharePartyRequest;
use App\Http\Requests\Lead\LeadAssignRequest;
use App\Models\Lead;
use App\Models\LeadUser;
use App\Models\User;
use App\Services\LeadService;
use App\Services\PartyService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class LeadUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * @throws Exception
     */
    public function index(Request $request, Lead $lead): JsonResponse
    {
        $this->authorize('view', $lead);
        return Datatables::of($lead->watchers()->with('party')->whereNull(['t_LeadUsers.DeletedOn', 't_LeadUsers.DeletedBy'])->select('*'))->addIndexColumn()
            ->addColumn('action', function (LeadUser $user) use ($lead, $request) {
                if ($request->user()->can('delete', $lead)) {
                    return '<button type="button" data-click_url="' . route('lead-watchers.destroy', [$lead->LeadID, $user->Id]) . '" data-info="' . (new PartyService($user->party))->getName() . '"
                        class="btn btn-danger btn-sm lead-watchers-trash"><i class="fas fa-trash"></i></button>';
                }
                return '...';
            })->editColumn('party', function (LeadUser $user) {
                return (new PartyService($user->party))->getDTRow();
            })->editColumn('CreatedOn', function (LeadUser $user) {
                return $user->CreatedOn?->format('d M, Y H:i');
            })->editColumn('Role', function (LeadUser $user) {
                return $user->Role->name;
            })->rawColumns(['action', 'party'])->make();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SharePartyRequest $request, Lead $lead): JsonResponse
    {
        $this->authorize('share', $lead);
        $assignee = $request->getParty();
        $role = $request->getRole();
        $actor = $request->user();
        try {
            DB::transaction(static function () use ($actor, $lead, $assignee, $role) {
                (new LeadService($lead))->addWatcher($assignee, $role, $actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (\Throwable|Exception $e) {
            Log::error('Error add lead watcher failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }
        return $this->succeeded('shared successfully');
    }


    public function reassign(LeadAssignRequest $request, Lead $lead): JsonResponse
    {
        $this->authorize('reassign', $lead);
        $assignee = $request->getAssignee();
        $actor = $request->user();

        if ($assignee->Id === $lead->RelationshipManagerID) {
            return $this->errored('lead already assigned to ' . $assignee->UserID);
        }
        try {
            DB::transaction(static function () use ($actor, $lead, $assignee) {
                (new LeadService($lead))->assign($assignee, $actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (\Throwable|Exception $e) {
            Log::error('Error  re assigned lead failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }
        return $this->succeeded('lead re assigned successfully, redirecting', route('leads.show', $lead->LeadID));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Lead $lead, $leadUser_Id): JsonResponse
    {
        $this->authorize('share', $lead);
        $actor = $request->user();
        $leadUser = $lead->watchers()->where('t_LeadUsers.Id', $leadUser_Id)->first();

        if ($leadUser instanceof LeadUser) {
            if (($leadUser->Party === User::getPrimaryKey()) && ((int)$leadUser->PartyID === (int)$lead->RelationshipManagerID)) {
                return $this->errored('cannot remove relationship officer.');
            }

            if (($leadUser->Party === User::getPrimaryKey()) && ((int)$leadUser->PartyID === (int)$lead->CreatedBy)) {
                return $this->errored('cannot remove introducer.');
            }

            try {
                DB::transaction(static function () use ($leadUser, $actor, $lead) {
                    (new LeadService($lead))->deleteWatcher($leadUser, $actor);
                });
            } catch (ErroredException $e) {
                return $e->toJson();
            } catch (\Throwable|Exception $e) {
                Log::error('Error remove lead watcher failed: ' . $e->getMessage());
                return $this->errored('unexpected error, try again later');
            }
        }

        return $this->succeeded('watcher removed successfully');
    }
}
