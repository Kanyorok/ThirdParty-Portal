<?php

namespace App\Http\Controllers\DMS\Verification;

use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\DocumentSignatureRequest;
use App\Models\DMS\DMSSignature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class SignatureController extends Controller
{
    public function __construct()
    {
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
    public function create()
    {
        return view('dms.signatures.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentSignatureRequest $request)
    {
        dd($request->all());
        /**
         * "Name" => "Approval Green Image"
         * "Visibility" => "pub"
         * "Opacity" => "90"
         * "Horizontal" => "10"
         * "Vertical" => "10"
         * "Width" => "300"
         * "Height" => "500"
         * "Content" => "#userid# #datetime#"
         * "ContentPosition" => "ss"
         * "ContentColour" => "#ff6347"
         * "ContentSize" => "28"
         * "ContentBorderColour" => "#1b1b1b"
         * "ContentBorderWeight" => "1"
         * "Description" => null
         * "file" => Illuminate\Http\UploadedFile {#5424
         */
    }

    /**
     * Display the specified resource.
     */
    public function show(DMSSignature $dMSSignature)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DMSSignature $dMSSignature)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DMSSignature $dMSSignature)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DMSSignature $dMSSignature)
    {
        //
    }
}
