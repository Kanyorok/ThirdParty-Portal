<?php 

namespace App\Http\Controllers\ThirdParty\API;

use App\Services\ThirdParties\TenantService;
use App\Services\Insurance\BancassuranceCustomersService;
use App\Services\ThirdParties\ThirdPartiesService;

use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\Api\NewThirdPartyRequest;
use App\Services\ThirdParties\SupplierService;
use App\Services\ThirdParties\ThirdPartyService;
use App\Models\ThirdParty\ThirdPartyUser as ThirdPartyUserModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use DateTime;

class NewThirdPartyController extends Controller
{
    public function store(NewThirdPartyRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $country = $request->getCountry();
            $location = $request->getLocation($country);
            $businessType = $request->getBusinessType();
            
            $actor = SystemHelper::user();

            $types = $request->validated('types') ?? [];

            $email = $request->validated('Email');
            if (empty($email)) {
                $registrationNumber = $request->validated('RegistrationNumber');
                $email = strtolower(str_replace([' ', '-', '/'], '', $registrationNumber)) . '@noreply.local';
            }

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
                $partyService = new class($party) extends ThirdPartiesService {
                    public static function getType(): \App\Models\ThirdParty\ThirdPartyType {
                        return ThirdPartyService::getType();
                    }
                };
                $partyService->setLogo($request->file('logo'), $actor ?? $party);
            }

            if (in_array(ThirdPartyService::TypeSupplier, $types)) {
                SupplierService::createFromParty($party, $actor ?? $party);
            }

            if (in_array(ThirdPartyService::TypeTenant, $types)) {
                TenantService::createFromParty(
                    party: $party, 
                    actor: $actor ?? $party, 
                    remarks: $request->validated('tenant_remarks')
                );
            }

            if (in_array(ThirdPartyService::TypeCustomer, $types)) {
                BancassuranceCustomersService::createFromParty(
                    party: $party,
                    Referral: null,
                    DateOfBirth: null, 
                    Gender: null,      
                    MaritalStatus: null,
                    Occupation: null,
                    user: $actor ?? $party
                );
            }

            if ($request->boolean('createUser')) {
                $gender = $request->getGender('user_Gender');
                
                $partyService = new class($party) extends ThirdPartiesService {
                    public static function getType(): \App\Models\ThirdParty\ThirdPartyType {
                        return ThirdPartyService::getType();
                    }
                };

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
                    'isCustomer' => $party->isCustomer()
                ]
            ], 201);
        });
    }
}