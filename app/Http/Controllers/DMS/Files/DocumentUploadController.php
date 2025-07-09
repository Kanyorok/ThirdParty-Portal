<?php

namespace App\Http\Controllers\DMS\Files;

use App\Http\Controllers\Controller;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\DMS\Repository;
use App\Services\DMS\RepositoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DocumentUploadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $actor = $request->user();
        $root = RepositoryService::root();
        $repositories = Repository::query()->where(function (Builder $query) use ($root) {
            $query->where('ParentId', $root->Id)->orWhere('ParentId', null);
        })->orWhereHas('permissions', function (Builder $query) use ($actor) {
            $query->where(function (Builder $query) use ($actor) {
                $query->where('t_SpecialPermissions.PartyID', $actor->Id)->where('t_SpecialPermissions.Party', User::getPrimaryKey());
            })->orWhere(function (Builder $query) use ($actor) {
                $query->where('t_SpecialPermissions.Party', Team::getPrimaryKey())->whereIn('t_SpecialPermissions.PartyID', $actor->teams()->pluck('id')->toArray());
            });
        })->get();
        return view('dms.files.create')->with('repositories', $repositories)->with('root', $root);
    }
}
