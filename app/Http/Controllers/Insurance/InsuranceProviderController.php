<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Insurance\InsuranceProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\Core\PermissionEnum;
use App\Models\Insurance\InsuranceProvider;
use App\Services\Insurance\ProviderAndProducts\InsuranceProviderService;
use App\Http\Requests\Insurance\ProviderAndProducts\InsuranceProviderRequest;


class InsuranceProviderController extends Controller
{
    //
    public function create()
{
    $this->authorize(PermissionEnum::InsuranceProviderView, InsuranceProvider::class);
    $providers = InsuranceProvider::all();

    return view('bancassurance.insurers.create', compact('providers'));
}
public function store(InsuranceProviderRequest $request)
{
    $this->authorize(PermissionEnum::InsuranceProviderCreate, InsuranceProvider::class);
    $validated = $request->validated();


        $providers = InsuranceProviderService::create(
                $validated['Name'],
                $validated['Country'],
                $validated['ContactPerson'],
                $validated['Email'],
                $validated['Phone'],
                $validated['IsActive'],
                Auth::user(),
            );
    return redirect()->route('bancassurance.insurers.index')->with('success', 'Insurance Provider registered.');
}

public function index()
{
    $providers = InsuranceProvider::all();

    return view('bancassurance.insurers.index', compact('providers'));
}
public function edit($Id)
{
    $this->authorize(PermissionEnum::InsuranceProviderView, InsuranceProvider::class);
    $provider = InsuranceProvider::findOrFail($Id);

    return view('bancassurance.insurers.edit', compact('provider'));
}

public function viewProducts($Id)
{
    $this->authorize(PermissionEnum::InsuranceProviderView, InsuranceProvider::class);
    
    $provider = InsuranceProvider::findOrFail($Id);

    // Only fetch products linked to this provider
    $products = InsuranceProduct::where('InsuranceProviderID', $Id)->get();
    
    return view('bancassurance.insurers.products', compact('provider','products'));
}


    public function update (InsuranceProviderRequest $request, $id)
    {
        $this->authorize(PermissionEnum::InsuranceProviderUpdate, InsuranceProvider::class);
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $provider = InsuranceProvider::findOrFail($id);

            $provider->update([
                'Name' => $validated['Name'],
                'Country' => $validated['Country'],
                'ContactPerson' => $validated['ContactPerson'],
                'Email' => $validated['Email'],
                'Phone' => $validated['Phone'],
                'IsActive' => $validated['IsActive'] ?? '',
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($provider)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated insurance Provider');

            return redirect()->route('bancassurance.insurers.index')->with('success', 'Insurance Provider updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update Insurance Provider:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update insurance Provider'])->withInput();
        }
    }

    public function destroy($Id)
    {
        $this->authorize(PermissionEnum::InsuranceProviderDelete, InsuranceProvider::class);
        try {
            $provider = InsuranceProvider::findOrFail($Id);
            $provider->delete();

            return redirect()->route('bancassurance.insurers.index')
                ->with('success', 'Insurance Provider Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting Insurance Provider: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Insurance Provider. Please try again.'])
                ->withInput();
        }
    }


public function detachProduct($providerId, $productId)
{
    DB::table('t_InsuranceProviderProducts')
        ->where('InsuranceProviderID', $providerId)
        ->where('ProductID', $productId)
        ->update(['IsActive' => 0]);

    return redirect()->route('bancassurance.insurers.products', $providerId)->with('success', 'Product detached successfully.');
}


}
