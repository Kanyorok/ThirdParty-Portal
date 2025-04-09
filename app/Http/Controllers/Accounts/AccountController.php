<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\BR\Account;
use App\Models\BR\Branch;
use App\Models\BR\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): View | JsonResponse
    {
        if ($request->ajax()){
            $search = $request->get('q');
            $search = (is_string($search)) ? str_replace(['*','%'], ['',''], $search) : '';

            $query = (empty($search))?collect([])
                : Account::query()->where('AccountID','like',"%$search%")->orWhere('Name','like',"%$search%")
                    ->lock('WITH(NOLOCK)')->with(['branch', 'product','status'])->limit(10)->get('*');

            return Datatables::of($query)/*->addIndexColumn()*/
            ->editColumn('product.Description', function (Account $account) {
                if ($account->product instanceof Product){
                    return $account->product->Description;
                }
                return '';
            })->editColumn('AccountID', function (Account $account) {
            })->editColumn('AccountID', function (Account $account) {
                return '<a href="#" data-click_url="' . route('accounts.summary', $account->AccountID) . '" data-summary_title="account summary" class="click-summary-data">' . $account->AccountID . '</a>';
            })->editColumn('branch.BranchName', function (Account $account) {
                if ($account->branch instanceof Branch){
                    return $account->branch->BranchName;
                }
                return '';
            })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                'dbl_click_url' => function (Account $account) {
                    return route('accounts.show', $account->AccountID);
                }
            ])->rawColumns(['AccountID'])->make();
        }

        return view('accounts.index');
    }

    /**
     * Display the specified resource.
     * @throws Exception
     */
    public function show(Request $request, Account $account): View|JsonResponse
    {
        if ($request->ajax()){
            return Datatables::of($account->transactions()->with(['type'])->select('*'))->addIndexColumn()
                /*->editColumn('ClearBalance', function (Account $account) {
                    return '<span style="cursor: pointer;" class="clear-balance" data-bal="'.number_format($account->ClearBalance,4).'">**********</span>';
                })->editColumn('product.Description', function (Account $account) {
                    if ($account->product instanceof Product){
                        return $account->product->Description;
                    }
                    return '';
                })->editColumn('LastCreditTrxDate', function (Account $account) {
                    if (!$account->LastDebitTrxDate instanceof Carbon){
                        if ($account->LastCreditTrxDate instanceof Carbon){
                            return $account->LastCreditTrxDate;
                        }
                        return '';
                    }
                    if (!$account->LastCreditTrxDate instanceof Carbon){
                        return $account->LastDebitTrxDate;
                    }
                    if ($account->LastDebitTrxDate->gte($account->LastCreditTrxDate)){
                        return $account->LastDebitTrxDate;
                    }
                    return$account->LastCreditTrxDate;

                })->rawColumns(['ClearBalance'])*/->make();
        }


        return view('accounts.show', compact('account', ));
    }
}
