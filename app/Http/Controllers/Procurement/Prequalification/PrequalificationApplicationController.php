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
use Illuminate\Database\QueryException;
use App\Enums\Procurement\PrequalificationApplicationEnum;
use App\Http\Resources\Procurement\PrequalificationRoundResource;
use App\Http\Resources\Procurement\PrequalificationApplicationResource;
use Illuminate\Support\Facades\Auth;

class PrequalificationApplicationController extends Controller
{
    public function index(): View
    {
        $applications = PrequalificationApplication::with('round', 'supplier', 'category')->paginate(10);
        return view('procurement.suppliers.prequalification.supplier-applications.index', compact('applications'));
    }

    public function show(PrequalificationApplication $application): View
    {
        // $application->load('round.masterSections.criteria');
        // return view('procurement.suppliers.prequalification.supplier-applications.show', compact('application'));

        $application->load('round.prequalificationSections.masterSection.criteria', 'category');

        return view(
            'procurement.suppliers.prequalification.supplier-applications.show',
            compact('application')
        );
    }

    public function apiIndex(): JsonResponse
    {
        $user = Auth::user();
        $supplierId = $user && $user->thirdParty ? $user->thirdParty->Id : null;

        // Include ALL open rounds, marking those already applied to by this supplier instead of excluding them.
        $availableRounds = PrequalificationRound::query()
            ->with(['sections.criteria', 'criteria.masterCriteria'])
            ->select('t_PrequalificationRounds.*')
            ->leftJoin('t_SupplierPrequalificationApplications as apps', function ($join) use ($supplierId) {
                $join->on('apps.RoundID', '=', 't_PrequalificationRounds.RoundID')
                    ->whereNull('apps.DeletedOn')
                    ->where('apps.SupplierID', '=', $supplierId);
            })
            ->addSelect([
                'apps.ApplicationID as applicationId',
                // Placeholder: pivot table lacks CreatedBy; default 0 so frontend can rely on field existence
                DB::raw('0 as createdByOwner')
            ])
            ->where('t_PrequalificationRounds.Status', PrequalificationRoundEnum::Open)
            ->latest('t_PrequalificationRounds.StartDate')
            ->paginate(10);

        return PrequalificationRoundResource::collection($availableRounds)->response();
    }

    public function apiShow(PrequalificationRound $round): JsonResponse
    {
        $round->load(['sections.criteria.masterCriteria', 'applications']);
        return (new PrequalificationRoundResource($round))->response();
    }

    public function store(StorePrequalificationApplicationRequest $request): JsonResponse
    {
        if (!Auth::check()) return response()->json(['error' => 'User not authenticated'], 401);

        $user = Auth::user();
        if (!$user->thirdParty) return response()->json(['error' => 'User not associated with a third party.'], 400);

        $validatedData = $request->validated();
        $supplierId = $user->thirdParty->Id;
        $roundId = $validatedData['round_id'];
        $categoryIds = $validatedData['category_ids'] ?? [];

        // normalize unique ids
        $categoryIds = array_values(array_unique(array_filter($categoryIds, 'is_numeric')));

        if (empty($categoryIds)) {
            return response()->json(['message' => 'At least one category must be selected.'], 422);
        }

        // check duplicates: existing rows for same supplier+round with any of these category IDs (ignore soft-deleted)
        $existing = PrequalificationApplication::query()
            ->where('SupplierID', $supplierId)
            ->where('RoundID', $roundId)
            ->whereNull('DeletedOn')
            ->whereIn('CategoryID', $categoryIds)
            ->pluck('CategoryID')
            ->toArray();

        if (!empty($existing)) {
            // fetch category names for better message
            $dupNames = \App\Models\ThirdParty\SupplierCategory::whereIn('SupplierCategoryID', $existing)
                ->pluck('CategoryName', 'SupplierCategoryID')
                ->toArray();

            $duplicates = [];
            foreach ($existing as $cid) {
                $duplicates[] = ['id' => $cid, 'name' => $dupNames[$cid] ?? null];
            }

            return response()->json([
                'message' => 'Some categories have already been applied for this round.',
                'duplicates' => $duplicates,
            ], 409);
        }

        // create records — one row per category
        DB::beginTransaction();
        try {
            $createdIds = [];
            foreach ($categoryIds as $cid) {
                $app = PrequalificationApplication::create([
                    'RoundID' => $roundId,
                    'SupplierID' => $supplierId,
                    'CategoryID' => $cid,
                    'Status' => PrequalificationApplicationEnum::Submitted,
                    'SubmittedOn' => now(),
                    'CreatedBy' => $user->Id,
                ]);
                $createdIds[] = $app->ApplicationID;
            }
            DB::commit();

            return response()->json([
                'message' => 'Application submitted.',
                'applicationIds' => $createdIds,
                'roundId' => $roundId,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to create prequalification application', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to submit application.'], 500);
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
