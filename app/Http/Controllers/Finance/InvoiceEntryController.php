<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Finance\FinanceTransaction;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\Order;
use App\Models\Procurement\OrderLines;
use App\Models\ThirdParies\Supplier;
use App\Models\ThirdParty\ThirdParties;
use App\Services\Finance\TransactionService;
use Illuminate\Support\Facades\DB;
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

        $invoices = FinanceInvoiceEntry::with(['thirdParty:Id,ThirdPartyName', 'supplier:Id'])
            ->get();

        return view('finance.accountspayable.invoiceentry.index', compact('invoices'));
    }

    public function create(){

        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceInvoiceEntry::class);

        // Vendors: fetch from suppliers joined to third parties (value = Supplier.Id, also return ThirdPartyID)
        $suppliers = Supplier::query()
            ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 't_Suppliers.ThirdPartyID')
            ->select('t_Suppliers.Id', 't_Suppliers.ThirdPartyID', DB::raw("ISNULL(tp.TradingName, tp.ThirdPartyName) as SupplierName"))
            ->get();
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
        // return$request->all();
        $validated = $request->validate([
            'InvoiceNumber'=> 'required|string',
            'ThirdPartyID' => 'required|exists:t_ThirdParties,Id',
            'CurrencyID'=> 'required|exists:t_Currencies,Id',
            'ExchangeRate'=> 'required|numeric|min:0',
            'POReference'=> 'required|exists:t_Orders,OrderNo',
            'GRNReference'=> 'required|exists:t_GoodsReceipts,GRNID',
            'InvoiceDate'=> 'required|date',
            'InvoiceAmount'=> 'required|numeric',
            'Description'=> 'required|string|max:255',
            // File upload validation
            'file' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg',
        ], [
            'file.mimes' => 'Only PDF, Word, Excel, CSV, JPG, and PNG files are allowed.',
            'file.max'   => 'File size must not exceed 5 MB.',
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


            FacadesDB::beginTransaction();

            $thirdPartyId = (int)$validated['ThirdPartyID'];
            // Optionally resolve a SupplierID that maps to this ThirdParty (for legacy FK compatibility)
            $legacySupplierId = FacadesDB::table('t_Suppliers')->where('ThirdPartyID', $thirdPartyId)->value('Id');

            $invoice =  FinanceInvoiceEntry::create([
                'InvoiceNumber'=> $validated['InvoiceNumber'],
                'SupplierID' => $legacySupplierId, // legacy field; prefer ThirdPartyID
                'ThirdPartyID' => $thirdPartyId,
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

            //File Upload
            if ($request->hasFile('file')) {
                $invoice->newDocument(
                    ModulesEnum::Finance, // or ModulesEnum::INVOICE if you have it
                    $request->file('file'),
                    [PermissionEnum::FinanceAccountsPayableCreate, PermissionEnum::FinanceAccountsPayableView], // Permissions
                    Auth::user()
                );
            }

            activity()
                ->performedOn($invoice)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created Invoice:' . $invoice->InvoiceNumber);

            FacadesDB::commit();

            return redirect()->route('invoiceentry.index')->with('success','Invoice created successfully');
        }catch(\Throwable $th){
            FacadesDB::rollback();
            //return $th->getMessage();
            Log::error('Failed to Create Invoice'. $th->getMessage());

            return back()->withError('error','Failed to create Invoice:' .$th->getMessage());

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

        // Get supplier third party name
        $supplier = FacadesDB::table('t_Suppliers as s')
            ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
            ->where('s.Id', $po->AccountID)
            ->select(DB::raw("ISNULL(tp.TradingName, tp.ThirdPartyName) as SupplierName"))
            ->first();

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

    public function viewGRNModal($grnId)
    {
        $lines = FacadesDB::table('t_GoodsReceipts as gr')
            ->leftJoin('t_Items as i', 'gr.ItemNo', '=', 'i.Id')
            ->leftJoin('t_Orders as o', 'gr.POID', '=', 'o.OrderNo')
            ->leftJoin('t_Suppliers as s', 'o.AccountID', '=', 's.Id')
            ->leftJoin('t_ThirdParties as tp', 's.ThirdPartyID', '=', 'tp.Id')
            ->where('gr.GRNID', $grnId)
            ->select(
                'gr.GRNID', 'gr.POID',
                FacadesDB::raw("ISNULL(tp.TradingName, tp.ThirdPartyName) as SupplierName"),
                'i.ItemName', 'i.ItemDescription as Description',
                'gr.POQTY as OrderedQty', 'gr.ReceivedQTY as ReceivedQty'
            )
            ->get();

        if ($lines->isEmpty()) {
            return response()->json(['error' => 'GRN not found'], 404);
        }

        $sumOrdered = (float)$lines->sum(fn($li) => (float)($li->OrderedQty ?? 0));
        $sumReceived = (float)$lines->sum(fn($li) => (float)($li->ReceivedQty ?? 0));

        return response()->json([
            'GRNID' => $grnId,
            'POID' => $lines->first()->POID,
            'SupplierName' => $lines->first()->SupplierName,
            'items' => $lines->map(fn($li) => [
                'ItemName' => $li->ItemName ?? '',
                'Description' => $li->Description ?? '',
                'OrderedQty' => $li->OrderedQty ?? 0,
                'ReceivedQty' => $li->ReceivedQty ?? 0,
            ]),
            'totals' => [
                'ordered' => $sumOrdered,
                'received' => $sumReceived,
            ],
        ]);
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
            'thirdParty:Id,ThirdPartyName,TradingName',
            'currency:Id,Name,Code,Symbol',
            'order:Id,OrderNo,Description,OrdTotExcl',
            'grn:id,GRNID,SupplierId',
            'createdBy:Id,Name',
        ])->findOrFail($id);

        $poItems = collect();
        $poSub = 0.0;

        if ($invoice->order) {
            $poItems = DB::table('t_OrderLines as ol')
                ->leftJoin('t_Items as i', 'ol.iStockCodeID', '=', 'i.Id')
                ->where('ol.iOrderID', $invoice->order->Id)
                ->select(
                    'i.ItemName',
                    'i.ItemDescription as Description',
                    'ol.fQuantity as Quantity',
                    'ol.fUnitPriceExcl as UnitCost'
                )
                ->get();

            $poSub = $poItems->sum(fn($li) => (float)$li->UnitCost * (float)$li->Quantity);
        }

        // Prepare view data
        $viewData = [
            'currencyCode'   => $invoice->currency->Code ?? '',
            'currencySymbol' => $invoice->currency->Symbol ?? '',
            'invNo'          => $invoice->InvoiceNumber ?? '—',
            'invDate'        => $invoice->InvoiceDate
                ? \Carbon\Carbon::parse($invoice->InvoiceDate)->format('d M Y')
                : '—',
            'amount'         => number_format((float)($invoice->InvoiceAmount ?? 0), 2),
            'exRate'         => $invoice->ExchangeRate ?? 1.0,
            'vendorName' => ($invoice->thirdParty->TradingName ?? $invoice->thirdParty->ThirdPartyName) ?? '—',
            'poNo'           => $invoice->order->OrderNo ?? '—',
            'grnNo'          => $invoice->grn->GRNID ?? '—',
            'poSub'          => $poSub,
        ];

        return view('finance.accountspayable.invoiceentry.show', compact('invoice', 'poItems') + $viewData);
    }


    public function approve(Request $request, int $id, TransactionService $svc)
    {
        // $this->authorize('approve-ap-invoice', FinanceInvoiceEntry::class);

        $validated = $request->validate([
            'Reason' => 'required|string|max:255',
        ]);

        // Configure your module + transaction type mapping IDs
        // Make sure these exist in t_Modules and t_FinanceTransactionTypes
        $MODULE_ID          = 1100000; // Finance module
        $TRANSACTION_TYPEID = 15;    // "AP Invoice"

        try {
            return DB::transaction(function () use ($id, $validated, $svc, $MODULE_ID, $TRANSACTION_TYPEID) {

                // Load the invoice with the same relations, and lock row for update
                $invoice = FinanceInvoiceEntry::with([
                    // Use thirdParty since SupplierName column doesn't exist on t_Suppliers
                    'thirdParty:Id,TradingName,ThirdPartyName',
                    'currency:Id,Name,Code,Symbol',
                    'order:Id,OrderNo,Description,OrdTotExcl',
                    'grn:id,GRNID,SupplierId',
                    'createdBy:Id,Name',
                ])
                    ->lockForUpdate()
                    ->findOrFail($id);

                // Guard: already posted?
                if (strtolower((string)$invoice->ApprovalStatus) === 'posted') {
                    return back()->with('error', "Invoice $invoice->InvoiceNumber is already posted.");
                }

                // Build payload for TransactionService (service does idempotency)
                $payload = [
                    'ModuleID'          => $MODULE_ID,
                    'ThirdPartyID'=>$invoice->SupplierID,
                    'TransactionTypeID' => $TRANSACTION_TYPEID,
                    'TransactionType'   => 'Account Payable Invoice',
                    'ReferenceNumber'   => $invoice->InvoiceNumber,
                    'TransactionDate'   => $invoice->InvoiceDate ?? now()->toDateString(),
                    'Amount'            => (float)($invoice->InvoiceAmount ?? 0),   // net (excl. tax) if that's your model
                    'TaxAmount'         => (float)($invoice->TaxAmount ?? 0),      // 0 if not captured
                    'BranchID'          => session('LoginBranchId', 1),
                    'DepartmentID'      => $invoice->DepartmentID ?? null,
                    'CurrencyID'        => $invoice->CurrencyID ?? 1,
                    'CurrencyCode'      => optional($invoice->currency)->Code ?? 'KES',
                    'ExchangeRate'      => (float)($invoice->ExchangeRate ?? 1),
                    'Narration'         => trim(($invoice->Description ?? '').' '.$validated['Reason']),
                    'SourceTable'       => 't_FinanceInvoiceEntries',
                    'SystemDescription' => 'AP Invoice '.$invoice->InvoiceNumber,
                    // Optional one‑off overrides if needed:
                    // 'DebitGLAccountID'  => 5_001,
                    // 'CreditGLAccountID' => 3_001,
                    // 'TaxGLAccountID'    => 2_101,
                ];
                // Post via mapping; TransactionService handles:
                // - mapping lookup
                // - idempotency (no duplicates)
                // - validation + balancing
                // - persistence (single DB txn internally)
                $result = $svc->postFromTypeMapping($payload);

                // Update invoice approval status if posted (or keep as-is if service reported 'exists')
                if (in_array($result['status'], ['success', 'exists'], true)) {
                    $invoice->update([
                        'ApprovalStatus' => 'posted',
                        'ApprovalReason' => $validated['Reason'],
                        'ModifiedBy'     => Auth::id(),
                        'ModifiedOn'     => now(),
                    ]);
                }

                // Prefer a user-friendly flash message
                $message = $result['status'] === 'exists'
                    ? "Invoice {$invoice->InvoiceNumber} was already posted (idempotent)."
                    : ($result['message'] ?? "Invoice {$invoice->InvoiceNumber} posted successfully.");

                $flashKey = $result['status'] === 'success' ? 'success' : 'info';

                activity('Transaction Posting')
                    ->performedOn(new FinanceTransaction())
                    ->causedBy(Auth::id())
                    ->withProperties(['Posting Transaction' => 'Posted from Account payable Invoice'])
                    ->log('Posted Transaction from Accounts Payable Invoice');

                return back()->with($flashKey, $message);
            });
        } catch (\Throwable $e) {
            // Log if you want: Log::error('AP approve error', ['id'=>$id, 'err'=>$e->getMessage()])
            //return $e->getMessage();
            return back()->with('error', "Approval/Post failed: ".$e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $invoice = FinanceInvoiceEntry::findOrFail($id);

        if ($invoice->Status !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending invoices can be rejected');
        }

        $invoice->update([
            'Status' => 'Rejected',
            'RejectedBy' => Auth::id(),
            'RejectedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->back()->with('success', 'Invoice rejected successfully');
    }

    public function edit($id)
    {
        $invoice = FinanceInvoiceEntry::findOrFail($id);
        $suppliers = Supplier::select('Id', 'SupplierName')->get();
        $orders = Order::select('Id', 'AccountID', 'Description', 'OrdTotExcl', 'OrderNo')->get();
        $currencies = Currency::select('Id', 'Code')->get();
        $grns = GoodsReceipt::select('Id', 'GRNID', 'SupplierId')->get();

        return view('finance.accountspayable.invoiceentry.edit', compact('invoice', 'suppliers', 'orders', 'currencies', 'grns'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'InvoiceNumber' => 'required|string',
            'SupplierID' => 'required|integer',
            'CurrencyID' => 'required|integer',
            'ExchangeRate' => 'required|numeric',
            'POReference' => 'nullable|string',
            'GRNReference' => 'nullable|string',
            'InvoiceDate' => 'required|date',
            'InvoiceAmount' => 'required|numeric',
            'Description' => 'nullable|string',
        ]);

        $invoice = FinanceInvoiceEntry::findOrFail($id);

        $invoice->update([
            'InvoiceNumber' => $validated['InvoiceNumber'],
            'SupplierID' => $validated['SupplierID'],
            'CurrencyID' => $validated['CurrencyID'],
            'ExchangeRate' => $validated['ExchangeRate'],
            'POReference' => $validated['POReference'] ?? null,
            'GRNReference' => $validated['GRNReference'] ?? null,
            'InvoiceDate' => $validated['InvoiceDate'],
            'InvoiceAmount' => $validated['InvoiceAmount'],
            'Description' => $validated['Description'] ?? null,
            'ModifiedBy' => Auth::id() ?? 1,
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('invoiceentry.index')->with('success', 'Invoice updated successfully');
    }

    public function destroy($id)
    {
        $entries = FinanceInvoiceEntry::findOrFail($id);
        $entries->DeletedBy = Auth::id();
        $entries->save();
        $entries->delete();

        return redirect()->route('invoiceentry.index')->with('success', 'Invoice deleted successfully');
    }
}
