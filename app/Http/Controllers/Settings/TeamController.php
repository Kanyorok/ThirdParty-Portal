<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except(['show']);
        $this->authorizeResource(Team::class);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(): View|JsonResponse
    {
        return Datatables::of(Team::query()->select('*')->withCount('users'))->addIndexColumn()
            ->addColumn('action', function (Team $team) {
                return '<a  href="' . route('teams.show', [$team->TeamID]) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> details</button>';
            })->editColumn('users_count', function ($team) {
                return number_format($team->users_count ?? 0);
            })->editColumn('Notes', function (Team $team) {
                return Str::limit($team->Notes ?? '', 50);
            })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                                                                                                   'dbl_click_url' => function (Team $team) {
                                                                                                       return route('teams.show', [$team->TeamID]);
                                                                                                   },
                                                                                                  ])->rawColumns(['action'])->make();

    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
                                    'TeamName' => [
                                                      'required',
                                                      'string',
                                                      'max:250',
                                                      'min:5',
                                                      Rule::unique('t_Teams', 'Name'),
                                                     ],
                                    'TeamEmail' => [
                                                      'required',
                                                      'string',
                                                      'email:rfc,dns',
                                                      'max:200',
                                                     ],
                                    'TeamLead' => [
                                                      'nullable',
                                                      'string',
                                                     ],
                                    'TeamMembers' => [
                                                      'nullable',
                                                      'array',
                                                      'max:100',
                                                     ],
                                    'TeamNotes' => [
                                                      'nullable',
                                                      'max:50000',
                                                     ],
                                   ]);
        $userIds = (is_array($data['TeamMembers'])) ? $data['TeamMembers'] : [];

        $userID = null;
        if (! empty($data['TeamLead'])) {
            $user = User::query()->where('UserID', $data['TeamLead'])->first();
            if (! $user instanceof User) {
                throw ValidationException::withMessages(['TeamLead' => 'user selected may be invalid']);
            }

            $userID = $user->Id;
            $userIds[] = $user->UserID;
        }


        $actor = $request->user();
        $dated = now();

        try {
            DB::transaction(static function () use ($dated, $userIds, $userID, $actor, $data) {
                $team = Team::create([
                                      'Name' => $data['TeamName'],
                                      'Email' => $data['TeamEmail'],
                                      'Notes' => $data['TeamNotes'],
                                      'UserId' => $userID,
                                      'CreatedBy' => $actor->Id,
                                      'ModifiedBy' => $actor->Id,
                                     ]);

                $users = User::query()->whereIn('t_Users.UserID', $userIds)->whereNotIn('t_Users.UserID', $team->users()->select('t_Users.UserID'))->get(['Id', 'UserID']);
                //check if users are valid
                $data = collect([]);
                foreach ($users as $user) {
                    $data->add([
                                'TeamId' => $team->TeamID,
                                'UserId' => $user->Id,
                                'CreatedBy' => $actor->Id,
                                'ModifiedBy' => $actor->Id,
                                'CreatedOn' => $dated,
                                'ModifiedOn' => $dated,
                               ]);
                }

                if ($data->count() > 0) {
                    DB::table('t_TeamUser')->insert($data->toArray());
                }
                activity()->causedBy($actor)->performedOn($team->refresh())->event('create')->log('Created a new team ' . $team->Name);
            });
        } catch (Exception $e) {
            Log::error('Error updating branch failed: ' . $e->getMessage());

            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('team created');
    }

    /**
     * Display the specified resource.
     * @throws Exception
     */
    public function show(Team $team): View
    {
        return view('settings.teams.show', compact('team'));
    }

    /**
     * Update the specified resource in storage.
     * @throws ValidationException
     */
    public function update(Request $request, Team $team): JsonResponse
    {
        $data = $request->validate([
                                    'Name' => [
                                                    'required',
                                                    'string',
                                                    'max:250',
                                                    'min:5',
                                                    Rule::unique('t_Teams', 'Name')->whereNotIn('TeamID', [$team->TeamID]),
                                                   ],
                                    'team_lead' => ['nullable'],//Rule::exists('t_Users','UserID')
                                    'Email' => [
                                                    'required',
                                                    'string',
                                                    'email:rfc,dns',
                                                    'max:200',
                                                   ],
                                    'Notes' => [
                                                    'nullable',
                                                    'max:50000',
                                                   ],
                                   ]);

        $userID = null;
        if (! empty($data['team_lead'])) {
            $user = $team->users()->where('t_Users.UserID', $data['team_lead'])->first();
            if (! $user instanceof User) {
                throw ValidationException::withMessages(['team_lead' => 'user not a member of team']);
            }
            $userID = $user->Id;
        }
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($userID, $actor, $data, $team) {
                $team->fill([
                             'Name' => $data['Name'],
                             'UserId' => $userID,
                             'Email' => $data['Email'],
                             'Notes' => $data['Notes'],
                             'ModifiedBy' => $actor->Id,
                            ])->save();

                activity()->causedBy($actor)->performedOn($team)->event('update')->log('updated team ' . $team->Name);
            });
        } catch (Exception $e) {
            Log::error('Error updating team failed: ' . $e->getMessage());

            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('team updated', route('teams.show', $team->TeamID));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Team $team): JsonResponse
    {
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($actor, $team) {
                $team->forceFill([
                                  'DeletedOn' => now(),
                                  'DeletedBy' => $actor->Id,
                                 ])->save(['timestamps' => false]);

                activity()->causedBy($actor)->performedOn($team)->event('delete')->log('deleted team ' . $team->Name);
            });
        } catch (Exception $e) {
            Log::error('Error deleting team failed: ' . $e->getMessage());

            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('team deleted', route('settings.users'));
    }
}
