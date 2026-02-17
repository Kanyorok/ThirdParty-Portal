<?php

namespace App\Http\Controllers\API\Property;

use App\Http\Controllers\Controller;
use App\Http\Resources\Property\PropertyLeaseCollection;
use App\Http\Resources\Property\PropertyLeaseResource;
use App\Models\PropertyManagement\PropertyNewLease;
use Illuminate\Http\Request;

class PropertyLeaseController extends Controller
{
    public function index(Request $request): PropertyLeaseCollection
    {
        $tenantId = $request->query('id');

        $leases = PropertyNewLease::with([
            'tenant',
            'property',
            'block',
            'floor',
            'unit',
            'currency',
            'code',
            'createdByUser',
        ])
        ->when($tenantId, function ($query) use ($tenantId) {
            $query->whereHas('tenant', fn ($q) => $q->where('Id', $tenantId));
        })
        ->paginate(10);

        return new PropertyLeaseCollection($leases);
    }

    public function show(Request $request): PropertyLeaseResource
    {
        $request->validate([
            'id' => 'required|integer', // tenant id
            'lease_id' => 'required|integer',
        ]);

        $tenantId = $request->query('id');
        $leaseId = $request->query('lease_id');

        $lease = PropertyNewLease::with([
            'tenant',
            'property',
            'block',
            'floor',
            'unit',
            'currency',
            'code',
            'createdByUser',
        ])
        ->where('Id', $leaseId)
        ->whereHas('tenant', fn ($q) => $q->where('Id', $tenantId))
        ->firstOrFail();

        return new PropertyLeaseResource($lease);
    }
}
