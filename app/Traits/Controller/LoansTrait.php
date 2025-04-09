<?php

namespace App\Traits\Controller;

use App\Models\BR\DebtProduct;
use Exception;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

trait LoansTrait
{

    public function getLoans($query): JsonResponse
    {
        try {
            return Datatables::of($query)->addIndexColumn()
                ->editColumn('ClientID', function ($debtProduct) {
                    return '<a href="javascript:void(0)" data-click_url="' . route('clients.summary', $debtProduct->ClientID) . '" data-summary_title="member summary" class="click-summary-data">' . $debtProduct->AccountName . '</a>';
                })->editColumn('OutstandingBalance', function (DebtProduct $debtProduct) {
                    return number_format(abs($debtProduct->OutstandingBalance), 2);
                })->editColumn('MaturityDate', function (DebtProduct $debtProduct) {
                    return $debtProduct->MaturityDate?->format('d M, Y');
                })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                    'dbl_click_url' => function (DebtProduct $debtProduct) {
                        return route('debt-collection.show', $debtProduct->AccountID);
                    }
                ])->rawColumns(['ClientID'])->make();
        } catch (Exception $e) {
        }
        return $this->errored('fetching data failed, try again later');
    }
}
