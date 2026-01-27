<?php

namespace App\Http\Controllers\DMS\Files;

use App\Http\Controllers\Controller;
use App\Http\Resources\DMS\FilesCollection;
use App\Models\DMS\Document;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class DocumentRecentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View|FilesCollection
    {
//->where('event', 'view')


        if ($request->ajax()) {
            $documents = Document::query()->user($request->user())
                ->select('t_Documents.*')
                ->join(config('activitylog.table_name') . ' as recent_activity', function ($join) use ($request) {
                    $join->on('t_Documents.Id', '=', 'recent_activity.subject_id')
                        ->where('recent_activity.subject_type', 'DocumentId')
                        ->where('recent_activity.causer_type', 'UserID')
                        ->where('recent_activity.causer_id', $request->user()->Id);
                })
                ->joinSub(
                    Activity::query()
                        ->select('subject_id')
                        ->selectRaw('MAX(created_at) as max_created_at')
                        ->where('subject_type', 'DocumentId')
                        ->where('causer_type', 'UserID')
                        ->where('causer_id', $request->user()->Id)
                        ->groupBy('subject_id'),
                    'latest_activity',
                    function ($join) {
                        $join->on('recent_activity.subject_id', '=', 'latest_activity.subject_id')
                            ->on('recent_activity.created_at', '=', 'latest_activity.max_created_at');
                    }
                )
                ->whereHas('current')
                ->with(['current'])
                ->orderByDesc('recent_activity.created_at')->paginate(40);

            return new FilesCollection($documents);
        }

        return view('dms.files.recent');
    }
}
