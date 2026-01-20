<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PostingEnum;
use App\Http\Controllers\Controller;
use App\Models\PropertyManagement\PropertyMaintenanceAssign;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;
use App\Models\PropertyManagement\PropertyRegistry;
use Illuminate\Http\Request;

class PropertyMaintenanceDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Summary
        $totalRequests = PropertyMaintenanceRequest::count();
        $inProgress = PropertyMaintenanceAssign::where('Status', PostingEnum::Pending)->count();
        $completed  = PropertyMaintenanceAssign::where('Status', PostingEnum::Completed)->count();

        // Base query
        $query = PropertyMaintenanceAssign::with([
            'request.property',
            'request.issueType',
            'request.priority',
            'internalTechnician',
            'prequalifiedVendor'
        ]);

        // Filters (ALL CAN CO-EXIST)
        if ($request->filled('property_id')) {
            $query->whereHas('request', fn ($q) =>
                $q->where('Property', $request->property_id)
            );
        }

        if ($request->filled('priority')) {
            $query->whereHas('request.priority', fn ($q) =>
                $q->where('Description', $request->priority)
            );
        }

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        $requests = $query
            ->orderByDesc('CreatedOn')
            ->paginate(20);

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
