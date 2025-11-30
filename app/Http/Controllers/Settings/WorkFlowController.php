<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Settings\WorkFlowRequest;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Settings\WorkFlow;
use App\Models\Core\Approval\WorkflowStage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\Core\ModulesEnum;

class WorkFlowController extends Controller
{
    public function index()
    {
        $morphMap = Relation::morphMap();

        $workFlowGroups = WorkFlow::all();

        $sourceOptions = array_flip($morphMap);

        $tableToAlias = collect($morphMap)
            ->mapWithKeys(function ($class, $alias) {
                try {
                    $instance = app($class);
                    if ($instance instanceof Model) {
                        return [$instance->getTable() => $alias];
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
                return [];
            })
            ->toArray();

        // Fetch all modules
        $allModules = DB::table('t_Modules')
            ->select('ModuleID', 'Name', 'ParentID')
            ->orderBy('Name')
            ->get();

        // Build tree
        $moduleTree = [];
        foreach ($allModules as $m) {
            $moduleTree[$m->ParentID ?? 0][] = $m;
        }

        // Flatten recursively
        $modules = collect();
        $flatten = function ($parentId = 0, $depth = 0) use (&$flatten, $moduleTree, &$modules) {
            if (isset($moduleTree[$parentId])) {
                foreach ($moduleTree[$parentId] as $module) {
                    $module->indentation = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth);
                    $modules->push($module);
                    $flatten($module->ModuleID, $depth + 1);
                }
            }
        };

        // Start with root items (ParentID is null or 0)
        // Note: DB dump showed ParentID as null for roots, but let's handle 0 just in case
        $flatten(0);
        // Also handle null explicitly if 0 didn't catch them (depends on how we keyed the array)
        if (isset($moduleTree[''])) { // null key often casts to empty string in PHP arrays or needs special handling
            // Actually, let's be safer with the keying
        }

        // Re-doing the tree building to be safer with nulls
        $moduleTree = [];
        foreach ($allModules as $m) {
            $pid = $m->ParentID ?? 'root';
            $moduleTree[$pid][] = $m;
        }

        $modules = collect();
        $flatten = function ($parentId = 'root', $depth = 0) use (&$flatten, $moduleTree, &$modules) {
            if (isset($moduleTree[$parentId])) {
                // Sort by Name within the level
                usort($moduleTree[$parentId], fn($a, $b) => strcmp($a->Name, $b->Name));

                foreach ($moduleTree[$parentId] as $module) {
                    $module->indentation = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth);
                    $modules->push($module);
                    $flatten($module->ModuleID, $depth + 1);
                }
            }
        };
        $flatten('root');

        return view('settings.approvals.sections', compact('workFlowGroups', 'sourceOptions', 'tableToAlias', 'modules'));
    }

    public function create()
    {
        // Not used
    }

