<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\ApplicationCategoryStatus;
use App\Models\Procurement\Prequalification\CategoryProgressHistory;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrequalificationProgressController extends Controller
{
    public function getApplicationProgress(Request $request, int $roundId)
    {
        $user = Auth::user();
        $supplierId = $user?->ThirdPartyID ?? null; // adapt if different linkage

        if (! $supplierId) {
            return response()->json(['error' => 'No supplier linked to user'], 422);
        }

        $application = PrequalificationApplication::where('SupplierID', $supplierId)
            ->where('RoundID', $roundId)
            ->with(['categoryStatuses.category'])
            ->first();

        if (! $application) {
            return response()->json([
                'data' => [
                    'overall_status' => 'NOT_APPLIED',
                    'categories' => [],
                    'summary' => [
                        'total_categories' => 0,
                        'approved_categories' => 0,
                        'rejected_categories' => 0,
                        'pending_categories' => 0,
                        'overall_progress' => 0,
                    ],
                ],
            ]);
        }

        $categories = $application->categoryStatuses->map(function ($catStatus) {
            $statusCode = (string) $catStatus->Status;
            $statusLabel = match ($statusCode) {
                'A', 'P' => 'Approved',
                'R' => 'Rejected',
                'U', 'V' => 'Under Review',
                'S' => 'Submitted',
                'D', 'C' => 'Pending',
                default => 'Submitted',
            };

            return [
                'category_id' => $catStatus->CategoryId,
                'category_name' => $catStatus->category?->CategoryName,
                'status' => $statusCode,
                'status_label' => $statusLabel,
                'progress_percent' => (float)$catStatus->ProgressPercent,
                'stage' => $catStatus->Stage,
                'stage_label' => $catStatus->StageLabel,
                'updated_on' => optional($catStatus->ModifiedOn ?? $catStatus->CreatedOn)->toISOString(),
                'decision_date' => optional($catStatus->DecisionDate)->toISOString(),
                'rejection_reason' => $catStatus->RejectionReason,
            ];
        })->values();

        $summary = $this->calculateProgressSummary($application->categoryStatuses);

        return response()->json([
            'data' => [
                'overall_status' => match ((string)($application->Status?->value ?? $application->Status)) {
                    'A', 'P' => 'Approved',
                    'R' => 'Rejected',
                    'U', 'V' => 'Under Review',
                    'S' => 'Submitted',
                    'D', 'C' => 'Pending',
                    default => 'Submitted',
                },
                'categories' => $categories,
                'summary' => $summary,
            ],
        ]);
    }

    public function updateCategoryProgress(Request $request, int $roundId, int $categoryId)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|integer',
            'status' => 'required|in:D,S,U,C,A,R',
            'progress_percent' => 'required|numeric|min:0|max:100',
            'stage' => 'required|string',
            'stage_label' => 'required|string',
            'notes' => 'nullable|string',
            'rejection_reason' => 'nullable|string',
        ]);

        $supplierId = (int)$validated['supplier_id'];
        $userId = Auth::id();

        $application = PrequalificationApplication::where([
            'SupplierID' => $supplierId,
            'RoundID' => $roundId,
        ])->first();

        if (! $application) {
            return response()->json(['error' => 'Application not found for supplier/round'], 404);
        }

        DB::transaction(function () use ($application, $categoryId, $validated, $userId) {
            $row = ApplicationCategoryStatus::firstOrNew([
                'ApplicationId' => $application->ApplicationID,
                'CategoryId' => $categoryId,
            ]);

            $previousStatus = $row->Status;
            $previousProgress = (float)($row->ProgressPercent ?? 0);

            $row->fill([
                'Status' => $validated['status'],
                'ProgressPercent' => $validated['progress_percent'],
                'Stage' => $validated['stage'],
                'StageLabel' => $validated['stage_label'],
                'RejectionReason' => $validated['rejection_reason'] ?? null,
                'ModifiedBy' => $userId,
                'ModifiedOn' => now(),
            ]);
            if (! $row->exists) {
                $row->CreatedBy = $userId;
                $row->CreatedOn = now();
            }
            $row->save();

            CategoryProgressHistory::create([
                'ApplicationCategoryId' => $row->Id,
                'PreviousStatus' => $previousStatus,
                'NewStatus' => $row->Status,
                'PreviousProgress' => $previousProgress,
                'NewProgress' => (float)$row->ProgressPercent,
                'ChangedBy' => $userId,
                'Notes' => $validated['notes'] ?? null,
                'CreatedBy' => $userId,
                'CreatedOn' => now(),
            ]);
        });

        return response()->json(['status' => 'ok']);
    }

    public function getMyApplications(Request $request)
    {
        $user = Auth::user();
        $supplierId = $user?->ThirdPartyID ?? null;
        if (! $supplierId) {
            return response()->json(['data' => []]);
        }
        $apps = PrequalificationApplication::where('SupplierID', $supplierId)
            ->with(['categoryStatuses.category', 'round'])
            ->orderByDesc('CreatedOn')
            ->limit(50)
            ->get();

        $out = $apps->map(function ($app) {
            return [
                'application_id' => $app->ApplicationID,
                'round_id' => $app->RoundID,
                'status' => $app->Status?->getLabel() ?? 'Submitted',
                'categories' => $app->categoryStatuses->map(fn ($cs) => [
                    'category_id' => $cs->CategoryId,
                    'status' => $cs->Status,
                    'progress_percent' => (float)$cs->ProgressPercent,
                ])->values(),
            ];
        })->values();

        return response()->json(['data' => $out]);
    }

    private function calculateProgressSummary($categoryStatuses): array
    {
        $total = $categoryStatuses->count();
        $approved = $categoryStatuses->where('Status', 'A')->count();
        $rejected = $categoryStatuses->where('Status', 'R')->count();
        $submitted = $categoryStatuses->where('Status', 'S')->count();
        $underReview = $categoryStatuses->whereIn('Status', ['U', 'V'])->count();
        $pending = $categoryStatuses->whereIn('Status', ['D', 'C'])->count();
        $overall = $total > 0 ? ($categoryStatuses->sum('ProgressPercent') / $total) : 0.0;

        return [
            'total_categories' => $total,
            'approved_categories' => $approved,
            'rejected_categories' => $rejected,
            'submitted_categories' => $submitted,
            'under_review_categories' => $underReview,
            'pending_categories' => $pending,
            'overall_progress' => round($overall, 2),
        ];
    }
}
