<?php

namespace App\Traits\Controller;

use App\Enums\Core\VisibilityEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\DMS\DMSTags;
use App\Models\DMS\Document;
use App\Services\DMS\TagService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;
use Yajra\DataTables\DataTables;

trait DMSTagTrait
{
    public function dt(Builder|BelongsToMany $query, User $actor, array $with = [], array $withCount = ['documents'], array $extra = []): JsonResponse
    {
        $query->where(function ($q) use ($actor) {
            $q->where('Visibility', VisibilityEnum::Public->value)
                ->orWhere(function ($subQuery) use ($actor) {
                    $subQuery->where('Visibility', VisibilityEnum::Private->value)
                        ->where('CreatedBy', $actor->Id);
                });
        });
        if (!empty($with)) {
            $query->with($with);
        }
        if (!empty($withCount)) {
            $query->withCount($withCount);
        }

        try {
            return Datatables::of($query->lock('WITH(NOLOCK)'))->addIndexColumn()
                ->addColumn('action', function (DMSTags $tag) use ($extra) {
                    return '<a  href="' . route('file-tags.show', [$tag->TagID]) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> details</button>';
                })->editColumn('documents_count', function (DMSTags $tag) use ($withCount) {
                    return (in_array('documents_count', $withCount, true)) ? number_format($tag->documents_count) : 0;
                })->addColumn('Description', function (DMSTags $tag) {
                    return Str::of($tag->Description)->limit(100);
                })->rawColumns(['action'])->make();
        } catch (Exception $e) {
            return $this->errored('fetching data failed, try again later');
        }
    }


    /**
     * @throws ErroredException
     */
    public function new(string $Name, string $Description, User $actor, VisibilityEnum $visibility, Document|null $document = null): DMSTags
    {
        try {
            return DB::transaction(static function () use ($visibility, $Name, $actor, $Description, $document) {
                $service = TagService::create($Name, $Description, $actor, $visibility);

                if ($document instanceof Document) {
                    $service->attach($document, $actor);
                }
                return $service->tag;
            });
        } catch (ErroredException $e) {
            throw new ErroredException($e->getMessage());
        } catch (Exception|Throwable $e) {
            Log::error('Error create Document Tag :  ');
            Log::error($e);
            throw new ErroredException('unexpected error, try again later');
        }
    }
}
