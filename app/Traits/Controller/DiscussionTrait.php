<?php

namespace App\Traits\Controller;

use App\Models\Call;
use App\Models\Discussion;
use App\Models\Meeting;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

trait DiscussionTrait
{
    /**
     * @throws Exception
     */
    public function discussions(Builder|MorphMany $query): JsonResponse
    {
        return Datatables::of($query->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
            ->addColumn('action', function (Discussion $discussion) {
                return '<button type="button" data-click_url="' . route('discussions.show', [$discussion->DiscussionID]) . '" data-summary_title="discussion summary" class="btn btn-info btn-sm click-summary-data"><i class="fas fa-eye"></i> details</button>';
            })->editColumn('SourceType', function (Discussion $discussion) {
                return match ($discussion->SourceType) {
                    Call::getPrimaryKey() => 'Call',
                    Meeting::getPrimaryKey() => 'Meeting',
                    default => '? ?',
                };
            })->editColumn('CreatedOn', function (Discussion $discussion) {
                return $discussion->CreatedOn?->format('F d, Y h:i A');
            })->editColumn('Discussion', function (Discussion $discussion) {
                return Str::limit($discussion->Discussion);
            })->rawColumns(['action'])->make();
    }
}
