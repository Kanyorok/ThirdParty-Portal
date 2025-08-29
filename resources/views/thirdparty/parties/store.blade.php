@extends('layouts.app')

@section('title', 'Third Party Details')

@section('content')
<div class="container mx-auto p-4">
    <div class="flex justify-center">
        <div class="w-full max-w-4xl">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-6">
                <div class="px-6 py-4 flex justify-between items-center bg-gray-50 rounded-t-xl border-b">
                    <h4 class="text-xl font-semibold text-gray-800">Third Party Details</h4>
                    <a href="/thirdparty/parties" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150">
                        <i class="fas fa-arrow-left mr-2"></i> Back to List
                    </a>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Assuming $thirdParty is the variable passed from the controller -->
                    <h2 class="text-2xl font-bold text-gray-900">{{ $thirdParty->ThirdPartyName }}</h2>
                    <p class="text-gray-600">Trading Name: <span class="font-medium text-gray-800">{{ $thirdParty->TradingName ?? 'N/A' }}</span></p>
                    <p class="text-gray-600">Business Type: <span class="font-medium text-gray-800">{{ $thirdParty->BusinessType ?? 'N/A' }}</span></p>
                    <p class="text-gray-600">Registration Number: <span class="font-medium text-gray-800">{{ $thirdParty->RegistrationNumber ?? 'N/A' }}</span></p>
                    <p class="text-gray-600">Tax PIN: <span class="font-medium text-gray-800">{{ $thirdParty->TaxPIN ?? 'N/A' }}</span></p>
                    <p class="text-gray-600">VAT Number: <span class="font-medium text-gray-800">{{ $thirdParty->VATNumber ?? 'N/A' }}</span></p>
                    <p class="text-gray-600">Country: <span class="font-medium text-gray-800">{{ $thirdParty->Country ?? 'N/A' }}</span></p>
                    <p class="text-gray-600">Email: <span class="font-medium text-gray-800">{{ $thirdParty->Email ?? 'N/A' }}</span></p>
                    <p class="text-gray-600">Phone: <span class="font-medium text-gray-800">{{ $thirdParty->Phone ?? 'N/A' }}</span></p>
                    <p class="text-gray-600">Website: <span class="font-medium text-gray-800">{{ $thirdParty->Website ?? 'N/A' }}</span></p>
                    <p class="text-gray-600">Physical Address: <span class="font-medium text-gray-800">{{ $thirdParty->PhysicalAddress ?? 'N/A' }}</span></p>
                    <p class="text-gray-600">Third Party Type: <span class="font-medium text-gray-800">{{ $thirdParty->ThirdPartyType ?? 'N/A' }}</span></p>
                    <p class="text-gray-600">Approval Status: <span class="font-medium text-gray-800">{{ $thirdParty->ApprovalStatus ?? 'N/A' }}</span></p>
                    <p class="text-gray-600">Status: <span class="font-medium text-gray-800">{{ $thirdParty->Status ?? 'N/A' }}</span></p>
                </div>
                <div class="px-6 py-4 flex justify-end gap-2 bg-gray-50 border-t rounded-b-xl">
                    <a href="/thirdparty/parties" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150">
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection