<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\Order;
use App\Models\Procurement\OrderLines;
use App\Models\ThirdParies\Supplier;
use Illuminate\Http\Request;

class InvoiceEntryController extends Controller
{
    public function index()
    {
        return view('finance.accountspayable.invoiceentry.index');
    }

    public function create(){

        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceInvoiceEntry::class);

        $suppliers = Supplier::select('Id', 'SupplierName')->get();
        $orders = Order::select('Id','AccountID','Description','OrdTotExcl','OrderNo')
            ->get();
        $currencies = Currency::select('Id', 'Code')->get();
        $grns = GoodsReceipt::select('Id', 'GRNID', 'SupplierId')
            ->get();
        $orderLines = OrderLines::select('Id','iOrderID','fQuantity','fTaxRate','fUnitPriceExcl','LineTotal')
            ->get();
        return view('finance.accountspayable.invoiceentry.create',compact('suppliers', 'orders', 'currencies', 'grns', 'orderLines'));
    }

    public function getOrders($selectedVendor)
    {
        $orders = Order::where('AccountID', $selectedVendor)->get();
        return response()->json($orders);
    }

    public function getGRNs($selectedPO)
    {
        $grns = GoodsReceipt::where('POID', $selectedPO)->get();
        return response()->json($grns);
    }

}
