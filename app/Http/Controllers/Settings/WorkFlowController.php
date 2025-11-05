<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Settings\WorkFlowRequest;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Settings\WorkFlow;
use App\Models\Settings\WorkFlowStage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        return view('settings.approvals.sections', compact('workFlowGroups', 'sourceOptions', 'tableToAlias'));
    }

    public function create()
    {
        // Not used
    }

    /**
     * Store a newly created workflow and auto-detect the module
     */
    public function store(WorkFlowRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        try {
            //  Resolve the selected document type (alias or model class)
            $selection = (string) ($validated['DocType'] ?? '');
            $tableName = $this->resolveSelectedToTable($selection);

            if (!$tableName) {
                throw new \InvalidArgumentException('Unrecognized model selection: ' . $selection);
            }

            // Automatically detect the module linked to the document type
            $moduleId = DB::table('t_ModuleSources')
                ->where('DocumentType', $tableName)
                ->value('ModuleID');

            if (!$moduleId) {
                Log::warning("No module found for document type: {$tableName}");
            }

            //  Create the workflow
            $workFlow = WorkFlow::create([
                'Name' => $validated['Name'],
                'Description' => $validated['Description'],
                'Source' => $tableName,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
                'CreatedOn' => now(),
            ]);

            //  Optionally log the module linkage
            Log::info('Workflow created successfully.', [
                'workflow_id' => $workFlow->id,
                'source' => $tableName,
                'module_id' => $moduleId,
                'created_by' => Auth::id(),
            ]);

            // (Optional) You can store module info in a pivot/log table if needed
            // DB::table('t_WorkflowModules')->insert([
            //     'WorkflowId' => $workFlow->id,
            //     'ModuleID' => $moduleId,
            // ]);

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
                'error' => 'Failed to create approval stage: ' . $e->getMessage()
            ]);
        }

        return redirect()->back()->with('success', 'Approval workflow created successfully.');
    }

    /**
     * Update existing workflow
     */
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

            // Auto-detect module again on update
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
                'workflow_id' => $workFlow->id,
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
        $approval = WorkFlow::findOrFail($id);
        $sourceOptions = array_flip(Relation::morphMap());
        $approvalTypes = DB::table('t_WorkFlowTypes')->get();
        $permissions = DB::table('t_Permissions')->get();
        $workflowLimits = DB::table('t_WorkflowLimits')->select('Id', 'Source')->get();

        $stages = WorkFlowStage::where('WorkFlowId', $id)->with(['workflow'])->get();

        return view('settings.approvals.show', compact('approval', 'sourceOptions', 'permissions', 'approvalTypes', 'workflowLimits', 'stages'));
    }

    public function destroy(string $id)
    {
        try {
            $workFlow = WorkFlow::findOrFail($id);
            $workFlow->delete();

            return redirect()->route('settings.workflows.index')->with('success', 'Approval workflow deleted.');
        } catch (\Throwable $e) {
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
}
