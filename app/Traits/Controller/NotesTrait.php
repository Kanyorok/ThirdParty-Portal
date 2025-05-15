<?php

namespace App\Traits\Controller;

use App\Models\Auth\User;
use App\Models\CRM\Notes;
use App\Services\ActivityService;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

trait NotesTrait
{
    /**
     * @throws Exception
     */
    public function notes(Builder|MorphMany $query): JsonResponse
    {
        return Datatables::of($query->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
            ->addColumn('action', function (Notes $notes) {
                return '<button type="button"  data-click_url="' . route('notes.show', [$notes->NoteID]) . '" data-summary_title="notes summary" class="btn btn-info btn-sm click-summary-data"><i class="fas fa-eye"></i> details</button>';
            })->editColumn('CreatedOn', function (Notes $notes) {
                return $notes->CreatedOn?->format('F d, Y h:i A');
            })->editColumn('Notes', function (Notes $notes) {
                return Str::limit($notes->Notes);
            })->rawColumns(['action'])->make();
    }

    public function save(MorphMany $query, string $notes, User $actor)
    {
        return DB::transaction(static function () use ($actor, $query, $notes) {
            $note = $query->create([
                                    'Notes'      => $notes,
                                    'CreatedBy'  => $actor->Id,
                                    'ModifiedBy' => $actor->Id,
                                   ]);

            return ActivityService::note($note, $actor->UserID . ' added a note.', $actor);
        });
    }
}
