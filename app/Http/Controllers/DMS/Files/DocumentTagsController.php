<?php

namespace App\Http\Controllers\DMS\Files;

use App\Enums\Core\VisibilityEnum;
use App\Http\Controllers\Controller;
use App\Models\DMS\DMSTags;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentTags;
use App\Services\DMS\DocumentService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class DocumentTagsController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Document $document): JsonResponse
    {
        $this->authorize('view', $document);
        $user = $request->user();
        $data = $request->validate([
            "DocumentTags" => "required|array",
            "DocumentTags.*" => "required|exists:t_DMSTags,TagID",
        ]);


        try {
            return DB::transaction(function () use ($document, $data, $user) {
                $tags = DMSTags::whereIn('TagID', $data['DocumentTags'])->where(static function (Builder $query) use ($user) {
                    $query->where('Visibility', VisibilityEnum::Public->value)
                        ->orWhere(function (Builder $query) use ($user) {
                            $query->where('Visibility', VisibilityEnum::Private->value)
                                ->where('t_DMSTags.CreatedBy', $user->Id);
                        });
                })->select('t_DMSTags.Id')->pluck('Id')->toArray();

                /*$userTags = DMSTags::where(static function (Builder $query) use ($user) {
                    $query->where('Visibility', VisibilityEnum::Public->value)
                        ->orWhere(function (Builder $query) use ($user) {
                            $query->where('Visibility', VisibilityEnum::Private->value)
                                ->where('t_DMSTags.CreatedBy', $user->Id);
                        });
                })->select('t_DMSTags.Id')->pluck('Id')->toArray();*/

                $documentTags = (new DocumentService($document))->tags($user)->select('t_DMSTags.Id')->pluck('Id')->toArray();

                $result = array_diff($documentTags, $tags);
                if (count($result)) {
                    DB::table('t_DocumentTags')->where('DocId', $document->Id)->whereIn('TagId', $result)->update([
                        'DeletedBy' => $user->Id,
                        'DeletedOn' => now(),
                    ]);
                }

                $date = now();
                foreach ($tags as $tag) {
                    if (!in_array($tag, $documentTags)) {
                        DocumentTags::create([
                            'DocId' => $document->Id,
                            'TagId' => $tag,
                            'CreatedBy' => $user->Id,
                            'ModifiedBy' => $user->Id,
                            'CreatedOn' => $date,
                            'ModifiedOn' => $date
                        ]);
                        activity()->causedBy($user)->performedOn($document)->event('create')->log('added tag ');
                    }
                }

                return $this->succeeded('tags updated successfully.', route('files.show', [$document->repository->RepositoryId, $document->DocumentId]));
            });
        } catch (Throwable|Exception $e) {
            Log::error('Could not attach document tags ' . $e);
        }
        return $this->errored('unexpected error occurred');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Document $document): View
    {
        $this->authorize('view', $document);
        $user = auth()->user();
        $tags = DMSTags::where(static function (Builder $query) use ($user) {
            $query->where('Visibility', VisibilityEnum::Public->value)
                ->orWhere(function (Builder $query) use ($user) {
                    $query->where('Visibility', VisibilityEnum::Private->value)
                        ->where('t_DMSTags.CreatedBy', $user->Id);
                });
        })->get();

        return view('dms.files.tags')
            ->with('file', $document)
            ->with('tags', $tags)
            ->with('file_tags', (new DocumentService($document))->tags($user)->select('t_DMSTags.Id')->pluck('Id')->toArray());
    }
}
