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
        $this->authorizeResource(LegalHold::class);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
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
                    'CreatedOn' => $dated,
                    'ModifiedBy' => $actor->Id,
                    'ModifiedOn' => $dated,
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
                        'event' => 'Legal Hold'
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
                    'event' => 'Create'
                ]) : collect();

                if ($activities->isNotEmpty()) {
                    DB::table(config('activitylog.table_name'))->insert($activities->toArray());
                }

                return $this->succeeded('Legal Hold created successfully');
            });
        } catch (Throwable|Exception $e) {
            Log::error('Error creating legal hold: ' . $e);
            return $this->errored('an error occurred while creating legal hold');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return view('dms.legal-hold.create')->with('tags', DMSTags::query()->user($request->user())->get(['t_DMSTags.TagID', 't_DMSTags.Name']));
    }

    /**
     * Display the specified resource.
     */
    public function show(LegalHold $dMSLegalHold)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LegalHold $dMSLegalHold)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LegalHold $dMSLegalHold)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LegalHold $dMSLegalHold)
    {
        //
    }
}