    public function store(WorkFlowRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        try {
            $selection = (string) ($validated['DocType'] ?? '');
            $tableName = $this->resolveSelectedToTable($selection);

            if (!$tableName) {
                throw new \InvalidArgumentException('Unrecognized model selection: ' . $selection);
            }

            $moduleId = DB::table('t_ModuleSources')
                ->where('DocumentType', $tableName)
                ->value('ModuleID');

            if (!$moduleId) {
                if ($request->filled('ModuleID')) {
                    $moduleId = $request->input('ModuleID');
                    DB::table('t_ModuleSources')->insert([
                        'DocumentType' => $tableName,
                        'ModuleID' => $moduleId,
                        'CreatedBy' => Auth::id(),
                        'CreatedOn' => now(),
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);
                    Log::info("Inserted new module source mapping for {$tableName} -> {$moduleId}");
                } else {
                    Log::warning("No module found for document type: {$tableName}");
                }
            }

            $workFlow = WorkFlow::create([
                'Name' => $validated['Name'],
                'Description' => $validated['Description'],
                'Source' => $tableName,
                'FinalStage' => '', // Initialize as empty string (column doesn't allow nulls)
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
                'CreatedOn' => now(),
            ]);

            Log::info('Workflow created successfully.', [
                'workflow_id' => $workFlow->Id,
                'source' => $tableName,
                'module_id' => $moduleId,
                'created_by' => Auth::id(),
            ]);

            activity()->causedBy($user)
                ->performedOn($workFlow)
                ->event('create')
                ->log('Created approval workflow: ' . $validated['Name']);
        } catch (\Exception $e) {
            Log::error('Failed to create workflow.', [
                'error_message' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()->withErrors([
                'error' => 'Failed to create approval workflow: ' . $e->getMessage()
            ]);
        }

        return redirect()->back()->with('success', 'Approval workflow created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'Name' => ['required', 'string', 'max:255'],
            'Description' => ['required', 'string', 'max:1000'],
            'DocType' => ['required', 'string'],
        ]);

        try {
            $workFlow = WorkFlow::findOrFail($id);
            $selection = (string) ($validated['DocType'] ?? '');
            $tableName = $this->resolveSelectedToTable($selection);

            if (!$tableName) {
                throw new \InvalidArgumentException('Unrecognized model selection: ' . $selection);
            }

            //auto-detect module

            $moduleId = DB::table('t_ModuleSources')
                ->where('DocumentType', $tableName)
                ->value('ModuleID');

            $workFlow->update([
                'Name' => $validated['Name'],
                'Description' => $validated['Description'],
                'Source' => $tableName,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            Log::info('Workflow updated successfully.', [
                'workflow_id' => $workFlow->Id,
                'module_id' => $moduleId,
                'updated_by' => Auth::id(),
            ]);

            return redirect()->back()->with('success', 'Approval workflow updated.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors([
                'error' => 'Failed to update workflow: ' . $e->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        // Force fresh data from database
        $approval = WorkFlow::findOrFail($id);
        $approval->refresh(); // Ensure we have the latest data

        $sourceOptions = array_flip(Relation::morphMap());
        $approvalTypes = DB::table('t_WorkFlowTypes')->get();
        $permissions = DB::table('t_Permissions')->get();
        $workflowLimits = DB::table('t_WorkflowLimits')->select('Id', 'WorkFlowStageId')->get();

        // Get stages with fresh data
        $stages = WorkflowStage::where('WorkFlowId', $id)
            ->with(['type_name', 'workflow'])
            ->orderBy('Order')
            ->get();

        // Force disable caching
        return response()
            ->view('settings.approvals.show', compact('approval', 'sourceOptions', 'permissions', 'approvalTypes', 'workflowLimits', 'stages'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $workFlow = WorkFlow::findOrFail($id);
            $workflowName = $workFlow->Name;

            // Count stages before deletion
            $stagesCount = WorkflowStage::where('WorkFlowId', $id)->count();

            // Delete all associated stages first (explicit deletion)
            WorkflowStage::where('WorkFlowId', $id)->delete();

            Log::info('Deleted workflow stages', [
                'workflow_id' => $id,
                'workflow_name' => $workflowName,
                'stages_deleted' => $stagesCount,
            ]);

            // Now delete the workflow
            $workFlow->delete();

            // Log activity
            activity()->performedOn($workFlow)
                ->event('delete')
                ->log("Deleted workflow '{$workflowName}' and {$stagesCount} stage(s)");

            DB::commit();

            return redirect()->route('settings.workflows.index')
                ->with('success', "Workflow '{$workflowName}' and {$stagesCount} associated stage(s) deleted successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to delete workflow', [
                'workflow_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    private function resolveSelectedToTable(?string $selection): ?string
    {
        if (!$selection) return null;

        $morphMap = Relation::morphMap();
        $class = $morphMap[$selection] ?? $selection;

        if (!class_exists($class)) return null;

        $instance = app($class);
        if (!$instance instanceof Model) return null;

        return $instance->getTable();
    }


    public function getState($id)
    {
        try {
            $workflow = WorkFlow::findOrFail($id);
            $workflow->refresh();

            // Get all stages with proper relationships
            $stagesCollection = WorkflowStage::where('WorkFlowId', $id)
                ->with(['type_name', 'workflow'])
                ->orderBy('Order')
                ->get();

            // Map stages with proper final stage detection
            $stages = $stagesCollection->map(function ($stage) use ($workflow) {
                // Check if this stage is marked as final stage
                $isFinalStage = ($stage->StageName === $workflow->FinalStage);


                return [
                    'Id' => $stage->Id,
                    'StageName' => $stage->StageName,
                    'Order' => $stage->Order,
                    'type' => $stage->type_name ? [
                        'TypeID' => $stage->type_name->TypeID,
                        'Name' => $stage->type_name->Name,
                    ] : null,
                    'role_name' => $stage->role_name ?? '-',
                    'MaxAmount' => $stage->MaxAmount ?? '-',
                    'Count' => $stage->Count ?? null,
                    'IsFinalStage' => $isFinalStage,
                    'FinalStageName' => $workflow->FinalStage,
                    'EscalationLimit' => $stage->EscalationLimit,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'workflow' => [
                    'Id' => $workflow->Id,
                    'Name' => $workflow->Name,
                    'IsFinalStage' => $workflow->FinalStage ? true : false,
                    'Description' => $workflow->Description,
                    'Source' => $workflow->Source,
                ],
                'stages' => $stages,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch workflow state', [
                'workflow_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch workflow state: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Get approvers for a specific stage
     */
    public function getApprovers($stageId)
    {
        $stage = \App\Models\Core\Approval\WorkflowStage::find($stageId);
        if (!$stage || !$stage->PermissionId) {
            return response()->json(['users' => []]);
        }

        // Use the SP logic via DB select
        $users = DB::select('SELECT * FROM f_getUserWithPermission(?)', [$stage->PermissionId]);

        return response()->json(['users' => $users]);
    }
}
