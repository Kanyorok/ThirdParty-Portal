<?php

namespace App\Http\Controllers\DMS;

use App\Enums\Core\IntegrationsEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\UploadDocumentRequest;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentValidation;
use App\Models\Settings\APICredential;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Yajra\DataTables\DataTables;

class DocumentValidationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DocumentValidation::query()->where(function ($query) {
                $query->whereNull('ApprovedBy')
                    ->orWhereNull('DocumentId');
            });
            try {
                return Datatables::of($query->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
                    ->addColumn('action', function (DocumentValidation $documentValidation) {
                        return '<a href="' . route('dms.validation.show', $documentValidation->ValidationId) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> details</a>';
                    })->editColumn('Type', function (DocumentValidation $documentValidation) {
                        return $documentValidation->Type->description();
                    })->addColumn('Stage', function (DocumentValidation $documentValidation) {

                        if (is_null($documentValidation->DocumentId)) {
                            return '<span class="badge rounded-pill bg-primary">Pending Document</span>';
                        }
                        if (is_null($documentValidation->ApprovedBy)) {
                            return '<span class="badge rounded-pill bg-warning">Pending</span>';
                        }
                        return '<span class="badge rounded-pill bg--success">Approved</span>';
                    })->rawColumns(['action', 'Stage'])->make();
            } catch (Exception $e) {
                return $this->errored('failed loading document validation.');
            }
        }
        return view('dms.validation.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($documentValidationId)
    {
        $documentValidation = DocumentValidation::query()->where('ValidationId', $documentValidationId)->with(['attributes', 'creator'])->first();
        if (!$documentValidation instanceof DocumentValidation) {
            return redirect()->back()->with('error', 'Invalid validation provided');
        }

        $document = $documentValidation->document;
        if (!$document instanceof Document) {
            $document = null;
        }

        return view('dms.validation.show', compact('documentValidation', 'document'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function approve(Request $request, $documentValidationId): JsonResponse
    {
        $documentValidation = DocumentValidation::query()->where('ValidationId', $documentValidationId)->whereNull('ApprovedBy')->first();
        if (!$documentValidation instanceof DocumentValidation) {
            return $this->errored('Invalid validation provided');
        }

        $actor = $request->user();

        try {
            $ApiCred = APICredential::query()->where('Integration', IntegrationsEnum::DMSCoreBanking->value)->latest('Id')->first();
            if (!$ApiCred instanceof APICredential) {
                return $this->errored('confirmation to cbs failed.');
            }
            $URL = $ApiCred->Configuration?->url;
            $TOKEN = $ApiCred->Configuration?->token;

            if (!filter_var($URL, FILTER_VALIDATE_URL)) {
                return $this->errored('confirmation to cbs failed.');
            }

            if (!is_string($TOKEN) || empty($TOKEN)) {
                return $this->errored('confirmation to cbs failed.');
            }

            return DB::transaction(function () use ($documentValidation, $URL, $TOKEN, $actor) {
                $client = new Client();

                $response = $client->post($URL . '/Client/EDMSMemberDocumentsVerification', [
                    'headers' => [
                        'token' => $TOKEN,
                        'Content-Type' => 'application/json'
                    ],
                    'json' => [
                        'application_id' => $documentValidation->ValidationId,
                        'identification_number' => $documentValidation->attributes->where('Name', 'identification_number')->first()?->Value ?? '',
                        'member_name' => $documentValidation->Name,
                        'mobile_number' => $documentValidation->attributes->where('Name', 'mobile_number')->first()?->Value ?? '',
                        'member_email' => $documentValidation->attributes->where('Name', 'email')->first()?->Value ?? '',
                        'document_id' => $documentValidation->Id,
                        'document_type' => $documentValidation->Type->value,
                        'status' => 'APPROVED',
                        'date' => now()->format('Y-m-d H:i:s')
                    ]
                ]);

                if ($response->getStatusCode() !== 200) {
                    throw new ErroredException('Failed to confirm with CBS');
                }

                $documentValidation->update([
                    'ApprovedBy' => $actor->Id,
                    'ApprovedOn' => now(),
                ]);

                activity()->causedBy($actor)
                    ->performedOn($documentValidation)
                    ->event('approve')
                    ->log('Approved document validation');

                return $this->succeeded('Document validated successfully', route('dms.validation.show', $documentValidation->ValidationId));
            });

        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception|Throwable $e) {
            Log::error('Error validating document: ' . $e->getMessage());
            return $this->errored('Unexpected error, try again later');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UploadDocumentRequest $request, $documentValidationId): JsonResponse
    {

        $documentValidation = DocumentValidation::query()->where('ValidationId', $documentValidationId)->whereNull('DocumentId')->first();
        if (!$documentValidation instanceof DocumentValidation) {
            return $this->errored('Invalid validation provided');
        }

        $actor = $request->user();
        try {
            return DB::transaction(function () use ($documentValidation, $request, $actor) {
                $document = DocumentService::createUpload(RepositoryService::validation($documentValidation->Type), $request->file('file'), $actor)->document;
                $documentValidation->update([
                    'DocumentId' => $document->Id,
                    'ModifiedBy' => $actor->Id
                    /* 'ApprovedBy' => $request->user()->Id,
                     'ApprovedOn' => now(),*/
                ]);

                activity()->causedBy($actor)->performedOn($documentValidation)->event('document')->log('Uploaded ' . $document->Name . ' for validation.');

                return $this->succeeded('document uploaded successfully', route('dms.validation.show', $documentValidation->ValidationId));
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception|Throwable $e) {
            Log::error('Error upload ticket document : ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocumentValidation $documentValidation)
    {
        //
    }
}
