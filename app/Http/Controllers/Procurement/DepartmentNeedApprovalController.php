<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use Exception;
use App\Models\Procurement\DepartmentNeeds;
use App\Services\Procurement\ProcurementPlan\DepartmentNeedsApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentNeedApprovalController extends Controller
{
    //
    public function index()
    {
        $NeedsApprovalviews = DepartmentNeeds::with('creator')->where('Status', DepartmentNeedsEnum::Pending)->get();
        return view('procurement.procurementplan.departmentneeds.approval.index', compact('NeedsApprovalviews'));
    }

    public function show($Id)
    {
        $need = DepartmentNeeds::with(['item.category','item.uom', 'creator'])->findOrFail($Id);
        return view('procurement.procurementplan.departmentneeds.approval.show', compact('need'));
    }

    public function update(Request $request, $departmentNeed_ID): RedirectResponse
    {
        $departmentNeeds = DepartmentNeeds::query()->findOrFail($departmentNeed_ID);

        $this->authorize('approve', $departmentNeeds);

        $lock = Cache::lock('approve-DepartmentNeeds-' . $departmentNeeds->NeedID, 5);
        if (!$lock->get()) {
            return redirect()
                ->back()
                ->with('error', 'Department Needs has been approved, or another user is working on it.');
        }

        $actor = $request->user();

        try {
            DB::transaction(static function () use ($departmentNeeds, $actor) {
                (new DepartmentNeedsApprovalService($departmentNeeds))
                    ->workflowApprove($actor);
            });
        } catch (ErroredException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        } catch (\Throwable|Exception $e) {
            \Log::error('Error approve department needs failed: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Unexpected error, try again later.');
        }

        return redirect()
            ->route('department-need-approval.index')
            ->with('success', 'Department needs approved successfully.');
    }


public function destroy(Request $request, $departmentNeed_ID): RedirectResponse
{
    $departmentNeeds = DepartmentNeeds::findOrFail($departmentNeed_ID);
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

