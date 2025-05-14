<?php

namespace App\Http\Controllers\CRM\Leads;

use App\Http\Controllers\Controller;
use App\Models\BR\Product;
use App\Models\CRM\Lead;
use App\Models\CRM\LeadProduct;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class LeadProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Lead $lead): JsonResponse
    {
        return Datatables::of($lead->products()->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
            ->addColumn('action', function (LeadProduct $product) use ($lead) {
                return '<button type="button" data-click_url="' . route('lead-products.show', [$lead->LeadID, $product->Id]) . '" data-summary_title="intrested product summary" class="btn btn-info btn-sm click-summary-data"><i class="fas fa-eye"></i> details</button>';
            })->editColumn('CreatedOn', function (LeadProduct $product) {
                return $product->CreatedOn?->format('F d, Y h:i A');
            })->editColumn('Notes', function (LeadProduct $product) {
                return Str::limit($product->Notes);
            })->rawColumns(['action'])->make();
    }

    /**
     * Add a product
     * @throws ValidationException
     */
    public function store(Request $request, Lead $lead): JsonResponse
    {
        $request->validate([
                            'lead_product'  => ['required'],
                            'product_notes' => [
                                                'nullable',
                                                'string',
                                                'max:1000',
                                               ],
                           ]);

        $product = Product::query()->where('ProductID', $request->lead_product)->select(['ProductID', 'Description'])->first(['ProductID', 'Description']);
        if (!$product instanceof Product) {
            throw ValidationException::withMessages(['lead_product' => 'Product not found']);
        }

        $lead->products()->create([
                                   'ProductID'   => $product->ProductID,
                                   'ProductName' => $product->Description,
                                   'Notes'       => $request->product_notes,
                                   'CreatedBy'   => $request->user()->Id,
                                   'ModifiedBy'  => $request->user()->Id,
                                  ]);

        return $this->succeeded('Product added successfully');
    }

    public function show(Lead $lead, string $lead_product_id): View|JsonResponse
    {
        $leadProduct = $lead->products()->where('Id', $lead_product_id)->with(['product'])->first();
        if (!$leadProduct instanceof LeadProduct) {
            return $this->errored('product not found');
        }
        return view(
            'crm.leads.product',
            compact('lead', 'leadProduct')
        );
    }

    /**
     * Remove a product.
     */
    public function destroy(Request $request, Lead $lead, string $lead_product_id): JsonResponse
    {
        $leadProduct = $lead->products()->where('Id', $lead_product_id)->with(['product'])->first();
        if (!$leadProduct instanceof LeadProduct) {
            return $this->errored('product not found');
        }

        $leadProduct->forceFill([
                                 'DeletedBy' => $request->user()->Id,
                                 'DeletedOn' => now(),
                                ])->save();

        return $this->succeeded('product trashed successfully');
    }
}
