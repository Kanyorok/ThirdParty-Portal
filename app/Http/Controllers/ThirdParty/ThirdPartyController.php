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
                $query = ThirdParties::query()
                    ->with(['businessType:Id,Description', 'country:Id,Name,Flag', 'types:TypeId,Code,Description', 'status:Id,Description']);

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('actions', function (ThirdParties $thirdParties) {
                        $showUrl = route('thirdparty.parties.show', $thirdParties->Id);
                        $editUrl = route('thirdparty.parties.edit', $thirdParties->Id);
                        
                        return '
                            <div class="action-buttons">
                                <a href="' . $showUrl . '" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="' . $editUrl . '" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger delete-btn" 
                                        data-id="' . $thirdParties->Id . '" 
                                        data-name="' . e($thirdParties->ThirdPartyName) . '"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        ';
                    })
                    ->editColumn('types', function (ThirdParties $thirdParties) {
                        return $thirdParties->types->pluck('Description')->map(fn($type) => "<span class='badge bg-primary me-1'>{$type}</span>")->implode(' ');
                    })
                    ->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')
                    ->setRowData([
                        'dbl_click_url' => function (ThirdParties $thirdParties) {
                            return route('thirdparty.parties.show', $thirdParties->Id);
                        },
                    ])
                    ->rawColumns(['types', 'actions'])
                    ->make();
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
    public function show($thirdPartyId): View
    {
        // Eager load all necessary relationships
        $thirdParty = ThirdParties::query()
            ->with([
                'types',
                'country:id,Name,Flag,PhoneCode',
                'businessType:id,Description',
                'status:id,Description',
                'location',
                'users',
                'customerDetails',
                'tenantDetails',
                // For supplier tab (if applicable) - FIXED: Removed non-existent relationships
                'supplierInfo' => function($query) {
                    // Load only relationships that actually exist
                    $query->with([
                        'prequalificationApplications',
                        'categories',
                        'categories.itemCategories'
                    ]);
                },
                // For attribution tab
                'attributions' => function($query) {
                    $query->latest();
                },
                'notes' => function($query) {
                    $query->latest()->with('user');
                },
                'documents' => function($query) {
                    $query->latest();
                },
                'logo',
            ])
            ->findOrFail($thirdPartyId);

        // Get available tabs based on third party types
        $availableTabs = $this->getAvailableTabs($thirdParty);
        
        // Get tab data
        $tabData = [
            'profile' => $this->getProfileTabData($thirdParty),
            'supplier' => $this->getSupplierTabData($thirdParty),
            'customer' => $this->getCustomerTabData($thirdParty),
            'tenant' => $this->getTenantTabData($thirdParty),
            'attribution' => $this->getAttributionTabData($thirdParty),
        ];

        return view('thirdparty.show', [
            'party' => $thirdParty,
            'tabData' => $tabData,
            'availableTabs' => $availableTabs,
            'activeTab' => request()->get('tab', 'profile'),
            // Additional data for forms/modals
            'businessTypes' => CodeDetail::query()->where('CodeID', 'BusinessType')->orderBy('DisplayOrder')->get(['Value', 'Description']),
            'genders' => CodeDetail::where('CodeID', 'Gender')->get(['Value', 'Description']),
            'maritalStatuses' => CodeDetail::where('CodeID', 'MaritalStatus')->get(['Value', 'Description']),
            'occupations' => CodeDetail::where('CodeID', 'Occupation')->get(['Value', 'Description']),
        ]);
    }

    /**
     * Get available tabs based on third party types
     */
    private function getAvailableTabs(ThirdParties $thirdParty): array
    {
        $tabs = ['profile']; // Profile tab is always available
        
        $typeCodes = $thirdParty->types->pluck('Code')->toArray();
        
        // Check for supplier type (assuming 'SU' or similar)
        if (in_array('SU', $typeCodes)) {
            $tabs[] = 'supplier';
        }
        
        // Check for customer type (from your form - 'CU')
        if (in_array('CU', $typeCodes)) {
            $tabs[] = 'customer';
        }
        
        // Check for tenant type (from your form - 'TN')
        if (in_array('TN', $typeCodes)) {
            $tabs[] = 'tenant';
        }
        
        // Attribution tab - maybe always available or based on permissions
        $tabs[] = 'attribution';
        
        return $tabs;
    }

    /**
     * Get profile tab data (From Step 1 of your form)
     */
    private function getProfileTabData(ThirdParties $thirdParty): array
    {
        return [
            'basicInfo' => [
                'name' => $thirdParty->Name,
                'tradingName' => $thirdParty->TradingName,
                'businessType' => [
                    'value' => $thirdParty->BusinessType,
                    'description' => $thirdParty->businessType->Description ?? null,
                ],
                'registrationNumber' => $thirdParty->RegistrationNumber,
                'taxPIN' => $thirdParty->TaxPIN,
                'vatNumber' => $thirdParty->VATNumber,
                'website' => $thirdParty->Website,
            ],
            'contactInfo' => [
                'email' => $thirdParty->Email,
                'phone' => $thirdParty->Phone,
                'physicalAddress' => $thirdParty->PhysicalAddress,
            ],
            'locationInfo' => [
                'country' => [
                    'name' => $thirdParty->country->Name ?? null,
                    'flag' => $thirdParty->country->Flag ?? null,
                    'phoneCode' => $thirdParty->country->PhoneCode ?? null,
                ],
                'location' => $thirdParty->location->Name ?? null,
            ],
            'statusInfo' => [
                'status' => $thirdParty->status->Description ?? null,
                'createdAt' => $thirdParty->CreatedDate,
                'updatedAt' => $thirdParty->UpdatedDate,
                'createdBy' => $thirdParty->CreatedBy,
                'updatedBy' => $thirdParty->UpdatedBy,
            ],
            'logo' => $thirdParty->logo ?? null,
            'users' => $thirdParty->users->map(function($user) {
                return [
                    'id' => $user->Id,
                    'firstName' => $user->FirstName,
                    'lastName' => $user->LastName,
                    'email' => $user->Email,
                    'phone' => $user->Phone,
                    'gender' => $user->Gender,
                    'status' => $user->status->Description ?? null,
                ];
            }) ?? [],
        ];
    }

    /**
     * Get customer tab data (From Step 2 - Customer Details)
     */
    private function getCustomerTabData(ThirdParties $thirdParty): array
    {
        $customer = $thirdParty->customerDetails;
        
        if (!$customer) {
            return ['exists' => false];
        }
        
        return [
            'exists' => true,
            'demographics' => [
                'dateOfBirth' => $customer->DateOfBirth,
                'gender' => [
                    'value' => $customer->Gender,
                    'description' => $customer->gender->Description ?? null,
                ],
                'maritalStatus' => [
                    'value' => $customer->MaritalStatus,
                    'description' => $customer->maritalStatus->Description ?? null,
                ],
                'occupation' => [
                    'value' => $customer->Occupation,
                    'description' => $customer->occupation->Description ?? null,
                ],
            ],
            'customerMetrics' => [
                'customerSince' => $customer->CreatedDate,
                'lastPurchase' => $customer->LastPurchaseDate,
                'totalPurchases' => $customer->TotalPurchases,
                'loyaltyPoints' => $customer->LoyaltyPoints,
                'creditLimit' => $customer->CreditLimit,
            ],
            'preferences' => $customer->preferences ?? [],
            'recentActivity' => $customer->activities ?? [],
        ];
    }

    /**
     * Get tenant tab data (From Step 2 - Tenant Details)
     */
    private function getTenantTabData(ThirdParties $thirdParty): array
    {
        $tenant = $thirdParty->tenantDetails;
        
        if (!$tenant) {
            return ['exists' => false];
        }
        
        return [
            'exists' => true,
            'tenantInfo' => [
                'remarks' => $tenant->Remarks,
                'tenantCode' => $tenant->TenantCode,
                'leaseStart' => $tenant->LeaseStart,
                'leaseEnd' => $tenant->LeaseEnd,
                'rentAmount' => $tenant->RentAmount,
                'securityDeposit' => $tenant->SecurityDeposit,
                'paymentFrequency' => $tenant->PaymentFrequency,
            ],
            'propertyInfo' => [
                'property' => $tenant->property,
                'unitNumber' => $tenant->UnitNumber,
                'floor' => $tenant->Floor,
            ],
            'emergencyContact' => [
                'name' => $tenant->EmergencyContactName,
                'phone' => $tenant->EmergencyContactPhone,
                'relationship' => $tenant->EmergencyContactRelationship,
            ],
            'paymentHistory' => $tenant->payments()->latest()->take(10)->get(),
            'maintenanceRequests' => $tenant->maintenanceRequests()->latest()->take(10)->get(),
        ];
    }

    /**
     * Get supplier tab data - FIXED VERSION
     */
    private function getSupplierTabData(ThirdParties $thirdParty): array
    {
        $supplier = $thirdParty->supplierInfo;
        
        if (!$supplier) {
            return ['exists' => false];
        }
        
        // Get products from categories/item categories if needed
        $products = [];
        $categories = [];
        
        if ($supplier->categories) {
            $categories = $supplier->categories->map(function($category) {
                $itemCategories = $category->itemCategories ? 
                    $category->itemCategories->map(function($itemCat) {
                        return [
                            'id' => $itemCat->Id ?? $itemCat->ItemCategoryID,
                            'name' => $itemCat->CategoryName ?? $itemCat->Name,
                            'code' => $itemCat->Code ?? 'N/A',
                            'category' => $itemCat->parentCategory->CategoryName ?? 'General',
                            'unitPrice' => 0,
                        ];
                    })->toArray() : [];
                
                return [
                    'id' => $category->SupplierCategoryID,
                    'name' => $category->CategoryName,
                    'description' => $category->Description,
                    'active' => $category->IsActive == '1' || $category->IsActive === true,
                    'item_categories' => $itemCategories,
                    'item_categories_count' => count($itemCategories),
                ];
            })->toArray();
            
            // Flatten item categories as products for backward compatibility
            foreach ($categories as $category) {
                foreach ($category['item_categories'] as $itemCategory) {
                    $products[] = [
                        'id' => $itemCategory['id'],
                        'name' => $itemCategory['name'],
                        'code' => $itemCategory['code'],
                        'category' => $category['name'],
                        'unitPrice' => $itemCategory['unitPrice'],
                    ];
                }
            }
        }
        
        // Get prequalification applications
        $prequalApplications = $supplier->prequalificationApplications ? 
            $supplier->prequalificationApplications : collect([]);
        
        return [
            'exists' => true,
            'supplierInfo' => [
                'supplierCode' => $supplier->SupplierID ?? 'N/A',
                'approvalStatus' => $supplier->ApprovalStatus?->value ?? 'Pending',
                'isPrequalified' => $supplier->IsPrequalified ? 'Yes' : 'No',
                'extraData' => $supplier->Extra ?? [],
                // Default values for view compatibility
                'paymentTerms' => 'N/A',
                'creditLimit' => 0,
                'leadTime' => 0,
                'rating' => 0,
                'primaryContact' => 'N/A',
            ],
            'products' => $products,
            'categories' => $categories,
            'prequalificationApplications' => $prequalApplications->map(function($application) {
                return [
                    'id' => $application->Id ?? $application->ApplicationID,
                    'applicationId' => $application->ApplicationID ?? 'N/A',
                    'status' => $application->Status ?? 'Pending',
                    'submittedOn' => $application->SubmittedOn ?? $application->CreatedOn,
                    'category' => $application->category->CategoryName ?? 'N/A',
                ];
            }),
            'contracts' => [],
            'performance' => [
                'onTimeDelivery' => 0,
                'qualityRating' => 0,
                'totalOrders' => 0,
                'totalSpend' => 0,
                'lastOrderDate' => null,
            ],
        ];
    }

    /**
     * Get attribution tab data
     */
    private function getAttributionTabData(ThirdParties $thirdParty): array
    {
        return [
            'notes' => $thirdParty->notes->map(function($note) {
                return [
                    'id' => $note->Id,
                    'content' => $note->Content,
                    'createdBy' => $note->user->Name ?? 'Unknown',
                    'createdAt' => $note->CreatedDate,
                    'type' => $note->NoteType,
                ];
            }),
            'documents' => $thirdParty->documents->map(function($document) {
                return [
                    'id' => $document->Id,
                    'name' => $document->Name,
                    'type' => $document->DocumentType,
                    'size' => $this->formatBytes($document->Size),
                    'uploadedBy' => $document->UploadedBy,
                    'uploadedAt' => $document->UploadedDate,
                    'url' => $document->FilePath,
                ];
            }),
            'attributions' => $thirdParty->attributions->map(function($attribution) {
                return [
                    'id' => $attribution->Id,
                    'type' => $attribution->Type,
                    'value' => $attribution->Value,
                    'source' => $attribution->Source,
                    'confidence' => $attribution->Confidence,
                    'createdAt' => $attribution->CreatedDate,
                    'notes' => $attribution->Notes,
                ];
            }),
            'activityLog' => $this->getActivityLog($thirdParty),
        ];
    }

    /**
     * Get activity log for the third party
     */
    private function getActivityLog(ThirdParties $thirdParty): array
    {
        return [
            [
                'action' => 'Created',
                'by' => 'System Admin',
                'at' => $thirdParty->CreatedDate,
                'details' => 'Third party record created',
            ],
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get tab content via AJAX (for lazy loading tabs)
     */
    public function getTabContent(Request $request, $thirdPartyId): JsonResponse
    {
        $request->validate([
            'tab' => 'required|in:profile,supplier,customer,tenant,attribution',
        ]);
        
        $thirdParty = ThirdParties::with($this->getTabRelationships($request->tab))
            ->findOrFail($thirdPartyId);
        
        $tab = $request->get('tab');
        $method = 'get' . ucfirst($tab) . 'TabData';
        
        if (method_exists($this, $method)) {
            $data = $this->$method($thirdParty);
            return response()->json([
                'success' => true,
                'html' => view("thirdparty.tabs.{$tab}", [
                    'data' => $data,
                    'party' => $thirdParty,
                ])->render(),
                'title' => $this->getTabTitle($tab),
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Tab not found',
        ], 404);
    }

    /**
     * Get relationships needed for specific tab
     */
    private function getTabRelationships(string $tab): array
    {
        $relationships = ['types', 'country:id,Name,Flag,PhoneCode'];
        
        switch ($tab) {
            case 'customer':
                $relationships[] = 'customerDetails';
                $relationships[] = 'customerDetails.gender';
                $relationships[] = 'customerDetails.maritalStatus';
                $relationships[] = 'customerDetails.occupation';
                break;
            case 'tenant':
                $relationships[] = 'tenantDetails';
                $relationships[] = 'tenantDetails.property';
                break;
            case 'supplier':
                $relationships[] = 'supplierInfo';
                $relationships[] = 'supplierInfo.prequalificationApplications';
                $relationships[] = 'supplierInfo.categories';
                $relationships[] = 'supplierInfo.categories.itemCategories';
                break;
            case 'attribution':
                $relationships[] = 'notes.user';
                $relationships[] = 'documents';
                $relationships[] = 'attributions';
                break;
            case 'profile':
                $relationships[] = 'businessType';
                $relationships[] = 'status';
                $relationships[] = 'location';
                $relationships[] = 'users.status';
                $relationships[] = 'logo';
                break;
        }
        
        return $relationships;
    }

    /**
     * Get tab title
     */
    private function getTabTitle(string $tab): string
    {
        $titles = [
            'profile' => 'Profile Information',
            'supplier' => 'Supplier Details',
            'customer' => 'Customer Details',
            'tenant' => 'Tenant Details',
            'attribution' => 'Attributions & Documents',
        ];
        
        return $titles[$tab] ?? ucfirst($tab);
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
    public function destroy($thirdPartyId)
    {
        try {
            $thirdParty = ThirdParties::findOrFail($thirdPartyId);
            $thirdPartyName = $thirdParty->ThirdPartyName;
            $thirdParty->delete();

            return response()->json([
                'success' => true,
                'message' => "Third party '{$thirdPartyName}' deleted successfully"
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Third party not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete third party: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete third party. Please try again.'
            ], 500);
        }
    }
}