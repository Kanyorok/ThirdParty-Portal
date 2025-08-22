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
        // $application->load('round.masterSections.criteria');
        // return view('procurement.suppliers.prequalification.supplier-applications.show', compact('application'));

        $application->load('round.prequalificationSections.masterSection.criteria');

        return view(
            'procurement.suppliers.prequalification.supplier-applications.show',
            compact('application')
        );
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
        if (!auth()->check()) return response()->json(['error' => 'User not authenticated'], 401);

        $user = auth()->user();
        if (!$user->thirdParty) return response()->json(['error' => 'User not associated with a third party.'], 400);

        $validatedData = $request->validated();
        $supplierId = $user->thirdParty->Id;

        $existingApplication = PrequalificationApplication::where('SupplierID', $supplierId)
            ->where('RoundID', $validatedData['round_id'])
            ->first();

        if ($existingApplication) {
            return response()->json([
                'message' => 'Already applied.',
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

            if (!empty($validatedData['category_ids'])) {
                $application->categories()->sync($validatedData['category_ids']);
            }

            DB::commit();
            $application->load('supplier', 'round', 'categories');

            return response()->json([
                'message' => 'Application submitted successfully.',
                'reference' => 'APP-' . $application->ApplicationID,
                'application' => $application,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to submit application.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(PrequalificationApplication $application)
    {
        try {
            $application->delete();
            return redirect()
                ->route('prequalification.applications.index')
                ->with('success', 'Application deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete application: ' . $e->getMessage());
            return redirect()
                ->route('prequalification.applications.index')
                ->with('error', 'Failed to delete application. Please try again.');
        }
    }
}
