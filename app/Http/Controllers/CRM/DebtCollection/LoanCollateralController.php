<?php

namespace App\Http\Controllers\CRM\DebtCollection;

use App\Http\Controllers\Controller;
use App\Models\BR\CollateralAccount;
use App\Models\BR\DebtProduct;
use Exception;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class LoanCollateralController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     * @throws Exception
     */
    public function __invoke(string $product_id): JsonResponse
    {
        $product = DebtProduct::query()->where('AccountID', $product_id)->oldest('processDate')->first();
        if (! $product instanceof DebtProduct) {
            throw new Exception('Product not found, maybe closed.');
        }
        $this->authorize('view', $product);

        return Datatables::of($product->collaterals()->with('collateral')->select('*'))->addIndexColumn()
            ->editColumn('CreatedOn', function (CollateralAccount $collateralAccount) {
                return $collateralAccount->CreatedOn?->format('d M, Y');
            })->editColumn('NetCollateralValue', function (CollateralAccount $collateralAccount) {
                return number_format($collateralAccount->NetCollateralValue, 2);
            })->make();
    }
}
