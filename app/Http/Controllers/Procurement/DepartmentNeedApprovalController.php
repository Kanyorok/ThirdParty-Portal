<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use Exception;
use App\Models\Procurement\DepartmentNeed;
use App\Services\Procurement\DepartmentNeedsWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class DepartmentNeedApprovalController extends Controller
{
    /**
     * Display a listing of department needs pending approval.
     */
    public function index()
    {
        $NeedsApprovalviews = DepartmentNeed::with('creator')->where('Status', DepartmentNeedsEnum::Pending)->get();
        return view('procurement.procurementplan.departmentneeds.approval.index', compact('NeedsApprovalviews'));
    }

    /**
     * Display the specified department need with approval options (maker-checker aware).
     */
    public function show(DepartmentNeed $department_need)
    {
        $need = DepartmentNeed::with(['item.category', 'item.uom', 'creator'])->findOrFail($department_need->Id);
        
        // Maker-checker: Check if the current user can approve (excludes submitter, requires pending)
        $user = Auth::user();
        $workflow = new DepartmentNeedsWorkflow();
        $canApprove = $workflow->canApproveNeed($need, $user);
        
        return view('procurement.procurementplan.departmentneeds.approval.show', [
            'need' => $need,
            'canApprove' => $canApprove,  // Pass to view for conditional buttons
            'history' => $workflow->historyForNeed($need),
        ]);
    }

    /**
     * Approve the specified department need (maker-checker enforced).
     */
    public function update(Request $request, DepartmentNeed $department_need): RedirectResponse
    {
        $departmentNeed = $department_need;
        $this->authorize('approve', $departmentNeed);
        
        $user = Auth::user();
        $workflow = new DepartmentNeedsWorkflow();
        
        // Maker-checker pre-check: Ensure user can approve (has pending, not submitter)
        if (!$workflow->canApproveNeed($departmentNeed, $user)) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to approve this need.']);
        }
        
        $lock = Cache::lock('approve-DepartmentNeeds-' . $departmentNeed->NeedID, 5);
        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Department Needs has been approved, or another user is working on it.');
        }
        
        try {
            DB::transaction(static function () use ($departmentNeed, $user) {
                $workflow = app(DepartmentNeedsWorkflow::class);
                
                // Only approve (no need to submit again, as pendings are already created)
                $workflow->approve($departmentNeed, $user, 'Approved');
            });
        } catch (ErroredException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable|Exception $e) {
            Log::error('Error approving department need: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unexpected error, try again later.');
        }
        
        return redirect()->route('department-need-approval.index')->with('success', 'Department need approved successfully.');
    }

    /**
     * Reject the specified department need (maker-checker enforced).
     */
    public function destroy(Request $request, DepartmentNeed $department_need): RedirectResponse
    {
        $departmentNeeds = $department_need;
        $this->authorize('destroy', $departmentNeeds);
        
        $user = Auth::user();
        $workflow = new DepartmentNeedsWorkflow();
        
        // Maker-checker pre-check: Ensure user can reject (has pending, not submitter)
        if (!$workflow->canApproveNeed($departmentNeeds, $user)) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to reject this need.']);
        }
        
        $data = $request->validate([
            'Department_needs_reject_reason' => ['required', 'string', 'min:15', 'max:2000'],
        ]);
        
        try {
            DB::transaction(static function () use ($departmentNeeds, $user, $data) {
                $workflow = app(DepartmentNeedsWorkflow::class);
                $workflow->reject($departmentNeeds, $user, $data['Department_needs_reject_reason']);
            });
        } catch (ErroredException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            Log::error('Error reject department needs failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unexpected error, try again later.');
        }
        
        return redirect()->route('department-need-approval.index')->with('success', 'Department needs rejected successfully.');
    }
}
