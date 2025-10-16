<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use Exception;
use App\Models\Procurement\DepartmentNeed;
use App\Services\Procurement\ProcurementPlan\DepartmentNeedsApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class DepartmentNeedApprovalController extends Controller
{
    //
    public function index()
    {
        $NeedsApprovalviews = DepartmentNeed::with('creator')->where('Status', DepartmentNeedsEnum::Pending)->get();
        return view('procurement.procurementplan.departmentneeds.approval.index', compact('NeedsApprovalviews'));
    }

    public function show($DepartmentNeedId)
    {
        $need = DepartmentNeed::with(['item.category', 'item.uom', 'creator'])->findOrFail($DepartmentNeedId);
        return view('procurement.procurementplan.departmentneeds.approval.show', compact('need'));
    }

    //approve

    public function update(Request $request, $DepartmentNeedID): RedirectResponse
    {
         $departmentNeed = DepartmentNeed::query()->findOrFail($DepartmentNeedID);

    $this->authorize('approve', $departmentNeed);

    $lock = Cache::lock('approve-DepartmentNeeds-' . $departmentNeed->NeedID, 5);
    if (!$lock->get()) {
        return redirect()
            ->back()
            ->with('error', 'Department Needs has been approved, or another user is working on it.');
    }

    $actor = $request->user();

    try {
        DB::transaction(static function () use ($departmentNeed, $actor) {
            $approvalService = new \App\Services\Procurement\ProcurementPlan\DepartmentNeedsApprovalService($departmentNeed);

            //  Submit the need first
            $approvalService->submit($actor);

            //  Approve the need immediately after submission
            $approvalService->workflowApprove($actor);
        });
    } catch (\App\Exceptions\ErroredException $e) {
        return redirect()
            ->back()
            ->with('error', $e->getMessage());
    } catch (\Throwable|Exception $e) {
        Log::error('Error approving department need: ' . $e->getMessage());
        return redirect()
            ->back()
            ->with('error', 'Unexpected error, try again later.');
    }

    return redirect()
        ->route('department-need-approval.index')
        ->with('success', 'Department need submitted & approved successfully.');
    }


    //reject

    public function destroy(Request $request, $DepartmentNeedID): RedirectResponse
    {
        $departmentNeeds = DepartmentNeed::findOrFail($DepartmentNeedID);
        $this->authorize('destroy', $departmentNeeds);

        $actor = $request->user();
        $data = $request->validate([
            'Department_needs_reject_reason' => ['required', 'string', 'min:15', 'max:2000'],
        ]);

        try {
            DB::transaction(static function () use ($departmentNeeds, $actor, $data) {
                (new DepartmentNeedsApprovalService($departmentNeeds))
                    ->workflowReject($actor, $data['Department_needs_reject_reason']);
            });
        } catch (ErroredException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Error reject department needs failed: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Unexpected error, try again later.');
        }

        return redirect()
            ->route('department-need-approval.index')
            ->with('success', 'Department needs rejected successfully.');
    }

}

