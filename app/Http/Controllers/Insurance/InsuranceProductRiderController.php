<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Insurance\InsuranceProduct;
use App\Models\Insurance\InsuranceProductRider;
use App\Models\Insurance\InsuranceProvider;
use App\Services\Insurance\ProviderAndProducts\InsuranceProductRiderService;
use App\Enums\Core\PermissionEnum;
use App\Http\Requests\Insurance\ProviderAndProducts\InsuranceProductRiderRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class InsuranceProductRiderController extends Controller
{
    public function index()
    {
        $riders = InsuranceProductRider::all();

        return view('bancassurance.riders.index', compact('riders'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::InsuranceProductRiderView, InsuranceProductRider::class);
        $providers = InsuranceProvider::all();

        return view('bancassurance.riders.create', compact('providers'));
    }

    public function getProductByProvider($providerId)
    {
        $products = InsuranceProduct::where('InsuranceProviderID', $providerId)->get();
        return response()->json($products);
    }

    public function store(InsuranceProductRiderRequest $request)
    {
        $this->authorize(PermissionEnum::InsuranceProductRiderCreate, InsuranceProductRider::class);

        $validated = $request->validated();

        $InsuranceProviderId = InsuranceProvider::findOrFail($validated['InsuranceProviderId']);
        $Product = InsuranceProduct::findOrFail($validated['Product']);

        $providers = InsuranceProductRiderService::create(
            $InsuranceProviderId,
            $Product,
            $validated['RiderName'],
            $validated['Description'] ?? '',
            $validated['AdditionalPremium'],
            $validated['IsOptional'] ?? null,
            $validated['IsActive'] ?? null,
            Auth::user(),
        );

        return redirect()->route('bancassurance.riders.index')->with('success', 'Rider added successfully.');
    }

    public function edit($Id)
    {
        $this->authorize(PermissionEnum::InsuranceProductRiderView, InsuranceProductRider::class);
        $rider = InsuranceProductRider::findOrFail($Id);
        $providers = InsuranceProvider::all();
        $products = InsuranceProduct::where('InsuranceProviderID', $rider->InsuranceProviderId)->get();
        return view('bancassurance.riders.edit', compact('rider', 'providers', 'products'));
    }

    // Update product
    public function update(InsuranceProductRiderRequest $request, $id)
    {
        $this->authorize(PermissionEnum::InsuranceProductRiderUpdate, InsuranceProductRider::class);
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $rider = InsuranceProductRider::findOrFail($id);

            $rider->update([
                'InsuranceProviderId' => $validated['InsuranceProviderId'],
                'Product' => $validated['Product'],
                'RiderName' => $validated['RiderName'],
                'Description' => $validated['Description'] ?? '',
                'AdditionalPremium' => $validated['AdditionalPremium'],
                'IsOptional' => $validated['IsOptional'] ?? '',
                'IsActive' => $validated['IsActive'] ?? '',
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($rider)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Rider ');

            return redirect()->route('bancassurance.riders.index')->with('success', 'Rider updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update Rider:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update Rider'])->withInput();
        }
    }

    public function destroy($Id)
    {
        $this->authorize(PermissionEnum::InsuranceProductRiderDelete, InsuranceProductRider::class);
        try {
            $rider = InsuranceProductRider::findOrFail($Id);
            $rider->delete();

            return redirect()->route('bancassurance.riders.index')
                ->with('success', 'Rider Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting Rider: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Rider. Please try again.'])
                ->withInput();
        }
    }
}
