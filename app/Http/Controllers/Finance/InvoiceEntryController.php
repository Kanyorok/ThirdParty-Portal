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
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Ramsey\Collection\Queue;

class InvoiceEntryController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableView, FinanceInvoiceEntry::class);

        $invoices = FinanceInvoiceEntry::with('suppliers:Id,SupplierName')
            ->get();

        return view('finance.accountspayable.invoiceentry.index', compact('invoices'));
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

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceInvoiceEntry::class);
        //return$request->all();
        $validated = $request->validate([
            'InvoiceNumber'=> 'required|string',
            'SupplierID'=> 'required|exists:t_Suppliers,Id',
            'CurrencyID'=> 'required|exists:t_Currencies,Id',
            'ExchangeRate'=> 'required|numeric|min:0',
            'POReference'=> 'required|exists:t_Orders,OrderNo',
            'GRNReference'=> 'required|exists:t_GoodsReceipts,GRNID',
            'InvoiceDate'=> 'required|date',
            'InvoiceAmount'=> 'required|numeric',
            'Description'=> 'required|string|max:255',
        ]);



        //gets the selected PO and GRN from the request
        $poOrderNo = $validated['POReference'];
        $poId = Order::where('OrderNo', $poOrderNo)->value('Id');
        $grnId = $validated['GRNReference'];

        // $poItemsID = FacadesDB::table('t_OrderLines')
        //     ->where('iOrderID', $poId)
        //     ->pluck('Id') //represents the unique Orderline Ids based on
        //     ->toArray();

        try{

        $grnItems = FacadesDB::table('t_GoodsReceipts')
            ->where('GRNID', $grnId)
            ->get();
        $sum=0;
        foreach($grnItems as $item) {
            $GRN_ID = $item->id;
            $itemID=$item->ItemNo;
            $grnItemQty=$item->ReceivedQTY;
            $poItemqty = $item->POQTY; //represents the unique Orderline Ids based on
            if($grnItemQty !== $poItemqty) {
                return back()->with('error' , 'GRN quantity does not match PO quantity for item.');
            }

            $linestotal = FacadesDB::table('t_OrderLines')
                ->where('iStockCodeID', $itemID)
                ->where('iOrderID', $poId)
                ->value('LineTotal'); //represents the unique Orderline Ids based on
            $sum += $linestotal;
            }
            $invoiceTotAmount = $validated['InvoiceAmount'];

            if($sum != $invoiceTotAmount){
                return back()->with('error', 'Invoice amount does not match PO total amount.');
            }
            //Currency Exchange Rates Details

// return 0;

        FacadesDB::beginTransaction();

        $invoice =  FinanceInvoiceEntry::create([
            'InvoiceNumber'=> $validated['InvoiceNumber'],
            'SupplierID'=> $validated['SupplierID'],
            'CurrencyID'=> $validated['CurrencyID'],
            'ExchangeRate'=> $validated['ExchangeRate'],
            'POReference'=> $poId,
            'GRNReference'=> $GRN_ID,
            'InvoiceDate'=> $validated['InvoiceDate'],
            'InvoiceAmount'=> $validated['InvoiceAmount'],
            'Description'=> $validated['Description'],
            'CreatedBy'          =>Auth::Id(),
            'ModifiedBy'         => Auth::Id(),
        ]);


         activity()
            ->performedOn($invoice)
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'create'])
            ->log('Created Invoice:' . $invoice->InvoiceNumber);

            FacadesDB::commit();

            return redirect()->route('invoiceentry.index')->with('Success','Invoice created successfully');
    }catch(\Throwable $th){
            FacadesDB::rollback();
            return $th->getMessage();
            Log::error('Failed to Create Invoice'. $th->getMessage());

            return back()->withError('Error','Failed to create Invoice:' .$th->getMessage());

        }
    }

    public function getOrders($selectedVendor)
    {
        $orders = Order::where('AccountID', $selectedVendor)->get();
        return response()->json($orders);
    }

    public function getGRNs($selectedPO)
    {
       // return $selectedPO;
        $grns = FacadesDB::table('t_GoodsReceipts')
            ->select(FacadesDB::raw('MIN(id) as id'), 'GRNID')
            ->where('POID', $selectedPO)
            ->where('InspectionStatus','p') // Pick ones that are posted or approved
            ->groupBy('GRNID')
            ->get();
            return response()->json($grns);
    }


    public function viewPOModal($selectedPO)
        {
            // Get PO details
            $po = FacadesDB::table('t_Orders')->where('OrderNo', $selectedPO)->first();
            $orderID=$po->Id;
            if (!$po) {
                return response()->json(['error' => 'PO not found'], 404);
            }

            // Get supplier name
            $supplier = FacadesDB::table('t_Suppliers')->where('Id', $po->AccountID)->first();

            // Get PO line items with ItemName from t_Items
            $items = FacadesDB::table('t_OrderLines as ol')
                ->leftJoin('t_Items as i', 'ol.iStockCodeID', '=', 'i.Id')
                ->where('ol.iOrderID', $orderID)
                ->get()
                ->map(function ($item) {
                    return [
                        'ItemName'    => $item->ItemName ?? '',
                        'Description' => $item->Description ?? '',
                        'Quantity'    => $item->fQuantity ?? '',
                        'UnitCost'    => $item->fUnitPriceExcl ?? '',
                    ];
                });

            // Build response
            $data = [
                'OrderNo'      => $po->OrderNo ?? '',
                'SupplierName' => $supplier->SupplierName ?? '',
                'OrderDate'    => $po->OrderDate ?? '',
                'items'        => $items,
            ];

            return response()->json($data);
        }

    public function saveInvoice(Request $request)
    {
        //gets the selected PO and GRN from the request
        $poId = $request->input('POReference');
        $grnId = $request->input('GRNReference');

        // $poItemsID = FacadesDB::table('t_OrderLines')
        //     ->where('iOrderID', $poId)
        //     ->pluck('Id') //represents the unique Orderline Ids based on
        //     ->toArray();

        FacadesDB::beginTransaction();

        try {
        $grnItems = FacadesDB::table('t_GoodsReceipts')
            ->where('POID', $poId)
            ->where('GRNID', $grnId)
            ->get();

        foreach($grnItems as $item) {
            $itemID=$item->ItemNo;
            $grnItemQty=$item->qty;
            $poItemqty = FacadesDB::table('t_OrderLines')
                ->where('iOrderID', $poId)
                ->where('iStockCodeID', $itemID)
                ->value('fQuantity');
            if($grnItemQty !== $poItemqty) {
                return back()->with('error' , 'GRN quantity does not match PO quantity for item.');
            }

            $poTotAmount = FacadesDB::table('t_Orders')
                ->where('Id', $poId)
                ->value('OrdTotExcl');

            $invoiceTotAmount = $request->input('InvoiceAmount');

            if($poTotAmount !== $invoiceTotAmount){
                return back()->with('error', 'Invoice amount does not match PO total amount.');
            }
            FacadesDB::commit();
            return 200;
        }
        }catch (\Throwable $th) {
            FacadesDB::rollback();

            return  'Failed to validate GRN and PO';
        }
        //Currency Exchange Rates Details
    }

    public function show($id)
    {
        $invoice = FinanceInvoiceEntry::with([
            'supplier:Id,SupplierName',
            'currency:Id,Name,Code,Symbol',
            'order:Id,OrderNo,Description,OrdTotExcl',
            'grn:id,GRNID,SupplierId',
        ])->findOrFail($id);

        $poItems = [];
        if ($invoice->order) {
            $poItems = \DB::table('t_OrderLines as ol')
                ->leftJoin('t_Items as i', 'ol.iStockCodeID', '=', 'i.Id')
                ->where('ol.iOrderID', $invoice->order->Id)
                ->select(
                    'i.ItemName',
                    'i.ItemDescription as Description',
                    'ol.fQuantity as Quantity',
                    'ol.fUnitPriceExcl as UnitCost'
                )
                ->get();
        }

        return view('finance.accountspayable.invoiceentry.show', compact('invoice', 'poItems'));
    }

}
