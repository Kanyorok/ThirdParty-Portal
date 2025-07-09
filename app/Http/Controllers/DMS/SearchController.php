<?php

namespace App\Http\Controllers\DMS;

use App\Enums\Core\VisibilityEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\DMS\FilesCollection;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\DMS\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $actor = $request->user();

        if ($request->type === 'files') {
            $search = $request->get('q', '');
            $search = (is_string($search)) ? str_replace(['*', '%'], ['', ''], $search) : '';
            $search = trim($search);
            if (empty($search)) {
                return new FilesCollection(collect([]));
            }
            $files = Document::query()->whereHas('current')->where(function (Builder $query) use ($actor) {
                $query->where('t_Documents.Visibility', VisibilityEnum::Public->value)
                    ->orWhereHas('permissions', function (Builder $q) use ($actor) {
                        $q->where(function (Builder $q) use ($actor) {
                            $q->where('Party', User::getPrimaryKey())
                                ->where('PartyID', $actor->Id);
                        })->orWhere(function (Builder $q) use ($actor) {
                            $q->where('Party', Team::getPrimaryKey())
                                ->whereIn('PartyID', $actor->teams()->select('t_Teams.TeamID'));
                        });
                    });
            })->with(['current'])->where(function (Builder $query) use ($search) {
                $query->where('t_Documents.Name', 'LIKE', "%{$search}%")
                    ->orWhereHas('current', function (Builder $q) use ($search) {
                        $q->where('t_DocumentVersions.Name', 'LIKE', "%{$search}%")
                            ->orWhere('t_DocumentVersions.Blob', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('tags', function (Builder $q) use ($search) {
                        $q->where('t_DMSTags.Name', 'LIKE', "%{$search}%");
                    });
            })->limit(10)->get();

            return new FilesCollection($files);
        }//type=files&q

        return $this->errored('Search functionality is not implemented yet.', status: 501);

    }
}
