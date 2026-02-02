<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\ProviderAndProducts\InsuranceProviderRequest;
use App\Models\Core\Country;
use App\Models\Insurance\InsuranceProduct;
use App\Models\Insurance\InsuranceProvider;
use App\Services\Insurance\ProviderAndProducts\InsuranceProviderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InsuranceProviderController extends Controller
{
    public function index()
    {
        $providers = InsuranceProvider::all();

        return view('bancassurance.insurers.index', compact('providers'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::InsuranceProviderCreate, InsuranceProvider::class);
        $providers = InsuranceProvider::all();
        $Countrys = Country::all();

        return view('bancassurance.insurers.create', compact('providers', 'Countrys'));
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
            $validated['IsActive'] ?? null,
            Auth::user(),
        );

        return redirect()->route('bancassurance.insurers.index')->with('success', 'Insurance Provider registered.');
    }

    public function edit($Id)
    {
        $provider = InsuranceProvider::findOrFail($Id);
        $Countrys = Country::all();

        return view('bancassurance.insurers.edit', compact('provider', 'Countrys'));
    }

    public function viewProducts($Id)
    {

        $provider = InsuranceProvider::findOrFail($Id);
        $products = InsuranceProduct::where('InsuranceProviderID', $Id)->get();

        return view('bancassurance.insurers.products', compact('provider', 'products'));
    }

    public function update(InsuranceProviderRequest $request, $id)
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

            if ($provider->getProductByProvider()->exists()) {
                return redirect()->back()
                    ->withErrors(['error' => 'This Provider is in use and cannot be deleted.']);
            }
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
}
