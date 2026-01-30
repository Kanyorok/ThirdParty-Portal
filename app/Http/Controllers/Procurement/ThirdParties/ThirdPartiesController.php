<?php

namespace App\Http\Controllers\Procurement\ThirdParties;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\Api\NewThirdPartyRequest;
use App\Http\Resources\ThirdParty\Api\ThirdPartyResource;
use App\Models\ThirdParty\ThirdParties;
use App\Services\ThirdParties\ThirdPartyService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ThirdPartiesController extends Controller
{
    public function index(Request $request)
    {
        $query = ThirdParties::query()->with(['types', 'country']);

        if ($request->has('type')) {
            $type = strtoupper($request->type);
            $query->where('ThirdPartyType', $type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ThirdPartyName', 'like', "%{$search}%")
                    ->orWhere('TradingName', 'like', "%{$search}%")
                    ->orWhere('Email', 'like', "%{$search}%");
            });
        }

        $thirdParties = $query->paginate(25);

        return ThirdPartyResource::collection($thirdParties);
    }

    public function store(NewThirdPartyRequest $request)
    {
        // Allow unauthenticated submission if valid user_id is provided
        $userId = $request->input('user_id');
        $actor = null;

        if ($userId) {
            $actor = \App\Models\ThirdParty\ThirdPartyUser::where('UserID', $userId)->first();
        } else {
            $actor = $request->user();
        }

        if (! $actor) {
            return response()->json(['message' => 'User could not be identified.'], 401);
        }

        $country = $request->getCountry();
        $businessType = $request->getBusinessType();
        $location = $request->getLocation($country);
        $phone = $request->getPhoneNumber($country, 'Phone');
        $logo = $request->getLogo();

        $userDetails = [];
        if ($request->boolean('createUser')) {
            $userDetails = [
                'FirstName' => $request->str('user_FirstName')->trim()->toString(),
                'LastName' => $request->str('user_LastName')->trim()->toString(),
                'Email' => $request->str('user_Email')->trim()->toString(),
                'Phone' => $request->getPhoneNumber($country, 'user_Phone'),
                'Gender' => $request->getGender('user_Gender'),
            ];
        }

        $customerDetails = [];
        if (in_array(ThirdPartyService::TypeCustomer, $request->array('types'), true)) {
            $customerDetails = [
                'DateOfBirth' => $request->date('customer_DateOfBirth'),
                'Gender' => $request->getGender('customer_Gender'),
                'MaritalStatus' => $request->getMaritalStatus(),
                'Occupation' => $request->getOccupation(),
            ];
        }

        $tenantDetails = [];
        if (in_array(ThirdPartyService::TypeTenant, $request->array('types'), true)) {
            $tenantDetails = [
                'Remarks' => $request->str('tenant_Remarks')->trim()->toString(),
            ];
        }

        try {
            return DB::transaction(function () use ($request, $actor, $businessType, $location, $phone, $userDetails, $customerDetails, $tenantDetails, $logo) {

                if ($actor->ThirdPartyId && $party = ThirdParties::find($actor->ThirdPartyId)) {
                    // Update existing Party
                    $service = ThirdPartyService::update(
                        party: $party,
                        name: $request->str('Name')->trim()->toString(),
                        tradingName: $request->str('TradingName')->trim()->toString(),
                        businessType: $businessType,
                        registrationNumber: $request->str('RegistrationNumber')->trim()->toString(),
                        taxPIN: $request->str('TaxPIN')->trim()->toString(),
                        vatNumber: $request->str('VATNumber')->trim()->toString(),
                        locationID: $location,
                        physicalAddress: $request->str('PhysicalAddress')->trim()->toString(),
                        email: $request->str('Email')->trim()->toString(),
                        phone: $phone,
                        website: $request->str('Website')->trim()->toString(),
                        status: null,
                        extra: null,
                        actor: $actor,
                        types: $request->array('types'),
                        CustomerDateOfBirth: $customerDetails['DateOfBirth'] ?? null,
                        CustomerGender: $customerDetails['Gender'] ?? null,
                        CustomerMaritalStatus: $customerDetails['MaritalStatus'] ?? null,
                        CustomerOccupation: $customerDetails['Occupation'] ?? null,
                        Tenant_Remarks: $tenantDetails['Remarks'] ?? null,
                    );
                } else {
                    // Create New Party for the authenticated User (Step 2 of Registration)
                    // We need to use RegistrationService or ThirdPartyService manually?
                    // RegistrationService has 'createThirdPartyForUser' which links them.

                    // Let's use RegistrationService's logic here or replicate it via ThirdPartyService + Manual Link.
                    // ThirdPartyService::create returns 'self'.

                    $service = ThirdPartyService::create(
                        name: $request->str('Name')->trim()->toString(),
                        tradingName: $request->str('TradingName')->trim()->toString(),
                        businessType: $businessType,
                        registrationNumber: $request->str('RegistrationNumber')->trim()->toString(),
                        taxPIN: $request->str('TaxPIN')->trim()->toString(),
                        vatNumber: $request->str('VATNumber')->trim()->toString(),
                        locationID: $location,
                        physicalAddress: $request->str('PhysicalAddress')->trim()->toString(),
                        email: $request->str('Email')->trim()->toString(),
                        phone: $phone,
                        website: $request->str('Website')->trim()->toString(),
                        status: null,
                        extra: null,
                        actor: $actor,
                        types: $request->array('types'),
                        CustomerDateOfBirth: $customerDetails['DateOfBirth'] ?? null,
                        CustomerGender: $customerDetails['Gender'] ?? null,
                        CustomerMaritalStatus: $customerDetails['MaritalStatus'] ?? null,
                        CustomerOccupation: $customerDetails['Occupation'] ?? null,
                        Tenant_Remarks: $tenantDetails['Remarks'] ?? null,
                    );

                    // CRITICAL: Link it to user
                    $actor->ThirdPartyId = $service->party->Id;
                    $actor->save();

                    // Note: RegistrationService also added the 't_ThirdPartyType_ThirdParties' manually.
                    // But ThirdPartyService::create handles creation of sub-entities (Supplier/Tenant).
                    // Does it add the 'types' pivot?
                    // Earlier analysis suggested we might need to sync types.
                    // Let's rely on standard logic. If ThirdPartyService handles it, great.
                    // If not, we can add:
                    // But let's trust the service or check if 'create' does it.
                    // Referring back to 'ThirdPartyService::create': it does `match($type) -> addTenant/Supplier`.
                    // It does NOT seem to explicitly attach the `types` pivot unless `addTenant` does.
                    // Let's manually sync types to be safe as RegistrationService did.
                    // Actually, 'addTenant' creates PropertyNewTenant which links to Party.
                    // The 'types' relationship is BelongsToMany on 't_ThirdPartyType_ThirdParties'.
                    // We should probably sync it.

                    // Actually, let's look at `createThirdPartyForUser` in RegistrationService again.
                    // It creates Party then INSERTs into `t_ThirdPartyType_ThirdParties`.
                    // So yes, we should do that here too.

                    if ($request->has('types')) {
                        // RegistrationService uses direct DB insert. Eloquent sync is better.
                        // But need to know 'TypeId' vs 'Code'.
                        // The request sends 'types' as array of Codes (SU, TN, CU) or IDs?
                        // ThirdPartyService::create expects `array|string $types`.
                        // Frontend sends `types` as array of strings (codes) like ['SU', 'TN'].
                        // We need IDs for sync?
                        // `ThirdParties` model `types()` relationship is BelongsToMany ThirdPartyType.
                        // So we need ThirdPartyType IDs.
                        // `ThirdPartyService::getTypes` resolves codes to models. we can use that.

                        $resolvedTypes = \App\Services\ThirdParties\ThirdPartyService::getTypes($request->array('types'));
                        $service->party->types()->syncWithoutDetaching($resolvedTypes->pluck('TypeId'));
                    }
                }

                if ($request->boolean('createUser')) {
                    // This is likely for creating *additional* users, not the primary one.
                    $service->addUser(
                        firstName: $userDetails['FirstName'],
                        lastName: $userDetails['LastName'],
                        email: $userDetails['Email'],
                        phone: $userDetails['Phone'],
                        gender: $userDetails['Gender'],
                        actor: $actor
                    );
                }

                if ($logo instanceof UploadedFile) {
                    $service->setLogo($logo, $actor);
                }

                return new ThirdPartyResource($service->party->load(['types', 'country']));
            });
        } catch (ErroredException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Failed to create/update third party: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    public function show(ThirdParties $thirdParty)
    {
        return new ThirdPartyResource($thirdParty->load(['types', 'country']));
    }

    // Explicitly added to handle "My Third Party" request
    public function showMyThirdPartyDetails(Request $request)
    {
        $user = $request->user();
        if (! $user || ! $user->ThirdPartyId) {
            return response()->json(['message' => 'No third party associated.'], 404);
        }
        $party = ThirdParties::find($user->ThirdPartyId);
        if (! $party) {
            return response()->json(['message' => 'Party not found'], 404);
        }

        return new ThirdPartyResource($party->load(['types', 'country']));
    }

    public function update(Request $request, ThirdParties $thirdParty)
    {
        $validated = $request->validate([
            'ThirdPartyName' => 'sometimes|required|string|max:255',
            'TradingName' => 'nullable|string|max:255',
            'Email' => 'sometimes|required|email|unique:t_ThirdParties,Email,' . $thirdParty->Id . ',Id',
            'types_ids' => 'nullable|array',
            'types_ids.*' => 'exists:t_ThirdPartyTypes,TypeId',
            'RegistrationNumber' => 'nullable|string|max:100',
            'CountryId' => 'nullable|exists:t_Countries,Id',
        ]);

        $thirdParty->update($validated);

        if (isset($validated['types_ids'])) {
            $thirdParty->types()->sync($validated['types_ids']);
        }

        return new ThirdPartyResource($thirdParty->load(['types', 'country']));
    }

    public function destroy(ThirdParties $thirdParty)
    {
        $thirdParty->delete();

        return response()->noContent();
    }
}
