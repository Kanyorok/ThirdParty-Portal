<?php

namespace App\Http\Controllers\CRM\Marketing\Competitor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\CompetitorProductRequest;
use App\Models\CRM\CompetitorProduct;
use App\Models\ThirdParies\Competitor;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class CompetitorProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Competitor $competitor): JsonResponse
    {
        $this->authorize('view', $competitor);
        return Datatables::of($competitor->products()->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
            /*->addColumn('action', function (CompetitorProduct $product) {
                return '<button type="button" class="btn btn-info btn-sm discussion-details" data-info="'.$discussion->DiscussionID.'"><i class="fas fa-eye"></i> details</button>';
            })*/
            ->editColumn('Limit', function (CompetitorProduct $product) {
                return number_format($product->Limit, 2);
            })->editColumn('InterestRate', function (CompetitorProduct $product) {
                return number_format($product->InterestRate, 2);
            })->editColumn('Clients', function (CompetitorProduct $product) {
                return number_format($product->Clients);
            })->setRowClass('mouse_pointer user-select-none dbl-click-summary-data')->setRowData([
                                                                                                  'dbl_click_url' => function (CompetitorProduct $product) use ($competitor) {
                                                                                                    return route('competitor-products.show', [$competitor->CompetitorID, $product->Id]);
                                                                                                  },
                                                                                                  'summary_title' => 'Product Details',
                                                                                                 ])->make();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompetitorProductRequest $request, Competitor $competitor): JsonResponse
    {
        $this->authorize('update', $competitor);
        $competitor->products()->create(array_merge($request->validated(), [
                                                                            'CreatedBy'  => $request->user()->Id,
                                                                            'ModifiedBy' => $request->user()->Id,
                                                                           ]));

        return $this->succeeded('product added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Competitor $competitor, string $product_id): View
    {
        $this->authorize('view', $competitor);
        $product = $competitor->products()->where('Id', $product_id)->firstOrFail();
        return view('crm.marketing.competitors.product', compact('product', 'competitor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompetitorProductRequest $request, Competitor $competitor, string $product_id): JsonResponse
    {
        $this->authorize('update', $competitor);
        $product = $competitor->products()->where('Id', $product_id)->firstOrFail();
        $product->update(array_merge($request->validated(), [
                                                             'ModifiedBy' => $request->user()->Id,
                                                            ]));

        return $this->succeeded('product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Competitor $competitor, string $product_id): JsonResponse
    {
        $this->authorize('update', $competitor);

        $product = $competitor->products()->where('Id', $product_id)->firstOrFail();

        $product->forceFill([
                             'DeletedBy' => $request->user()->Id,
                             'DeletedOn' => now(),
                            ])->save();

        return $this->succeeded('product trashed successfully');
    }
}
