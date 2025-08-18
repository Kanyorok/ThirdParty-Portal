<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Enums\Procurement\PrequalificationRoundEnum;
use App\Http\Requests\Procurement\Suppliers\Prequalification\StorePrequalificationApplicationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\Procurement\PrequalificationApplicationEnum;

class PrequalificationApplicationController extends Controller
{
    public function index(): View
    {
        $applications = PrequalificationApplication::with('round', 'supplier')->paginate(10);
        return view('procurement.suppliers.prequalification.supplier-applications.index', compact('applications'));
    }

    public function show(PrequalificationApplication $application): View
    {
        // $application->load('round.masterSections.criteria', 'responses');
        return view('procurement.suppliers.prequalification.supplier-applications.show', compact('application'));
    }

    public function apiIndex(): JsonResponse
    {
        $vendorId = auth()->id();
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
        if (!auth()->check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $validatedData = $request->validated();
        $user = auth()->user();

        if (!$user->thirdParty) {
            return response()->json(['error' => 'User not associated with a third party.'], 400);
        }

        $supplierId = $user->thirdParty->Id;

        $existingApplication = PrequalificationApplication::where('SupplierID', $supplierId)
            ->where('RoundID', $validatedData['round_id'])
            ->first();

        if ($existingApplication) {
            return response()->json([
                'message' => 'You have already applied for this prequalification round.',
                'reference' => 'APP-' . $existingApplication->ApplicationID,
                'application' => $existingApplication,
            ], 200);
        }

        DB::beginTransaction();
        try {
            $application = PrequalificationApplication::create([
                'RoundID' => $validatedData['round_id'],
                'SupplierID' => $supplierId,
                'Status' => PrequalificationApplicationEnum::Submitted,
                'SubmittedOn' => now(),
                'CreatedBy' => $user->Id,
            ]);

            DB::commit();

            $application->load('supplier', 'round');

            return response()->json([
                'message' => 'Prequalification application submitted successfully.',
                'reference' => 'APP-' . $application->ApplicationID,
                'application' => $application,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit application: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to submit application.', 'trace' => $e->getTraceAsString()], 500);
        }
    }
}
