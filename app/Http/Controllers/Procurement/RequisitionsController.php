<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Requisition\RequisitionRequest;
use App\Models\Procurement\Requisitions;
use App\Models\HRM\Employee;
use App\Services\Procurement\Requisition\RequisitionItemService;
use App\Services\Procurement\Requisition\RequisitionService;
use App\Services\Workflow\ApprovalWorkflow;
use App\Enums\WorkflowStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RequisitionsController extends Controller
{
    protected ApprovalWorkflow $workflow;


    public function __construct(
        protected RequisitionService $service,
        protected RequisitionItemService $requisitionItemService
    ) {
        $this->middleware('ajax')->except(['index', 'show', 'create', 'approval', 'approve','submit']);
        
        // Initialize workflow - IMPORTANT: Use DocStatus column, not StatusID
        $this->workflow = new ApprovalWorkflow('RequisitionStatus', 'DocStatus');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Requisitions::class);
        
        try {
            $user = Auth::user();
            $employee = Employee::where('Id', $user->EmployeeId)
                ->with('branch', 'department')
                ->first();

            $branchId = session('LoginBranchId');
            $departmentId = $employee?->DepartmentId ?? null;
            $departmentName = null;

            if ($departmentId) {
                $departmentName = DB::table('t_Departments')
                    ->where('Id', $departmentId)
                    ->value('Name');
            }

            $details = $this->service->fetchRequisition();
            $procurementPlans = $this->service->fetchProcurementPlan();

            return view('procurement.requisitions.create', compact('details', 'procurementPlans', 'branchId', 'departmentId', 'departmentName'));
        } catch (\Exception $e) {
            Log::error('Failed to fetch requisitions: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch requisitions: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Requisitions::class);
        
        try {
            $user = Auth::user();
            $employee = Employee::where('Id', $user->EmployeeId)
                ->with('branch', 'department')
                ->first();

            $branchId = session('LoginBranchId');
            $departmentId = $employee?->DepartmentId ?? null;
            $departmentName = null;

            if ($departmentId) {
                $departmentName = DB::table('t_Departments')
                    ->where('Id', $departmentId)
                    ->value('Name');
            }

            $details = $this->service->fetchRequisition();
            $procurementPlans = $this->service->fetchProcurementPlan();

            return view('procurement.requisitions.create', [
                'details' => $details ?? [],
                'branchId' => $branchId,
                'departmentId' => $departmentId,
                'departmentName' => $departmentName,
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
     *
     */
    public function store(RequisitionRequest $request): JsonResponse
    {
        $this->authorize('create', Requisitions::class);
        
        try {
            $validatedData = $request->validated();
            $actor = $request->user();

            if (!$actor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $result = $this->service->addRequisition(
                $validatedData['Branch'],
                $validatedData['Department'],
                $validatedData['Remarks'],
                $validatedData['ProcurementPlan'] ?? null,
                $actor
            );

            if ($result['status'] === 'success') {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'route' => route('requisition.show', ['requisition' => $result['requisition_id']]),
                    'requisition_id' => $result['requisition_id'] ?? null
                ], 200);
            }

            Log::error('Failed to create requisition.', [
                'input' => $validatedData,
                'user_id' => $actor->Id ?? null,
                'service_response' => $result,
            ]);

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to create requisition. Please try again.'
            ], 500);
            
        } catch (\Throwable $e) {
            Log::error('Exception occurred while creating requisition.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create requisition. Please try again later.'
            ], 500);
        }
    }

    /**
     * Submit requisition for approval workflow
     * This is called AFTER requisition is created and items are added
     */
    public function submit(Request $request, $id)
    {
        try {
            $requisition = Requisitions::findOrFail($id);
            $this->authorize('update', $requisition);

            // Check if requisition has items
            $itemCount = DB::table('t_RequisitionLines')
                ->where('RequisitionId', $id)
                ->whereNull('DeletedOn')
                ->count();

            if ($itemCount === 0) {
                return back()->with('error', 'Cannot submit requisition without items. Please add at least one item.');
            }

            // Get current DocStatus
            $currentDocStatus = strtoupper(trim($requisition->DocStatus ?? 'DR'));
            
            Log::info("Submitting requisition", [
                'requisition_id' => $id,
                'current_doc_status' => $currentDocStatus,
                'item_count' => $itemCount
            ]);
            
            // Check if already in workflow (PE = Pending, AP = Approved, RE = Rejected)
            if (in_array($currentDocStatus, ['PE', 'AP', 'RE'])) {
                $statusMap = [
                    'PE' => 'pending approval',
                    'AP' => 'approved',
                    'RE' => 'rejected'
                ];
                $statusText = $statusMap[$currentDocStatus] ?? 'processed';
                
                return back()->with('warning', "This requisition has already been {$statusText}.");
            }

            // Only DR (Draft) status can be submitted
            if ($currentDocStatus !== 'DR') {
                return back()->with('error', 'Only draft requisitions can be submitted for approval.');
            }

            DB::beginTransaction();
            try {
                $user = Auth::user();
                
                // Submit to workflow system
                $this->workflow->submit(
                    $requisition,
                    $user,
                    WorkflowStatus::Pending,
                    $request->input('remarks', 'Submitted for approval')
                );
                
                // Update DocStatus to Pending
                DB::table('t_Requisitions')
                    ->where('Id', $requisition->Id)
                    ->update([
                        'DocStatus' => 'PE', // Pending
                        'ModifiedBy' => $user->Id,
                        'ModifiedOn' => now()
                    ]);
                
                DB::commit();
                
                Log::info("Requisition submitted successfully", [
                    'requisition_id' => $id,
                    'user_id' => $user->Id
                ]);
                
                return redirect()
                    ->route('requisition.show', $requisition->Id)
                    ->with('success', 'Requisition submitted for approval successfully.');
                    
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning("Unauthorized submission attempt for Requisition ID: {$id}");
            return back()->with('error', 'You are not authorized to submit this requisition.');
        } catch (\Exception $e) {
            Log::error("Failed to submit Requisition ID {$id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to submit requisition: ' . $e->getMessage());
        }
    }

    /**
     * Display approval page for a specific requisition
     */
    public function approval($id)
    {
        try {
            $requisition = Requisitions::findOrFail($id);
            $this->authorize('view', $requisition);

            $requisitionInfo = $this->service->getRelatedRequisition($id);
            $requisitionlineInfo = $this->requisitionItemService->getRequisitionRelatedItems($id);
            
            // Get workflow status
            $approvalStatus = $this->workflow->getWorkflowStatus(
                $requisition->getMorphClass(),
                $requisition->getKey()
            );

            // Check if current user can approve
            $canApprove = $this->workflow->canApprove(
                $requisition->getMorphClass(),
                $requisition->getKey(),
                Auth::user()
            );

            return view('procurement.requisitions.approval', compact(
                'requisitionInfo',
                'requisitionlineInfo',
                'approvalStatus',
                'canApprove'
            ));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning("Unauthorized access attempt to view Requisition ID: {$id}");
            return redirect()->route('requisition.index')->with('error', 'Unauthorized access.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Requisition ID {$id} not found.");
            return redirect()->route('requisition.index')->with('error', 'Requisition not found.');
        } catch (\Exception $e) {
            Log::error("Failed to fetch Requisition ID {$id}. Exception: " . $e->getMessage());
            return redirect()->route('requisition.index')->with('error', 'Failed to fetch requisition.');
        }
    }

    /**
     * Process approval/rejection/return of a requisition
     */
    public function approve(Request $request, $id)
    {
        try {
            $requisition = Requisitions::findOrFail($id);
            $this->authorize('approve', $requisition);

            $action = strtolower($request->input('action'));
            $remarks = (string) ($request->input('comments') ?? $request->input('remarks') ?? $request->input('rejection_reason') ?? '');

            // Validate action
            if (!in_array($action, ['approve', 'reject', 'return'])) {
                return back()->with('error', 'Invalid action specified.');
            }

            // Validate remarks for rejection
            if ($action === 'reject' && empty($remarks)) {
                return back()->with('error', 'Rejection reason is required.');
            }

            DB::beginTransaction();
            try {
                $user = Auth::user();
                
                switch ($action) {
                    case 'approve':
                        $this->workflow->approve(
                            $requisition,
                            $user,
                            WorkflowStatus::Approved,
                            $remarks,
                            'DocStatus' // Explicitly pass the column name
                        );
                        
                        // Update DocStatus to Approved
                        DB::table('t_Requisitions')
                            ->where('Id', $requisition->Id)
                            ->update([
                                'DocStatus' => 'AP', // Approved
                                'ModifiedBy' => $user->Id,
                                'ModifiedOn' => now()
                            ]);
                        break;
                        
                    case 'reject':
                        $this->workflow->reject(
                            $requisition,
                            $user,
                            WorkflowStatus::Rejected,
                            $remarks,
                            'DocStatus' // Explicitly pass the column name
                        );
                        
                        // Update DocStatus to Rejected
                        DB::table('t_Requisitions')
                            ->where('Id', $requisition->Id)
                            ->update([
                                'DocStatus' => 'RE', // Rejected
                                'ModifiedBy' => $user->Id,
                                'ModifiedOn' => now()
                            ]);
                        break;
                        
                    case 'return':
                        $this->workflow->reject(
                            $requisition,
                            $user,
                            WorkflowStatus::Deferred,
                            $remarks,
                            'DocStatus' // Explicitly pass the column name
                        );
                        
                        // Update DocStatus to Draft (returned for revision)
                        DB::table('t_Requisitions')
                            ->where('Id', $requisition->Id)
                            ->update([
                                'DocStatus' => 'DR', // Draft
                                'ModifiedBy' => $user->Id,
                                'ModifiedOn' => now()
                            ]);
                        break;
                }
                
                DB::commit();
                
                $actionText = ucfirst($action) . 'd';
                return redirect()->route('requisition.create')
                    ->with('success', "Requisition {$actionText} successfully.");
                    
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
                
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning("Unauthorized approval attempt for Requisition ID: {$id}");
            return back()->with('error', 'You are not authorized to perform this action.');
        } catch (\Exception $e) {
            Log::error("Workflow action failed for Requisition ID {$id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to process requisition: ' . $e->getMessage());
        }
    }

    /**
     * Get plan details for a procurement plan
     */
    public function getPlanDetails($id)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Failed to get plan details: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch plan details'
            ], 500);
        }
    }

    /**
     * Get all requisitions as JSON
     */
    public function getRequisitions(): JsonResponse
    {
        try {
            $details = $this->service->fetchRequisition();
            return response()->json([
                'success' => true,
                'data' => $details,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch requisitions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch requisitions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $requisition = Requisitions::findOrFail($id);
            $this->authorize('view', $requisition);

            $details = $this->requisitionItemService->getRequisitionRelatedItems($id);
            $types = $this->service->getItemTypes();
            $requisitionInfo = $this->service->getRelatedRequisition($id);
            
            return view('procurement.requisitions.show', compact('details', 'types', 'id', 'requisitionInfo'));
        } catch (\Exception $e) {
            Log::error('Failed to show requisition: ' . $e->getMessage());
            return redirect()->route('requisition.index')->with('error', 'Failed to fetch requisition: ' . $e->getMessage());
        }
    }
}