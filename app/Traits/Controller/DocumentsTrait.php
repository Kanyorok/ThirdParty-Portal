<?php

namespace App\Traits\Controller;

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
    private function documents(Builder|MorphMany|BelongsToMany $query, User $actor, array $with = ['repository', 'current']): JsonResponse
    {
        $with = in_array('current', $with, true) ? $with : array_merge($with, ['current']);

        try {
            return Datatables::of($query->whereHas('current')->user($actor)->with($with)->lock('WITH(NOLOCK)'))->addIndexColumn()
                ->addColumn('action', function (Document $document) {
                    return [
                        'restore' => route('document-trashed.restore', [$document->DocumentId]),
                    ];
                })->editColumn('current.Size', function (Document $document) {
                    if (is_null($document->current)) {
                        return '?';
                    }

                    return Number::fileSize($document->current->Size, 2);
                })->addColumn('Icon', function (Document $document) {
                    return $document->ext()?->getIcon('img');
                })->addColumn('Repository', function (Document $document) use ($with) {
                    if (! in_array('repository', $with, true)) {
                        return 'N/A';
                    }

                    return $document->repository?->Name ?? 'N/A';
                })->editColumn('CreatedOn', function (Document $document) {
                    return $document->CreatedOn?->format('d M, Y H:i');
                })->editColumn('DeletedOn', function (Document $document) {
                    return $document->DeletedOn?->format('d M, Y H:i');
                })->editColumn('Visibility', function (Document $document) {
                    return $document->Visibility->icon();
                    /* })->editColumn('Name', function (Document $document) {
                         return $document->Name;*/
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
