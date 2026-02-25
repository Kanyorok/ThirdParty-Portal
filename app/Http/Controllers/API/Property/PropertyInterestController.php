<?php

namespace App\Http\Controllers\API\Property;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\TenantAndLease\PropertyInterestRequest;
use App\Http\Resources\Property\PropertyInterestCollection;
use App\Http\Resources\Property\PropertyInterestResource;
use App\Models\PropertyManagement\PropertyInterest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyInterestController extends Controller
{
    public function index(Request $request): PropertyInterestCollection
    {
        $tenantId = $request->query('id');

        $interests = PropertyInterest::with([
            'tenant',
            'property',
            'block',
            'floor',
            'unit',
            'code',
            'price',
            'createdBy',
        ])
        ->when($tenantId, function ($query) use ($tenantId) {
            $query->where('TenantId', $tenantId);
        })
        ->latest('CreatedOn')
        ->paginate(10);

        return new PropertyInterestCollection($interests);
    }

    public function show(Request $request): PropertyInterestResource
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $interest = PropertyInterest::with([
            'tenant',
            'property',
            'block',
            'floor',
            'unit',
            'code',
            'price',
            'createdBy',
        ])->findOrFail($request->query('id'));

        return new PropertyInterestResource($interest);
    }

    public function store(PropertyInterestRequest $request)
    {
        $validated = $request->validated();

        $interest = PropertyInterest::create([
            'PropertyId' => $validated['PropertyId'],
            'BlockId' => $validated['BlockId'],
            'FloorId' => $validated['FloorId'],
            'UnitId' => $validated['UnitId'],
            'TenantId' => $validated['TenantId'],
            'InterestedStartDate' => $validated['InterestedStartDate'],
            'InterestedEndDate' => $validated['InterestedEndDate'],
            'PaymentFrequency' => $validated['PaymentFrequency'],
            'AdditionalInformation' => $validated['AdditionalInformation'],
            'CreatedBy' => $validated['CreatedBy'],
            'ModifiedBy' => $validated['ModifiedBy'],
        ]);

        return response()->json([
            'message' => 'Property interest created successfully',
            'data' => new PropertyInterestResource($interest->load([
                'tenant',
                'property',
                'block',
                'floor',
                'unit',
                'code',
                'price',
                'createdBy',
            ])),
        ], 201);
    }


    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'DeletedBy' => 'nullable|exists:t_Users,Id',
        ]);

        $interest = PropertyInterest::findOrFail($request->query('id'));
        $deletedBy = $request->input('DeletedBy') ?? Auth::id();
        
        // Update both DeletedOn and DeletedBy for soft delete
        $interest->update([
            'DeletedOn' => now(),
            'DeletedBy' => $deletedBy,
        ]);

        return response()->json(['message' => 'Property interest deleted successfully']);
    }
}
