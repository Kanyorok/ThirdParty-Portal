<?php

namespace App\Http\Controllers\ThirdParty\API;

use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\Api\NewThirdPartyRequest;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Services\RegistrationService;
use App\Services\ThirdParties\ThirdPartyService;
use DateTime;
use Illuminate\Support\Facades\DB;

class NewThirdPartyController extends Controller
{
    public function __construct(protected RegistrationService $registrationService)
    {
    }

    public function store(NewThirdPartyRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $country = $request->getCountry();
            $location = $request->getLocation($country);

            $businessType = $request->getBusinessType();

            if (! $businessType) {
                throw new ErroredException("Invalid Business Type provided.");
            }

            $actor = SystemHelper::user();
            $types = $request->validated('types') ?? [];

            $email = $request->validated('Email');
            if (empty($email)) {
                $registrationNumber = $request->validated('RegistrationNumber');
                $email = strtolower(str_replace([' ', '-', '/'], '', $registrationNumber)) . '@noreply.local';
            }

            $data = [
                'types' => $types,
                'user_Remarks' => $request->validated('user_Remarks'),
            ];

            if (in_array(ThirdPartyService::TypeCustomer, $types)) {
                $data['user_Gender'] = $request->getGender('user_Gender');
                $data['user_MaritalStatus'] = $request->getMaritalStatus('user_MaritalStatus');
                $data['user_Occupation'] = $request->getOccupation('user_Occupation');

                $dob = $request->validated('user_DateOfBirth');
                $data['user_DateOfBirth'] = $dob ? new DateTime($dob) : null;
            }

            $party = ThirdPartyService::create(
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
                actor: $actor,
                data: $data
            );

            $partyService = new ThirdPartyService($party);

            if ($request->hasFile('logo')) {
                $partyService->setLogo($request->file('logo'), $actor ?? $party);
            }

            if ($request->boolean('createUser')) {
                $partyService->addUser(
                    firstName: $request->validated('user_FirstName'),
                    lastName: $request->validated('user_LastName'),
                    email: $request->validated('user_Email'),
                    phone: $request->getPhoneNumber($country, 'user_Phone'),
                    gender: $request->getGender('user_Gender'),
                    actor: $actor,
                    password: $request->validated('user_Password'),
                    sendVerification: true
                );
            }

            $party->load('types');

            $verificationUrl = null;
            if ($request->boolean('createUser')) {
                $user = ThirdPartyUser::where('ThirdPartyId', $party->Id)
                    ->where('Email', strtolower($request->validated('user_Email')))
                    ->first();

                if ($user) {
                    $verificationUrl = $this->registrationService->getThirdPartyVerificationUrl($user);
                }
            }

            return response()->json([
                'success' => true,
                'message' => $request->boolean('createUser')
                    ? 'Registration successful! Check your email to verify your account.'
                    : 'Profile created successfully.',
                'verification_url' => $verificationUrl,
                'data' => [
                    'id' => $party->Id,
                    'name' => $party->ThirdPartyName,
                    'isSupplier' => $party->isSupplier(),
                    'isTenant' => $party->isTenant(),
                    'isCustomer' => $party->isCustomer(),
                ],
            ], 201);
        });
    }
}


class NewThirdPartyController extends Controller
{
    public function store(NewThirdPartyRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $country = $request->getCountry();
            $location = $request->getLocation($country);

            $businessType = $request->getBusinessType();

            if (! $businessType) {
                throw new ErroredException("Invalid Business Type provided.");
            }

            $actor = SystemHelper::user();
            $types = $request->validated('types') ?? [];

            $email = $request->validated('Email');
            if (empty($email)) {
                $registrationNumber = $request->validated('RegistrationNumber');
                $email = strtolower(str_replace([' ', '-', '/'], '', $registrationNumber)) . '@noreply.local';
            }

            $data = [
                'types' => $types,
                'user_Remarks' => $request->validated('user_Remarks'),
            ];

            if (in_array(ThirdPartyService::TypeCustomer, $types)) {
                $data['user_Gender'] = $request->getGender('user_Gender');
                $data['user_MaritalStatus'] = $request->getMaritalStatus('user_MaritalStatus');
                $data['user_Occupation'] = $request->getOccupation('user_Occupation');

                $dob = $request->validated('user_DateOfBirth');
                $data['user_DateOfBirth'] = $dob ? new DateTime($dob) : null;
            }

            $party = ThirdPartyService::create(
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
                actor: $actor,
                data: $data
            );

            $partyService = new ThirdPartyService($party);

            if ($request->hasFile('logo')) {
                $partyService->setLogo($request->file('logo'), $actor ?? $party);
            }

            if ($request->boolean('createUser')) {
                $partyService->addUser(
                    firstName: $request->validated('user_FirstName'),
                    lastName: $request->validated('user_LastName'),
                    email: $request->validated('user_Email'),
                    phone: $request->getPhoneNumber($country, 'user_Phone'),
                    gender: $request->getGender('user_Gender'),
                    actor: $actor,
                    password: $request->validated('user_Password'),
                    sendVerification: true
                );
            }

            $party->load('types');

            return response()->json([
                'success' => true,
                'message' => $request->boolean('createUser')
                    ? 'Registration successful! Check your email to verify your account.'
                    : 'Profile created successfully.',
                'data' => [
                    'id' => $party->Id,
                    'name' => $party->ThirdPartyName,
                    'isSupplier' => $party->isSupplier(),
                    'isTenant' => $party->isTenant(),
                    'isCustomer' => $party->isCustomer(),
                ],
            ], 201);
        });
    }
}
