<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ApproveOrderRequest;
use App\Http\Requests\Procurement\Requisition\ApproveRequisitionRequest;
use App\Http\Requests\Procurement\Requisition\RequisitionRequest;
use App\Models\Procurement\Order;
use App\Models\Procurement\Requisitions;
use App\Models\HRM\Employee;
use App\Services\Core\DocumentApprovalService;
use App\Services\Procurement\Requisition\RequisitionItemService;
use App\Services\Procurement\Requisition\RequisitionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Enums\ProcurementPlanStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RequisitionsController extends Controller
{
    public function __construct(protected RequisitionService $service, protected RequisitionItemService $requisitionItemService, protected DocumentApprovalService $documentApprovalService)
    {
        $this->middleware('ajax')->except(['index', 'show', 'create', 'approval', 'approve']);
//         $this->authorizeResource(Requisitions::class); // Uncomment if using authorization
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
//        return view('procurement.requisitions.approval');
        try {
            $details = $this->service->fetchRequisition();
            return view('procurement.requisitions.approval', compact('details'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch items: ' . $e->getMessage());
        }
    }

    public function approvalList(){
        return view("procurement.requisitions.approval");
//        try {
//            $details = $this->service->fetchRequisition();
//            return view('procurement.requisition.approval', compact('details'));
//        } catch (\Exception $e) {
//            return redirect()->back()->with('error', 'Failed to fetch items: ' . $e->getMessage());
//        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $user = Auth::user();

            // Fetch employee with branch and department
            $employee = Employee::where('Id', $user->EmployeeId)
                ->with('branch', 'department') // if relations exist
                ->first();

            $approvedStatusId = DB::table('t_CodeDetails')
                ->where('CodeID', 'RequisitionStatus')
                ->where('Value', 'Ap')
                ->value('ID');
          
            $branchId = session('LoginBranchId');
            $departmentId = $employee?->DepartmentId ?? null;

            $departmentName = null;

            if ($departmentId) {
                $departmentName = DB::table('t_Departments')->where('Id', $departmentId)->value('Name');
            }

            $details = $this->service->fetchRequisition();
            $procurementPlans = $this->service->fetchProcurementPlan();

            return view('procurement.requisitions.create', [
                'details' => $details ?? [],
                'branchId' => $branchId,
                'departmentId' => $departmentId,
                'departmentName' => $departmentName,
                'approvedStatusId' => $approvedStatusId,
                'procurementPlans' => $procurementPlans ?? [],
            ]);
        } catch (\Exception $e) {
            Log::error('Requisition create failed: ' . $e->getMessage());

            return view('procurement.requisitions.create', [
                'details' => [],
                'branchId' => null,
                'departmentId' => null,
                'procurementPlans' => [],
            ])->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RequisitionRequest $request): JsonResponse
    {
//       dd($request->user());
        try {
            $validatedData = $request->validated();


            $actor = $request->user();
            if (!$actor) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }


            $requisitionAdd = $this->service->addRequisition(
                $validatedData['Branch'],
                $validatedData['Department'],
                $validatedData['Remarks'],
                $validatedData['ProcurementPlan'],
                $actor
            );

            if ($requisitionAdd['status'] === 'success') {
                return response()->json([
                    'message' => $requisitionAdd['message'],
                    'route' =>route('requisition.create')
                ], 200);
            }

            // Log failure with details
            Log::error('Failed to create requisition.', [
                'input' => $validatedData,
                'user_id' => $actor->id ?? null,
                'service_response' => $requisitionAdd,
            ]);

            return response()->json([
                'message' => $requisitionAdd['message'],
                'error' => $requisitionAdd['error'] ?? 'Unknown error'
            ], 500);

        } catch (\Throwable $e) {
            Log::error('Exception occurred while creating requisition.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create requisition',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approval($id)
    {
        try {
            $requisition = Requisitions::findOrFail($id);
            $this->authorize('view', $requisition); // Authorize the order object itself

            $requisitionInfo = $this->service->getRelatedRequisition($id);
            $requisitionlineInfo = $this->requisitionItemService->getRequisitionRelatedItems($id);
            $approvalStatus = $this->getApprovalStatus('purchase_requisition', $id);

            return view('procurement.requisitions.approval', compact('requisitionInfo', 'requisitionlineInfo', 'approvalStatus'));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning("Unauthorized access attempt to view Requisition ID: {$id} by user ID: " . auth()->id());
            return redirect()->back()->with('error', 'Unauthorized access.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Requisition ID {$id} not found. Exception: " . $e->getMessage());
            return redirect()->back()->with('error', 'Requisition not found.');
        } catch (\Exception $e) {
            Log::error("Failed to fetch Requisition ID {$id}. Exception: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to fetch Requisition.');
        }
    }

    public function approve(ApproveRequisitionRequest $requisitionRequest, $id)
    {
        // Allow rejection even if no lines; enforce line check only for approval action
        if ($requisitionRequest->input('action') === 'approve') {
            $hasLines = DB::table('t_RequisitionLines')->where('RequisitionId', $id)->exists();
            if (!$hasLines) {
                return back()->with('error', 'Cannot approve a requisition without items.');
            }
        }

        return $this->documentApprovalService->approve($requisitionRequest, $id);
    }

    private function getApprovalStatus(string $docType, int $documentId)
    {
        $permissionId = DB::table('t_ApprovalGroups')
            ->where('DocType', $docType)
            ->value('Permission');

        if (!$permissionId) return [];

        $approverUsers = DB::table('t_ModelRoles as mr')
            ->join('t_RolePermissions as rp', 'mr.role_id', '=', 'rp.role_id')
            ->join('t_Users as u', 'mr.model_id', '=', 'u.Id')
            ->where('mr.model_type', 'UserID')
            ->where('rp.permission_id', $permissionId)
            ->select('u.Id', 'u.Name')
            ->distinct()
            ->get();

        $approvedUserIds = DB::table('t_Approvals')
            ->where('DocType', $docType)
            ->where('DocumentId', $documentId)
            ->where('Status', 'approved')
            ->pluck('UserId')
            ->toArray();

        return $approverUsers->map(function ($user) use ($approvedUserIds) {
            return [
                'name' => $user->Name,
                'approved' => in_array($user->Id, $approvedUserIds),
            ];
        });
    }

    public function getPlanDetails($id)
    {
        $branchIds = DB::table('t_PlanLineItem')
            ->where('PlanID', $id)
            ->distinct()
            ->pluck('BranchID');

        $departmentIds = DB::table('t_PlanLineItem')
            ->where('PlanID', $id)
            ->distinct()
            ->pluck('DepartmentID');

        $branches = DB::table('t_Branches')
            ->whereIn('BranchCode', $branchIds)
            ->select('Id', 'Name')
            ->get();

        $departments = DB::table('t_Departments')
            ->whereIn('DepartmentCode', $departmentIds)
            ->select('Id', 'Name')
            ->get();

        return response()->json([
            'branches' => $branches,
            'departments' => $departments,
        ]);
    }
    
    public function getRequisitions(): JsonResponse{
        try{
            $details = $this->service->fetchRequisition();
            return response()->json([
                'success' => true,
                'data' => $details,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch items.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('view', Requisitions::query()->findOrFail($id));

        try {

            $details = $this->requisitionItemService->getRequisitionRelatedItems($id);
            $types = $this->service->getItemTypes();
            return view('procurement.requisitions.show', compact('details', 'types'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch items: ' . $e->getMessage());
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
