<?php

namespace App\Http\Controllers\Settings;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\Auth\Team;
use App\Models\Auth\TeamUser;
use App\Models\Auth\User;
use App\Services\HRM\UserService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeamUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Team $team): JsonResponse
    {
        $this->authorize('view', $team);
        return UserService::dt($team->users(), ['photo'], ['pivot_date', 'action_team' => $team->TeamID]);
    }

    /**
     * Store a newly created resource in storage.
     * @throws AuthorizationException
     */
    public function store(Request $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team);
        $request->validate([
                            'users' => [
                                        'required',
                                        'array',
                                        'min:1',
                                        'max:200',
                                       ],
                           ]);

        $actor = $request->user();
        $dated = now();
        try {
            DB::transaction(static function () use ($dated, $team, $request, $actor) {
                $users = User::query()->whereIn('t_Users.UserID', $request->get('users'))->whereNotIn('t_Users.UserID', $team->users()->select('t_Users.UserID'))->get(['Id', 'UserID']);
                //check if users are valid
                $data = collect([]);
                foreach ($users as $user) {
                    $data->add([
                                'TeamId'     => $team->TeamID,
                                'UserId'     => $user->Id,
                                'CreatedBy'  => $actor->Id,
                                'ModifiedBy' => $actor->Id,
                                'CreatedOn'  => $dated,
                                'ModifiedOn' => $dated,
                               ]);
                }

                if ($data->count() > 0) {
                    DB::table('t_TeamUser')->insert($data->toArray());
                }

                activity()->causedBy($actor)->performedOn($team)->event('updated')->log('added users : ' . implode(',', $users->pluck('UserID')->toArray()));
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error adding users to list ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('added successfully');
    }


    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy(Request $request, Team $team, User $user): JsonResponse
    {
        $this->authorize('update', $team);
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($team, $user, $actor) {
                TeamUser::query()->where('TeamId', $team->TeamID)->where('UserId', $user->Id)->delete();

                if ($team->UserId === $user->Id) {
                    $team->update(['UserId' => null]);
                }
                activity()->causedBy($actor)->performedOn($user)->event('delete')->log('Removed ' . $user->UserID . ' from ' . $team->Name);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error removing user from team ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }


        return $this->succeeded('removed successfully');
    }
}
