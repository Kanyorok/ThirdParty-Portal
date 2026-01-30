<?php

namespace App\Http\Controllers\DMS\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\DocumentSignatureRequest;
use App\Models\DMS\DMSSignature;
use App\Services\DMS\Verification\SignatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class SignatureController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except(['show', 'index']);
        $this->authorizeResource(DMSSignature::class, 'dMSSignature');
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            try {
                return Datatables::of(DMSSignature::query()->userCreator($request->user())->lock('WITH(NOLOCK)')->withCount('documents'))->addIndexColumn()
                    ->addColumn('action', function (DMSSignature $signature) {
                        return '<a  href="' . route('document-signature.show', [$signature->SignatureId]) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> details</button>';
                    })->editColumn('documents_count', function (DMSSignature $signature) {
                        return number_format($signature->documents_count);
                    })->addColumn('Description', function (DMSSignature $signature) {
                        return Str::of($signature->Description)->limit(100);
                    })->rawColumns(['action'])->make();
            } catch (\Exception $e) {
                return $this->errored('fetching data failed, try again later');
            }
        }

        return view('dms.signatures.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('dms.signatures.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentSignatureRequest $request): JsonResponse
    {
        $visibility = $request->getVisibility();
        $contentPosition = $request->getContentPosition();

        try {
            return \DB::transaction(function () use ($request, $visibility, $contentPosition) {
                SignatureService::create(
                    $request->user(),
                    $request->string('Name')->trim()->toString(),
                    $visibility,
                    $request->integer('Width', 100),
                    $request->integer('Height', 200),
                    $request->integer('Horizontal', 10),
                    $request->integer('Vertical', 10),
                    $request->integer('Opacity', 100),
                    $request->str('Content')->trim()->toString(),
                    $request->string('ContentColour'),
                    $request->integer('ContentSize', 10),
                    $contentPosition,
                    $request->string('ContentBorderColour'),
                    $request->integer('ContentBorderWeight', 1),
                    $request->file('file'),
                    $request->string('Description', null)->trim()->toString()
                );

                return $this->succeeded('signature created successfully');
            });
        } catch (\Throwable $e) {
            Log::error('creating signature failed : ' . $e);
        }

        return $this->errored('an unexpected error occurred, try again later');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, DMSSignature $dMSSignature): View
    {
        $actor = $request->user();
        $Content = Str::of($dMSSignature->Content)->trim()->replace(
            [" ", '#name#', '#userid#', '#datetime#', '#date#'],
            ["<br/>", $actor->Name, $actor->UserID, now()->format('d M Y H:i'), now()->format('d M Y')]
        )->limit(100, '>>')->toString();

        return view('dms.signatures.show', ['signature' => $dMSSignature->loadCount('documents'), 'SignatureContent' => $Content]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DMSSignature $dMSSignature): View
    {
        return view('dms.signatures.edit', ['signature' => $dMSSignature]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DocumentSignatureRequest $request, DMSSignature $dMSSignature): JsonResponse
    {
        $visibility = $request->getVisibility();
        $contentPosition = $request->getContentPosition();
        $actor = $request->user();

        try {
            return \DB::transaction(function () use ($dMSSignature, $request, $visibility, $contentPosition, $actor) {
                $service = new SignatureService($dMSSignature);
                if ($request->hasFile('file')) {
                    $service->setImage($request->file('file'), $actor);
                }

                $service->update(
                    $actor,
                    $request->string('Name')->trim()->toString(),
                    $visibility,
                    $request->integer('Width', 100),
                    $request->integer('Height', 200),
                    $request->integer('Horizontal', 10),
                    $request->integer('Vertical', 10),
                    $request->integer('Opacity', 100),
                    $request->str('Content')->trim()->toString(),
                    $request->string('ContentColour'),
                    $request->integer('ContentSize', 10),
                    $contentPosition,
                    $request->string('ContentBorderColour'),
                    $request->integer('ContentBorderWeight', 1),
                    $request->string('Description', null)->trim()->toString()
                );

                return $this->succeeded('signature updated successfully', route: route('document-signature.show', [$dMSSignature->SignatureId]));
            });
        } catch (\Throwable $e) {
            Log::error('updating signature failed : ' . $e);
        }

        return $this->errored('an unexpected error occurred, try again later');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, DMSSignature $dMSSignature): JsonResponse
    {
        try {
            return \DB::transaction(function () use ($dMSSignature, $request) {
                if ((new SignatureService($dMSSignature))->trash($request->user())) {
                    return $this->succeeded('signature trashed successfully', route: route('document-signature.index'));
                }

                throw new \RuntimeException('signature trashed failed, returned false');
            });
        } catch (\Throwable $e) {
            Log::error('updating signature failed : ' . $e);
        }

        return $this->errored('an unexpected error occurred, try again later');
    }
}
