<?php

namespace App\Http\Controllers\API\Property;

use App\Http\Controllers\Controller;
use App\Http\Resources\Property\PropertyLeaseCollection;
use App\Models\PropertyManagement\PropertyNewLease;


class PropertyLeaseController extends Controller
{
    public function index(): PropertyLeaseCollection
    {
        $leases = PropertyNewLease::with([
            'tenant',
            'property',
            'block',
            'floor',
            'unit',
            'currency'
        ])
        ->paginate(10);

        return new PropertyLeaseCollection($leases);
    }
}
