<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PostingEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Approval\CodeDetail;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;
use App\Models\PropertyManagement\PropertyMaintenanceAssign;
use App\Models\PropertyManagement\PropertyRegistry;
use Illuminate\Http\Request;

class PropertyMaintenanceDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Summary counts
        $totalRequests = PropertyMaintenanceRequest::count();
        $inProgress = PropertyMaintenanceAssign::where('Status', PostingEnum::Pending)->count();
        $completed = PropertyMaintenanceAssign::where('Status', PostingEnum::Completed)->count();

        // Base query
        $query = PropertyMaintenanceAssign::with([
            'request.property',
            'request.unit',
            'request.issueType',
            'request.priority',
            'internalTechnician',
            'prequalifiedVendor'
        ]);

        // Apply filters
        if ($request->filled('property_id')) {
            $query->whereHas('request', function ($q) use ($request) {
                $q->where('Property', $request->property_id);
            });
        }

        if ($request->filled('priority')) {
            $query->whereHas('request.priority', function ($q) use ($request) {
                $q->where('Description', $request->priority);
            });
        }

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        // Paginate results
        $requests = $query->orderByDesc('CreatedOn')->paginate(20);

        // Dropdown list of properties
        $properties = PropertyRegistry::all();

        return view('property.maintenanceandissues.dashboard.index', compact(
            'totalRequests',
            'inProgress',
            'completed',
            'requests',
            'properties'
        ));
    }
}
