<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Enums\Procurement\PrequalificationRoundEnum;
use App\Http\Requests\Procurement\Suppliers\Prequalification\StorePrequalificationApplicationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Enums\Procurement\PrequalificationApplicationEnum;

class PrequalificationApplicationController extends Controller
{
    // ERP Views; Being silly :)
    public function index(): View
    {
        $applications = PrequalificationApplication::with('round', 'vendor')->paginate(10);
        return view('procurement.applications.index', compact('applications'));
    }

    public function show(PrequalificationApplication $application): View
    {
        $application->load('round.masterSections.criteria', 'responses');
        return view('procurement.applications.show', compact('application'));
    }

    // Someone said API-driven too? Say no more
    public function apiIndex(Request $request): JsonResponse
    {
        $vendorId = auth()->id(); //logged on supplier.
        $availableRounds = PrequalificationRound::where('Status', PrequalificationRoundEnum::Open)
            ->whereDoesntHave('applications', function ($query) use ($vendorId) {
                $query->where('SupplierID', $vendorId);
            })->get();

        return response()->json($availableRounds);
    }

    public function apiShow(PrequalificationRound $round): JsonResponse
    {
        $round->load('sections.masterSection', 'criteria.masterCriteria');
        return response()->json($round);
    }

    public function store(StorePrequalificationApplicationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $vendorId = auth()->id(); // logged on supplier

        DB::beginTransaction();
        try {
            $application = PrequalificationApplication::create([
                'RoundID' => $validatedData['round_id'],
                'SupplierID' => $vendorId, // this can be done as $supplier->$SupplierID
                'Status' => PrequalificationApplicationEnum::Submitted,
                'SubmittedOn' => now(),
            ]);

            foreach ($validatedData['responses'] as $response) {
                $filePath = null;
                $fileName = null;
                if (isset($response['file']) && $response['file'] instanceof \Illuminate\Http\UploadedFile) {
                    $file = $response['file'];
                    $filePath = $file->store('prequalification_documents', 'public');
                    $fileName = $file->getClientOriginalName();
                }
                $application->responses()->create([
                    'CriteriaID' => $response['criteria_id'],
                    'ResponseText' => $response['response_text'] ?? null,
                    'FilePath' => $filePath,
                    'FileName' => $fileName,
                ]);
            }

            DB::commit();
            $application->load('responses', 'supplier', 'round');
            // return response()->json(['message' => 'Application submitted successfully.', 'application_id' => $application->ApplicationID], 201);
            return response()->json([
                'message' => 'Application submitted successfully.',
                'application' => $application,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit application: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to submit application.'], 500);
        }
    }
}
