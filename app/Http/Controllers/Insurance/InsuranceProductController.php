<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\ProviderAndProducts\InsuranceProductRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Insurance\InsuranceProduct;
use App\Models\Insurance\InsuranceProvider;
use App\Services\Insurance\ProviderAndProducts\InsuranceProductService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InsuranceProductController extends Controller
{
    // Show all products
    public function index()
    {
        $products = InsuranceProduct::all();

        return view('bancassurance.products.index', compact('products'));
    }

    // Show create form
    public function create()
    {
        $this->authorize(PermissionEnum::InsuranceProductCreate, InsuranceProduct::class);
        $providers = InsuranceProvider::all()->where('IsActive', true);
        $producttypes = CodeDetail::where('CodeID', 'PolicyTypeId')->get();

        return view('bancassurance.products.create', compact('providers', 'producttypes'));
    }

    // Store product
    public function store(InsuranceProductRequest $request)
    {
        $this->authorize(PermissionEnum::InsuranceProductCreate, InsuranceProduct::class);

        $validated = $request->validated();

        $InsuranceProviderID = InsuranceProvider::findOrFail($validated['InsuranceProviderID']);

        $providers = InsuranceProductService::create(
            $InsuranceProviderID,
            $validated['Name'],
            $validated['Type'],
            $validated['Description'] ?? '',
            $validated['IsActive'] ?? null,
            Auth::user(),
        );

        return redirect()->route('bancassurance.products.index')->with('success', 'Product created successfully.');
    }

    // Edit product
    public function edit($Id)
    {
        $this->authorize(PermissionEnum::InsuranceProductView, InsuranceProduct::class);

        $product = InsuranceProduct::findOrFail($Id);
        $producttypes = CodeDetail::where('CodeID', 'PolicyTypeId')->get();
        $providers = InsuranceProvider::all();

        return view('bancassurance.products.edit', compact('product', 'providers', 'producttypes'));
    }

    // Update product
    public function update(InsuranceProductRequest $request, $id)
    {
        $this->authorize(PermissionEnum::InsuranceProductUpdate, InsuranceProduct::class);
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $product = InsuranceProduct::findOrFail($id);

            $product->update([
                'InsuranceProviderID' => $validated['InsuranceProviderID'],
                'Name' => $validated['Name'],
                'Type' => $validated['Type'],
                'Description' => $validated['Description'] ?? '',
                'IsActive' => $validated['IsActive'] ?? '',
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($product)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated insurance Product');

            return redirect()->route('bancassurance.products.index')->with('success', 'Insurance Product updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update Insurance Product:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update insurance Product'])->withInput();
        }
    }

    public function destroy($Id)
    {
        $this->authorize(PermissionEnum::InsuranceProductDelete, InsuranceProduct::class);

        try {
            $product = InsuranceProduct::findOrFail($Id);

            if ($product->policies()->exists()) {
                return redirect()->back()
                    ->withErrors(['error' => 'This Product is in use and cannot be deleted.']);
            }
            $product->delete();

            return redirect()->route('bancassurance.products.index')
                ->with('success', 'Insurance Product Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting Insurance Product: ' . $th->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Insurance Product. Please try again.'])
                ->withInput();
        }
    }
}
