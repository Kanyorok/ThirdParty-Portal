<?php

namespace App\Http\Controllers\ThirdParty;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\NewThirdPartyRequest;
use App\Models\Core\Country;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyType;
use App\Models\Workflow\CodeDetail;
use App\Services\ThirdParties\ThirdPartyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Log;
use Throwable;
use Yajra\DataTables\DataTables;

class ThirdPartyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            try {
                $query = ThirdParties::query()->with(['businessType:Id,Description', 'country:Id,Name,Flag', 'types:TypeId,Code,Description', 'status:Id,Description']);

                return DataTables::of($query)->editColumn('types', function (ThirdParties $thirdParties) {
                    return $thirdParties->types->pluck('Description')->map(fn($type) => "<span class='badge bg-primary'>{$type}</span>")->implode(' ');
                })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                    'dbl_click_url' => function (ThirdParties $thirdParties) {
                        return route('thirdparty.parties.show', $thirdParties->Id);
                    },
                ])->addIndexColumn()->rawColumns(['types'])->make();
            } catch (Throwable $e) {
                Log::error('Failed to load third parties: ' . $e->getMessage());
                return $this->errored('unexpected error occurred while loading the data. please try again later.');
            }
        }
        return view('thirdparty.index', [
            'types' => ThirdPartyType::query()->get(['Code', 'Description']),
            'businessTypes' => CodeDetail::query()->where('CodeID', 'BusinessType')->orderBy('DisplayOrder')->get(['Value', 'Description'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NewThirdPartyRequest $request)
    {
        $actor = $request->user();
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
                'Occupation' => $request->getOccupation()
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

                if ($request->boolean('createUser')) {
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

                return $this->succeeded("{$service->party->ThirdPartyName} created successfully");
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Throwable $e) {
            Log::error('Failed to create third party: ');
            Log::error($e);
        }
        return $this->errored('unexpected error occurred while creating the third party. please try again later.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('thirdparty.create', [
            'types' => ThirdPartyType::query()->get(['Code', 'Description']),
            'countries' => Country::query()->select(['Name', 'CountryCode', 'Id', 'PhoneCode', 'Flag'])->whereHas('localities')->orderBy('t_Countries.Name')->get(),
            'businessTypes' => CodeDetail::query()->where('CodeID', 'BusinessType')->orderBy('DisplayOrder')->get(['Value', 'Description']),
            'genders' => CodeDetail::where('CodeID', 'Gender')->get(['Value', 'Description']),
            'maritalstatus' => CodeDetail::where('CodeID', 'MaritalStatus')->get(['Value', 'Description']),
            'occupations' => CodeDetail::where('CodeID', 'Occupation')->get(['Value', 'Description'])
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($thirdParties)
    {
        $thirdParties = ThirdParties::query()->with(['types', 'country:Id,Name,Flag'])->findOrFail($thirdParties);
        dd($thirdParties);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ThirdParties $thirdParties)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ThirdParties $thirdParties)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ThirdParties $thirdParties)
    {
        //
    }
}
