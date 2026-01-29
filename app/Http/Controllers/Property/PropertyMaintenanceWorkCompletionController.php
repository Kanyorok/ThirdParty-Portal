<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Enums\Core\PostingEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\MaintenanceAndIssues\PropertyMaintenanceWorkCompletionRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\PropertyManagement\PropertyMaintenanceAssign;
use App\Models\PropertyManagement\PropertyMaintenanceWorkCompletion;
use App\Services\Property\MaintenanceAndIssues\PropertyMaintenanceWorkCompletionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PropertyMaintenanceWorkCompletionController extends Controller
{
    public function index()
    {
        $workCompletions = PropertyMaintenanceWorkCompletion::with('request', 'finalstatus')->get();

        return view('property.maintenanceandissues.workcompletion.index', compact('workCompletions'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceWorkCompletionCreate, PropertyMaintenanceWorkCompletion::class);
        $assignments = PropertyMaintenanceAssign::where('Status', '!=', PostingEnum::Completed)->with('request')->get();
        $finalstatus = CodeDetail::where('CodeID', 'FinalStatus')->get();

        return view('property.maintenanceandissues.workcompletion.create', compact('assignments', 'finalstatus'));
    }

    public function store(PropertyMaintenanceWorkCompletionRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceWorkCompletionCreate, PropertyMaintenanceWorkCompletion::class);

        $validated = $request->validated();

        $requestNumber = PropertyMaintenanceAssign::findOrFail($validated['RequestNumber']);
        $finalstatus = CodeDetail::findOrFail((int)$validated['FinalStatus']);

        $user = Auth::user();

        $uploadedFile = $request->file('Document')[0] ?? null;
        $workCompletion = PropertyMaintenanceWorkCompletionService::create(
            $requestNumber,
            $validated['CompletionDate'],
            $validated['WorkDoneSummary'],
            $validated['PartsUsed'] ?? '',
            $validated['Cost'] ?? '0',
            $finalstatus,
            Auth::user(),
            $uploadedFile
        );

        if ($request->hasFile('Document')) {
            foreach (array_slice($request->file('Document'), 1) as $uploadedFile) {
                $workCompletion->propertyMaintenanceWorkCompletion->newDocument(
                    ModulesEnum::Property,
                    $uploadedFile,
                    [PermissionEnum::PropertyMaintenanceWorkCompletionView->value],
                    $request->user()
                );
            }
        }

        return redirect()->route('workcompletion.index')->with('success', 'Work completion created successfully');
    }
    //     //Check if user has permission to edit tender categories




    //             Auth::user(),

    public function show($Id)
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceWorkCompletionView, PropertyMaintenanceWorkCompletion::class);
        $workCompletion = PropertyMaintenanceWorkCompletion::with('request', 'finalstatus')->findOrFail($Id);

        return view('property.maintenanceandissues.workcompletion.show', compact('workCompletion'));
    }


    //     //Check if user has permission to delete property categories
    //     try {

    //         // Log the error for debugging
    //         Log::error('Error deleting property work completion: ' . $th->getMessage());
}
