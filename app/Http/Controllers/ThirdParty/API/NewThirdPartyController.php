<?php

namespace App\Http\Controllers\ThirdParty\API;

use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\Api\NewThirdPartyRequest;
use App\Services\ThirdParties\SupplierService;
use App\Services\ThirdParties\ThirdPartiesService;
use Illuminate\Support\Facades\DB;

class NewThirdPartyController extends Controller
{
    public function store(NewThirdPartyRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $country = $request->getCountry();
            $location = $request->getLocation($country);
            $businessType = $request->getBusinessType();

            $actor = SystemHelper::user();

            // TODO: implement this better
            $email = $request->validated('Email');
            if (empty($email)) {
                $registrationNumber = $request->validated('RegistrationNumber');
                $email = strtolower(str_replace([' ', '-', '/'], '', $registrationNumber)) . '@noreply.local';
            }

            $party = ThirdPartiesService::create(
                name: $request->validated('Name'),
                tradingName: $request->validated('TradingName'),
                businessType: $businessType,
                registrationNumber: $request->validated('RegistrationNumber'),
                taxPIN: $request->validated('TaxPIN'),
                vatNumber: $request->validated('VATNumber'),
                locationID: $location,
                physicalAddress: $request->validated('PhysicalAddress'),
                email: $email,
                phone: $request->getPhoneNumber($country, 'Phone'),
                website: $request->validated('Website'),
                status: null,
                extra: $request->validated('extra'),
                actor: $actor
            );

            if ($request->hasFile('logo')) {
                $partyService = new class($party) extends ThirdPartiesService {
                    public static function getType(): \App\Models\ThirdParty\ThirdPartyType
                    {
                        return \App\Services\ThirdParties\ThirdPartyService::getType();
                    }
                };
                $partyService->setLogo($request->file('logo'), $actor);
            }

            if (in_array(\App\Services\ThirdParties\ThirdPartyService::TypeSupplier, $request->validated('types'))) {
                SupplierService::createFromParty($party, $actor);
            }

            if ($request->boolean('createUser')) {
                $gender = $request->getGender('user_Gender');
                $partyService = new class($party) extends ThirdPartiesService {
                    public static function getType(): \App\Models\ThirdParty\ThirdPartyType
                    {
                        return \App\Services\ThirdParties\ThirdPartyService::getType();
                    }
                };

                $partyService->addUser(
                    firstName: $request->validated('user_FirstName'),
                    lastName: $request->validated('user_LastName'),
                    email: $request->validated('user_Email'),
                    phone: $request->getPhoneNumber($country, 'user_Phone'),
                    gender: $gender,
                    actor: $actor,
                    password: $request->validated('user_Password'),
                    sendVerification: true
                );
            }

            return response()->json([
                'success' => true,
                'message' => $request->boolean('createUser')
                    ? 'Registration successful! Check your email to verify your account.'
                    : 'Profile created successfully.',
                'data' => [
                    'id' => $party->Id,
                    'name' => $party->ThirdPartyName,
                    'isSupplier' => in_array(ThirdPartyService::TypeSupplier, $types),
                    'isTenant' => in_array(ThirdPartyService::TypeTenant, $types),
                    'isCustomer' => in_array(ThirdPartyService::TypeCustomer, $types)
                ]
            ], 201);
        });
    }
}
