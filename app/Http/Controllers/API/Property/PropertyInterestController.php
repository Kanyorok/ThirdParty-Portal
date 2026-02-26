<?php

namespace App\Http\Controllers\API\Property;

use App\Http\Controllers\Controller;
use App\Helpers\SystemHelper;
use App\Http\Requests\Property\TenantAndLease\PropertyInterestRequest;
use App\Http\Resources\Property\PropertyInterestCollection;
use App\Http\Resources\Property\PropertyInterestResource;
use App\Models\Auth\User as InternalUser;
use App\Models\PropertyManagement\PropertyInterest;
use App\Models\PropertyManagement\PropertyNewTenant;
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
        $portalUser = $request->user();

        if (! $portalUser || empty($portalUser->ThirdPartyId)) {
            return response()->json([
                'message' => 'Unable to resolve authenticated user context.',
            ], 401);
        }

        $tenant = PropertyNewTenant::query()
            ->where('ThirdPartyId', (int) $portalUser->ThirdPartyId)
            ->whereNull('DeletedOn')
            ->where('IsActive', true)
            ->first();

        if (! $tenant) {
            return response()->json([
                'message' => 'Tenant profile not found for the authenticated user.',
                'errors' => [
                    'tenant_id' => ['No active tenant profile linked to this account.'],
                ],
            ], 422);
        }

        $actorId = $this->resolveActorId($portalUser);

        $interest = PropertyInterest::create([
            'PropertyId' => $validated['PropertyId'],
            'BlockId' => $validated['BlockId'],
            'FloorId' => $validated['FloorId'],
            'UnitId' => $validated['UnitId'],
            'TenantId' => $tenant->Id,
            'InterestedStartDate' => $validated['InterestedStartDate'],
            'InterestedEndDate' => $validated['InterestedEndDate'],
            'PaymentFrequency' => $validated['PaymentFrequency'],
            'AdditionalInformation' => $validated['AdditionalInformation'] ?? null,
            'CreatedBy' => $actorId,
            'ModifiedBy' => $actorId,
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

    private function resolveActorId(object $authenticatedUser): int
    {
        if ($authenticatedUser instanceof InternalUser) {
            return (int) $authenticatedUser->Id;
        }

        if (isset($authenticatedUser->Id) && InternalUser::where('Id', (int) $authenticatedUser->Id)->exists()) {
            return (int) $authenticatedUser->Id;
        }

        if (isset($authenticatedUser->Email)) {
            $internal = InternalUser::query()
                ->where('Email', $authenticatedUser->Email)
                ->first();

            if ($internal) {
                return (int) $internal->Id;
            }
        }

        return (int) SystemHelper::user()->Id;
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
