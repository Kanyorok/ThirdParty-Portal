<?php

namespace App\Http\Controllers\Settings;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserSessionController extends Controller
{
    /**
     * Display users with multiple active sessions and their session details.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionEnum::UsersSessions), 403);

        $connection = config('session.connection');
        $table = config('session.table', 'sessions');

        // Find users with >1 sessions
        $duplicates = DB::connection($connection)
            ->table($table)
            ->select('user_id', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $byUser = [];
        if ($duplicates->count() > 0) {
            $userIds = $duplicates->pluck('user_id')->filter()->unique()->values();
            $users = DB::table('t_Users')->whereIn('Id', $userIds)->get(['Id', 'UserID', 'Name', 'Email', 'current_session_id']);
            $sessionRows = DB::connection($connection)
                ->table($table)
                ->whereIn('user_id', $userIds)
                ->orderByDesc('last_activity')
                ->get(['id', 'user_id', 'ip_address', 'user_agent', 'last_activity']);

            foreach ($users as $u) {
                $byUser[$u->Id] = [
                    'user' => $u,
                    'total' => (int)($duplicates->firstWhere('user_id', $u->Id)->cnt ?? 0),
                    'sessions' => $sessionRows->where('user_id', $u->Id)->values(),
                ];
            }
        }

        return view('settings.user-sessions.index', [
            'groups' => $byUser,
            'sessionTable' => $table,
        ]);
    }

    /**
     * Revoke a specific session id.
     */
    public function revoke(Request $request, string $id): RedirectResponse
    {
        abort_unless($request->user()?->can(PermissionEnum::UsersSessions), 403);

        $connection = config('session.connection');
        $table = config('session.table', 'sessions');

        $row = DB::connection($connection)->table($table)->where('id', $id)->first(['id', 'user_id']);
        if ($row) {
            DB::connection($connection)->table($table)->where('id', $id)->delete();

            if (! empty($row->user_id)) {
                // If the user's current_session_id equals the revoked id, clear it
                DB::table('t_Users')->where('Id', $row->user_id)->where('current_session_id', $id)->update(['current_session_id' => null]);
            }
        }

        return back()->with('status', 'Session revoked.');
    }

    /**
     * Revoke all sessions for a user, optionally keeping one.
     */
    public function revokeOthers(Request $request, int $userId): RedirectResponse
    {
        abort_unless($request->user()?->can(PermissionEnum::UsersSessions), 403);

        $keepId = $request->string('keep_id')->toString();
        $connection = config('session.connection');
        $table = config('session.table', 'sessions');

        $query = DB::connection($connection)->table($table)->where('user_id', '=', $userId);
        if (! empty($keepId)) {
            $query->where('id', '!=', $keepId);
        }
        $query->delete();

        // If we wiped all sessions, clear current_session_id
        if (empty($keepId)) {
            DB::table('t_Users')->where('Id', $userId)->update(['current_session_id' => null]);
        }

        return back()->with('status', 'User sessions updated.');
    }
}
