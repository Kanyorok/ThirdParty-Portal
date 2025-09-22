<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Procurement\Order;
use App\Models\Procurement\GoodsReceipt;
use App\Models\ThirdParies\Supplier;
use App\Models\Core\Currency;
use App\Services\Finance\TransactionService;
use Illuminate\Http\JsonResponse;

class InvoiceEntryController extends Controller
{
    public function index()
    {
        $invoices = FinanceInvoiceEntry::limit(50)->get();
        return view('finance.accountspayable.invoiceentry.index', compact('invoices'));
    }

    public function create()
    {
        $suppliers = Supplier::select('Id', 'SupplierName')->get();
        $orders = Order::select('Id', 'AccountID', 'Description', 'OrdTotExcl', 'OrderNo')->get();
        $currencies = Currency::select('Id', 'Code')->get();
        $grns = GoodsReceipt::select('Id', 'GRNID', 'SupplierId')->get();

        return view('finance.accountspayable.invoiceentry.create', compact('suppliers', 'orders', 'currencies', 'grns'));
    }

    public function store(Request $request)
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

        DB::beginTransaction();
        try {
            $invoice = FinanceInvoiceEntry::create([
                'InvoiceNumber' => $validated['InvoiceNumber'],
                'SupplierID' => $validated['SupplierID'],
                'CurrencyID' => $validated['CurrencyID'],
                'ExchangeRate' => $validated['ExchangeRate'],
                'POReference' => $validated['POReference'] ?? null,
                'GRNReference' => $validated['GRNReference'] ?? null,
                'InvoiceDate' => $validated['InvoiceDate'],
                'InvoiceAmount' => $validated['InvoiceAmount'],
                'Description' => $validated['Description'] ?? null,
                'CreatedBy' => Auth::id() ?? 1,
                'ModifiedBy' => Auth::id() ?? 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]);

            DB::commit();
            return redirect()->route('invoiceentry.index')->with('success', 'Invoice created successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create invoice: ' . $th->getMessage());
        }
    }

    public function getOrders($selectedVendor): JsonResponse
    {
        $orders = DB::table('t_Orders')
            ->where('AccountID', $selectedVendor)
            ->select('Id', 'OrderNo', 'Description', 'OrdTotExcl')
            ->get();

        return response()->json($orders);
    }

    public function getGRNs($selectedPO): JsonResponse
    {
        $grns = DB::table('t_GoodsReceipt')
            ->where('OrderID', $selectedPO)
            ->select('Id', 'GRNID')
            ->get();

        return response()->json($grns);
    }

    public function viewPOModal($selectedPO): JsonResponse
    {
        $po = DB::table('t_Orders')->where('OrderNo', $selectedPO)->first();
        if (!$po) {
            return response()->json(['error' => 'PO not found'], 404);
        }

        $supplier = DB::table('t_Suppliers')->where('Id', $po->AccountID)->first();

        $items = DB::table('t_OrderLines as ol')
            ->leftJoin('t_Items as i', 'ol.iStockCodeID', '=', 'i.Id')
            ->where('ol.iOrderID', $po->Id)
            ->get()
            ->map(function ($item) {
                return [
                    'ItemName' => $item->ItemName ?? '',
                    'Description' => $item->Description ?? '',
                    'Quantity' => $item->fQuantity ?? '',
                    'UnitCost' => $item->fUnitPriceExcl ?? '',
                ];
            });

        return response()->json([
            'supplier' => $supplier,
            'items' => $items
        ]);
    }

    public function show($id)
    {
        $invoice = FinanceInvoiceEntry::findOrFail($id);
        return view('finance.accountspayable.invoiceentry.show', compact('invoice'));
    }

    public function approve(Request $request, int $id, TransactionService $svc = null)
    {
        $invoice = FinanceInvoiceEntry::findOrFail($id);

        if ($invoice->Status !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending invoices can be approved');
        }

        DB::beginTransaction();
        try {
            $invoice->update([
                'Status' => 'Approved',
                'ApprovedBy' => Auth::id(),
                'ApprovedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Create accounting entries if service is provided
            // if ($svc) {
            //     $svc->createInvoiceEntries($invoice);
            // }

            DB::commit();
            return redirect()->back()->with('success', 'Invoice approved successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to approve invoice: ' . $th->getMessage());
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
