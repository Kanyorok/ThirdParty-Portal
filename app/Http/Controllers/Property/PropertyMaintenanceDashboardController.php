<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PostingEnum;
use App\Http\Controllers\Controller;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;
use App\Models\PropertyManagement\PropertyMaintenanceAssign;

class PropertyMaintenanceDashboardController extends Controller
{
    public function index()
    {
        // Summary counts
        $totalRequests = PropertyMaintenanceRequest::count();
        $inProgress = PropertyMaintenanceAssign::where('Status', PostingEnum::Pending)->count();
        $completed = PropertyMaintenanceAssign::where('Status', PostingEnum::Completed)->count();

        // Table data
        $requests = PropertyMaintenanceAssign::with([
            'request.property',
            'request.unit',
            'request.issueType',
            'request.priority',
            'internalTechnician',
            'prequalifiedVendor'
        ])
        ->orderByDesc('CreatedOn')
        ->take(20) // limit for demo
        ->get();

        return view('property.maintenanceandissues.dashboard.index', compact(
            'totalRequests',
            'inProgress',
            'completed',
            'requests'
        ));
    }
}
