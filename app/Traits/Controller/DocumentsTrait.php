<?php

namespace App\Traits\Controller;

use App\Enums\Core\VisibilityEnum;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use App\Models\DMS\Document;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Number;
use Yajra\DataTables\DataTables;

trait DocumentsTrait
{
    private function documents(MorphMany|BelongsToMany $query, User $actor, array $with = ['repository']): JsonResponse
    {
        $query->whereHas('current')->where(function (Builder $query) use ($actor) {
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
        })->with(in_array('current', $with, true) ? $with : array_merge($with, ['current']));

        try {
            return Datatables::of($query->lock('WITH(NOLOCK)'))->addIndexColumn()
                ->editColumn('current.Size', function (Document $document) {
                    return Number::fileSize($document->current->Size, 2);
                })->addColumn('Icon', function (Document $document) {
                    return $document->ext()?->getIcon('img');
                })->addColumn('Repository', function (Document $document) use ($with) {
                    if (!in_array('repository', $with, true)) {
                        return 'N/A';
                    }
                    return $document->repository?->Name ?? 'N/A';
                })->editColumn('CreatedOn', function (Document $document) {
                    return $document->CreatedOn?->format('d M, Y H:i');
                })->editColumn('Visibility', function (Document $document) {
                    return $document->Visibility->icon();
                })->editColumn('Name', function (Document $document) {
                    return $document->Name;
                })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                    'dbl_click_url' => function (Document $document) {
                        return route('files.show', [$document->repository->RepositoryId, $document->DocumentId]);
                    },
                ])->rawColumns(['Icon', 'Visibility'])->make();
        } catch (Exception $e) {
            return $this->errored('an error occurred fetching related documents');
        }
    }

}
