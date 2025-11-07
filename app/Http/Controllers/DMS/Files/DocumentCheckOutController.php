<?php

namespace App\Http\Controllers\DMS\Files;

use App\Enums\DMS\DocumentCheckOutStatusEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DMS\DocumentCheckInRequest;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentCheckOut;
use App\Services\DMS\DocumentService;
use App\Services\HRM\UserService;
use App\Services\PartyService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Yajra\DataTables\DataTables;


class DocumentCheckOutController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * List of checkouts & Ins
     */
    public function index(Request $request, Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        try {
            return Datatables::of($document->checkouts()->with(['creator'])->select('*'))->addIndexColumn()
                ->editColumn('creator', function (DocumentCheckOut $checkOut) {
                    return (new PartyService($checkOut->creator))->getDTRow();
                })->editColumn('CheckOutRemark', function (DocumentCheckOut $checkOut) {
                    return '<details><summary>' . $checkOut->CreatedOn->format('M Y, D H:i') . '</summary><b>Notes:</b> ' . $checkOut->CheckOutRemark . '</details>';
                })->editColumn('Status', function (DocumentCheckOut $checkOut) use ($document, $request) {
                    $action = ($request->user()->Id === $checkOut->CreatedBy || $request->user()->can('destroy', $document)) ? '<a href="javascript:void(0)" class="btn btn-sm btn-outline-danger action-checkout-cancel" data-action="' . route('document-checkouts.destroy', [$document->DocumentId, $checkOut->Id]) . '"><i class="fas fa-trash-alt"></i></a>' : '';
                    return match ($checkOut->Status->value) {
                        DocumentCheckOutStatusEnum::CheckOut->value => 'CheckedOut &nbsp;' . $action,
                        DocumentCheckOutStatusEnum::CheckIn->value => '<details><summary>Checked In</summary><b>Dated:</b> ' . $checkOut->Dated->format('M Y, D H:i') . '<br><b>Notes:</b> ' . $checkOut->CheckInRemark . '</details>',
                        DocumentCheckOutStatusEnum::Canceled->value => '<details><summary>Canceled</summary><b>Dated:</b> ' . $checkOut->Dated->format('M Y, D H:i') . '<br><b>Notes:</b> ' . $checkOut->CheckInRemark . '</details>',
                        default => "",
                    };
                })->editColumn('CreatedOn', function (DocumentCheckOut $checkOut) {
                    return $checkOut->CreatedOn->format('d M, Y H:i');
                })->setRowClass(function (DocumentCheckOut $checkOut) {
                    return ($checkOut->Status->value === DocumentCheckOutStatusEnum::CheckOut->value) ? 'fw-bold' : '';
                })->rawColumns(['creator', 'CheckOutRemark', 'Status'])->make();
        } catch (Exception) {
        }
        return $this->errored('cannot retrieve checkout history for this document.');
    }

    /**
     * Checkout.
     */
    public function store(Request $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);
        $request->validate(['CheckOutRemark' => 'nullable|string|max:500']);
        $actor = $request->user();

        try {
            $service = (new DocumentService($document))->checkout($actor, $request->string('CheckOutRemark', '')->trim()->limit(500, '')->toString());
        } catch (ErroredException $e) {
            return $e->toJson();
        }

        return $this->succeeded('document checked out successfully', route: route('file-download.index', ['document' => $document->DocumentId, 'token' => $service->generateToken($actor)]));
    }

    /**
     * CheckIn.
     */
    public function update(DocumentCheckInRequest $request, Document $document, string $holder): JsonResponse
    {
        $actor = $request->user();
        try {
            (new DocumentService($document))->checkin($request->getFile($document), $actor, $request->string('CheckInRemark', '')->trim()->limit(500, '')->toString());
        } catch (ErroredException $e) {
            return $e->toJson();
        }
        return $this->succeeded('document checked in successfully', route: route('files.show', [$document->repository->RepositoryId, $document->DocumentId]));
    }

    /**
     * Cancel
     */
    public function destroy(Request $request, Document $document, int $documentCheckOutId): JsonResponse
    {
        $request->validate(['CheckOutCancelReason' => 'required|string|max:500']);
        $actor = $request->user();
        $checkOut = $document->checkouts()->where('Status', DocumentCheckOutStatusEnum::CheckOut->value)->first();
        if (!$checkOut instanceof DocumentCheckOut) {
            return $this->errored('you don\'t have an active checkout for this document.');
        }

        if ($actor->Id !== $checkOut->CreatedBy && $actor->can('destroy', $document)) {
            return $this->errored('you don\'t have permission to cancel this checkout.', status: 403);
        }

        try {
            return DB::transaction(function () use ($actor, $document, $checkOut, $request) {
                $checkOut->forceFill([
                    'Status' => DocumentCheckOutStatusEnum::Canceled->value,
                    'Dated' => now(),
                    'CheckInRemark' => $request->str('CheckOutCancelReason', '')->trim()->toString(),
                    'ModifiedBy' => $actor->Id,
                ])->save();

                activity()->causedBy($actor)->performedOn($document)->event('canceled')->log('canceled  document  checkout .');

                if ($checkOut->CreatedBy !== $actor->Id) {
                    (new UserService($actor))->sendEmail('Document Checkout Canceled',
                        '<p>Heads up ' . $actor->Name . ', <br>
                        We would like to inform you that the document you had previously checked out  — <strong>' . $document->Name . '</strong> — has been <strong>canceled</strong> and is no longer available for editing or review. <br>
                        Thank you for your attention.</p>')?->send();
                }
                return $this->succeeded('document trashed successfully', route: route('files.show', [$document->repository->RepositoryId, $document->DocumentId]));
            });
        } catch (Throwable|Exception $e) {
            Log::error('cancel document checkout failed :');
            Log::error($e);
        }

        return $this->errored('an unexpected error occurred');
    }
}
