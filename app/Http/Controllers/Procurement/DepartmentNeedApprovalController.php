<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Services\Core\WorkflowActionService;
use App\Exceptions\ErroredException;
use App\Services\Procurement\ProcurementPlan\DepartmentNeedsWorkflow;
use App\Http\Controllers\Controller;
use App\Models\Procurement\DepartmentNeed;
use App\Services\Workflow\ApprovalWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Exception;

class DepartmentNeedApprovalController extends Controller
{
    protected ApprovalWorkflow $workflow;

    public function __construct(ApprovalWorkflow $workflow)
    {
        $this->workflow = $workflow;  // Injected with codeId via service container
    }


    /**
     * Display a listing of department needs pending approval.
     */
    public function index()
    {
        $this->authorize('viewAny', DepartmentNeed::class);
        $NeedsApprovalviews = DepartmentNeed::with('creator')->where('Status', DepartmentNeedsEnum::Pending)->get();
        return view('procurement.procurementplan.departmentneeds.approval.index', compact('NeedsApprovalviews'));
    }

    /**
     * Display the specified department need with approval options (maker-checker aware).
     */
    public function show(DepartmentNeed $department_need)
    {
        $this->authorize('view', $department_need);

    // Load relations
    $need = $department_need->load(['item.category', 'item.uom', 'creator']);

    // Current user
    $user = Auth::user();

    // Maker-checker: can this user approve?
    $canApprove = $this->workflow->canApproveModel($need, $user);

    Log::info("Can approve for user {$user->Id}: " . ($canApprove ? 'Yes' : 'No'));

    return view('procurement.procurementplan.departmentneeds.approval.show', [
        'need'       => $need,
        'canApprove' => $canApprove,
        'history'    => $this->workflow->historyForModel($need),
    ]);
    }

    /**
     * Approve the specified department need (maker-checker enforced).
     */
    public function update(Request $request, DepartmentNeed $department_need): RedirectResponse
    {
        $departmentNeed = $department_need;

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
                $workflow = app(DepartmentNeedsWorkflow::class);

                // Submit then approve using the new unified workflow service
                // $workflow->submit($departmentNeed, $actor, 'Submitted for approval');
                $workflow->approve($departmentNeed, $actor, 'Approved');
            });
        } catch (\App\Exceptions\ErroredException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        } catch (\Throwable | Exception $e) {
            Log::error('Error approving department need: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Unexpected error, try again later.');
        }

        return redirect()
            ->route('department-need-approval.index')
            ->with('success', 'Department need submitted & approved successfully.');
        $this->authorize('approve', $departmentNeed);

        $user = Auth::user();

        // Maker-checker pre-check: Ensure user can approve (has pending, not submitter)
        if (!$this->workflow->canApproveModel($departmentNeed, $user)) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to approve this need.']);
        }

        $lock = Cache::lock('approve-DepartmentNeeds-' . $departmentNeed->NeedID, 5);
        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Department Needs has been approved, or another user is working on it.');
        }

        try {
            DB::transaction(function () use ($departmentNeed, $user) {
                // Only approve (no need to submit again, as pendings are already created)
                $this->workflow->approve($departmentNeed, $user, DepartmentNeedsEnum::Approved, 'Approved');
            });
        } catch (ErroredException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable | Exception $e) {
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

        // Maker-checker pre-check: Ensure user can reject (has pending, not submitter)
        if (!$this->workflow->canApproveModel($departmentNeeds, $user)) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to reject this need.']);
        }

        $data = $request->validate([
            'reject_reason' => ['required', 'string', 'min:15', 'max:2000'],  // Matches the view (update view if needed)
        ]);

        try {
            DB::transaction(function () use ($departmentNeeds, $user, $data) {
                $this->workflow->reject($departmentNeeds, $user, DepartmentNeedsEnum::Rejected, $data['reject_reason']);
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
