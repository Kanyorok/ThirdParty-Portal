<?php

namespace App\Http\Controllers\DMS\LegalHold;

use App\Enums\DMS\LegalHoldStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\CreateLegalHoldRequest;
use App\Models\Auth\User;
use App\Models\DMS\DMSTags;
use App\Models\DMS\Document;
use App\Models\DMS\LegalHold;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\DataTables;

class LegalHoldController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except(['index', 'create', 'show']);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', LegalHold::class);
        if ($request->ajax()) {
            return Datatables::of(LegalHold::query()->lock('WITH(NOLOCK)')->withCount('documents'))->addIndexColumn()
                ->addColumn('action', function (LegalHold $legalHold) {
                    return '<a  href="' . route('legal-hold.show', [$legalHold->Ref]) . '"  class="btn btn-info btn-sm"><i class="fas fa-eye"></i> details</button>';
                })->editColumn('documents_count', function ($legalHold) {
                    return number_format($legalHold->documents_count);
                })->editColumn('CreatedOn', function (LegalHold $legalHold) {
                    return $legalHold->CreatedOn?->format('F d, Y h:i A');
                })->editColumn('Status', function (LegalHold $legalHold) {
                    return match ($legalHold->Status->value) {
                        LegalHoldStatusEnum::Active->value => '<span class="badge rounded-pill bg-success">Active</span>',
                        LegalHoldStatusEnum::Canceled->value => '<details><summary><span class="badge rounded-pill bg-warning text-dark">Canceled</span></summary>
                            <p>Cancelled On: ' . $legalHold->ReleasedOn?->format('F d, Y h:i A') . '</p></details>',
                        LegalHoldStatusEnum::Released->value => '<details><summary><span class="badge rounded-pill bg-primary">Released</span></summary>
                            <p>Released On: ' . $legalHold->ReleasedOn?->format('F d, Y h:i A') . '</p></details>',
                    };
                })->rawColumns(['action', 'Status'])->make();
        }

        return view('dms.legal-hold.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateLegalHoldRequest $request)
    {
        $this->authorize('create', LegalHold::class);
        $documents = $request->getDocumentIds();
        $actor = $request->user();

        try {
            return DB::transaction(function () use ($request, $documents, $actor) {
                $dated = now();
                $hold = LegalHold::create([
                    'Name' => $request->string('Name')->toString(),
                    'Ref' => $request->getRef(),
                    'Description' => $request->string('Description', '')->toString(),
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ])->refresh();
                $hold->documents()->attach($documents, [
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ], false);

                $activities = (config('activitylog.enabled')) ? collect($documents)->map(function ($document) use ($dated, $actor) {
                    return [
                        'log_name' => config('activitylog.default_log_name'),
                        'description' => 'Document added to Legal Hold ',
                        'subject_id' => $document,
                        'subject_type' => Document::getPrimaryKey(),
                        'causer_id' => $actor->Id,
                        'causer_type' => User::getPrimaryKey(),
                        'created_at' => $dated,
                        'updated_at' => $dated,
                        'event' => 'Legal Hold',
                    ];
                })->add([
                    'log_name' => config('activitylog.default_log_name'),
                    'description' => 'Created legal hold for documents',
                    'subject_id' => $hold->Id,
                    'subject_type' => LegalHold::getPrimaryKey(),
                    'causer_id' => $actor->Id,
                    'causer_type' => User::getPrimaryKey(),
                    'created_at' => $dated,
                    'updated_at' => $dated,
                    'event' => 'Create',
                ]) : collect();

                if ($activities->isNotEmpty()) {
                    DB::table(config('activitylog.table_name'))->insert($activities->toArray());
                }

                return $this->succeeded('Legal Hold created successfully');
            });
        } catch (Throwable | Exception $e) {
            Log::error('Error creating legal hold: ' . $e);

            return $this->errored('an error occurred while creating legal hold');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', LegalHold::class);

        return view('dms.legal-hold.create')->with('tags', DMSTags::query()->user($request->user())->get(['t_DMSTags.TagID', 't_DMSTags.Name']));
    }

    /**
     * Display the specified resource.
     */
    public function show(LegalHold $dMSLegalHold)
    {
        $this->authorize('view', $dMSLegalHold);

        return view('dms.legal-hold.show')
            ->with('hold', $dMSLegalHold->loadCount('documents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LegalHold $dMSLegalHold)
    {
        $request->validate([
            'Name' => 'required|string|max:255',
            'Description' => 'nullable|string|max:5000',
        ]);

        $actor = $request->user();

        try {
            return DB::transaction(function () use ($request, $dMSLegalHold, $actor) {
                $dMSLegalHold->forceFill([
                    'Name' => $request->string('Name')->trim()->toString(),
                    'Description' => $request->string('Description', '')->trim()->toString(),
                    'ModifiedBy' => $actor->Id,
                ]);

                activity()->causedBy($actor)->performedOn($dMSLegalHold)->event('update')->log('updated ' . $dMSLegalHold->Ref . ' legal hold.');

                return $this->succeeded('Legal Hold updated successfully', route('legal-hold.show', [$dMSLegalHold->Ref]));
            });
        } catch (Throwable | Exception $e) {
            Log::error('Error updating legal hold: ' . $e);

            return $this->errored('an error occurred while updating legal hold');
        }
    }

    /**
     * Cancel
     */
    public function destroy(Request $request, LegalHold $dMSLegalHold)
    {
        $actor = $request->user();

        try {
            return DB::transaction(function () use ($dMSLegalHold, $actor) {
                $dMSLegalHold->update([
                    'Status' => LegalHoldStatusEnum::Canceled,
                    'ReleasedOn' => now(),
                    'ReleasedBy' => $actor->Id,
                ]);


                $documents = $dMSLegalHold->documents()->get(['DocId'])->pluck('DocId')->toArray();
                $dated = now();
                $activities = (config('activitylog.enabled')) ? collect($documents)->map(function ($document) use ($dMSLegalHold, $dated, $actor) {
                    return [
                        'log_name' => config('activitylog.default_log_name'),
                        'description' => 'Legal Hold (' . $dMSLegalHold->Ref . ') Canceled',
                        'subject_id' => $document,
                        'subject_type' => Document::getPrimaryKey(),
                        'causer_id' => $actor->Id,
                        'causer_type' => User::getPrimaryKey(),
                        'created_at' => $dated,
                        'updated_at' => $dated,
                        'event' => 'Legal Hold',
                    ];
                })->add([
                    'log_name' => config('activitylog.default_log_name'),
                    'description' => 'Canceled legal hold for documents',
                    'subject_id' => $dMSLegalHold->Id,
                    'subject_type' => LegalHold::getPrimaryKey(),
                    'causer_id' => $actor->Id,
                    'causer_type' => User::getPrimaryKey(),
                    'created_at' => $dated,
                    'updated_at' => $dated,
                    'event' => 'Canceled',
                ]) : collect();

                if ($activities->isNotEmpty()) {
                    DB::table(config('activitylog.table_name'))->insert($activities->toArray());
                }

                return $this->succeeded('Legal Hold canceled successfully', route('legal-hold.show', [$dMSLegalHold->Ref]));
            });
        } catch (Throwable | Exception $e) {
            Log::error('Error canceling legal hold: ' . $e);

            return $this->errored('an error occurred while canceling legal hold');
        }
    }

    /**
     * release
     */
    public function release(Request $request, LegalHold $dMSLegalHold)
    {
        $actor = $request->user();

        try {
            return DB::transaction(function () use ($dMSLegalHold, $actor) {
                $dMSLegalHold->update([
                    'Status' => LegalHoldStatusEnum::Released,
                    'ReleasedOn' => now(),
                    'ReleasedBy' => $actor->Id,
                ]);


                $documents = $dMSLegalHold->documents()->get(['DocId'])->pluck('DocId')->toArray();
                $dated = now();
                $activities = (config('activitylog.enabled')) ? collect($documents)->map(function ($document) use ($dMSLegalHold, $dated, $actor) {
                    return [
                        'log_name' => config('activitylog.default_log_name'),
                        'description' => 'Legal Hold (' . $dMSLegalHold->Ref . ') Released',
                        'subject_id' => $document,
                        'subject_type' => Document::getPrimaryKey(),
                        'causer_id' => $actor->Id,
                        'causer_type' => User::getPrimaryKey(),
                        'created_at' => $dated,
                        'updated_at' => $dated,
                        'event' => 'Legal Hold',
                    ];
                })->add([
                    'log_name' => config('activitylog.default_log_name'),
                    'description' => 'legal hold released',
                    'subject_id' => $dMSLegalHold->Id,
                    'subject_type' => LegalHold::getPrimaryKey(),
                    'causer_id' => $actor->Id,
                    'causer_type' => User::getPrimaryKey(),
                    'created_at' => $dated,
                    'updated_at' => $dated,
                    'event' => 'Released',
                ]) : collect();

                if ($activities->isNotEmpty()) {
                    DB::table(config('activitylog.table_name'))->insert($activities->toArray());
                }

                return $this->succeeded('Legal Hold released successfully', route('legal-hold.show', [$dMSLegalHold->Ref]));
            });
        } catch (Throwable | Exception $e) {
            Log::error('Error releasing legal hold: ' . $e);

            return $this->errored('an error occurred while releasing legal hold');
        }
    }
}
