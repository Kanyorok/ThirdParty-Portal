<?php

namespace App\Http\Controllers\Settings\Users;

use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UserSelectController extends Controller
{
    protected const int LIMIT = 10;

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {

        if (!$request->has('q')) {
            return $this->_response($request, collect());
        }

        $search = str($request->q)->squish();
        if (empty($search)) {
            return $this->_response($request, collect());
        }

        if (Str::startsWith($search, 't:') && $request->has('with_teams')) {
            $search = explode(':', $search);
            array_shift($search);
            return $this->_response($request, $this->_searchTeams(implode(':', $search)));
        }

        if (Str::startsWith($search, 'u:')) {
            $search = explode(':', $search);
            array_shift($search);
            return $this->_response($request, $this->_searchUsers($request, implode(':', $search)));
        }

        $data = $this->_searchUsers($request, $search);

        if ($request->has('with_teams')) {
            $data = $data->concat($this->_searchTeams($search)->toArray());
        }

        return $this->_response($request, $data);
    }

    protected function _response(Request $request, Collection $data): JsonResponse
    {
        if ($request->has('add_none')) {
            $data->add([
                        'UserID' => SystemHelper::ID,
                        'Name'   => 'None - Unassigned',
                        'type'   => 'user',
                       ]);
        }

        if ($request->has('add_all')) {
            $data->add([
                        'UserID' => UserService::MODULE,
                        'Name'   => 'All Users',
                        'type'   => 'user',
                       ]);
        }
        return response()->json($data->toarray());
    }

    private function _searchTeams(string $search): Collection
    {
        $teams = Team::query()->where(function (Builder $query) use ($search) {
            $query->where('t_Teams.Name', 'LIKE', "%$search%")
                ->orWhere('t_Teams.Email', 'LIKE', "%$search%")
                ->orWhere('t_Teams.Notes', 'LIKE', "%$search%");
        })->select('t_Teams.TeamID', 't_Teams.Name')->lock('WITH(NOLOCK)')->limit(self::LIMIT)->get(['TeamID', 'Name']);
        return $teams->map(function ($team) {
            return [
                    'UserID' => 't#' . $team->TeamID,
                    'Name'   => $team->Name . ' (team)',
                    'type'   => 'team',
                   ];
        });
    }

    private function _searchUsers(Request $request, string $search): Collection
    {
        $query = User::query()->where('t_Users.UserID', '!=', SystemHelper::ID);

        if ($request->has('filter_team')) {
            $query->whereNotIn('Id', TeamUser::query()->where('t_TeamUser.TeamId', $request->get('filter_team'))->select('t_TeamUser.UserId'));
        } elseif ($request->has('filter_team_only')) {
            $query->whereIn('Id', TeamUser::query()->where('t_TeamUser.TeamId', $request->get('filter_team_only'))->select('t_TeamUser.UserId'));
        } elseif ($request->has('filter_current')) {
            $query->where('Id', '!=', $request->user()->Id);
        }

        $users = $query->where(function (Builder $query) use ($search) {
            $query->where('t_Users.UserID', 'LIKE', "%$search%")
                ->orWhere('t_Users.Name', 'LIKE', "%$search%")
                ->orWhere('t_Users.Email', 'LIKE', "%$search%")
                ->orWhere('t_Users.Phone', 'LIKE', "%$search%");
        })->lock('WITH(NOLOCK)')->select(['UserID', 'Name'])->lock('WITH(NOLOCK)')->limit(self::LIMIT)->get(['UserID', 'Name']);
        $append = ($request->has('with_teams')) ? ' (user)' : '';
        return $users->map(function ($user) use ($append) {
            return [
                    'UserID' => $user->UserID,
                    'Name'   => $user->Name . ' - ' . $user->UserID . $append,
                    'type'   => 'user',
                   ];
        });
    }
}
