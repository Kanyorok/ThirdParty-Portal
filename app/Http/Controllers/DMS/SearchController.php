<?php

namespace App\Http\Controllers\DMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\DMS\FilesCollection;
use App\Http\Resources\DMS\RepositoryCollection;
use App\Models\DMS\Document;
use App\Models\DMS\Repository;
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
        $search = $request->get('q', '');
        $search = (is_string($search)) ? str_replace(['*', '%'], ['', ''], $search) : '';
        $search = trim($search);

        if ($request->type === 'files') {
            if (empty($search)) {
                return new FilesCollection(collect([]));
            }
            $files = Document::query()->user($actor)->whereHas('current')->where(function (Builder $query) use ($search) {
                $query->where('t_Documents.Name', 'LIKE', "%{$search}%")
                    ->orWhereHas('current', function (Builder $q) use ($search) {
                        $q->where('t_DocumentVersions.Name', 'LIKE', "%{$search}%")
                            ->orWhere('t_DocumentVersions.Blob', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('tags', function (Builder $q) use ($search) {
                        $q->where('t_DMSTags.Name', 'LIKE', "%{$search}%");
                    });
            })->with(['current', 'repository'])->limit(5)->get();

            return (new FilesCollection($files))->setMinified(true);
        }
        if ($request->type === 'repositories') {
            if (empty($search)) {
                return new RepositoryCollection(collect([]));
            }

            $repos = Repository::query()->user($actor)->where(function (Builder $query) use ($search) {
                $query->where('t_Repositories.Name', 'LIKE', "%{$search}%")
                    ->Orwhere('t_Repositories.Description', 'LIKE', "%{$search}%");
            })->limit(5)->get();

            return (new RepositoryCollection($repos))->setMinified(true);
        }


        return $this->errored('Search functionality is not implemented yet.', status: 501);
    }
}
