<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Finance\FinanceInvoiceEntry;
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
        return view('finance.accountspayable.invoiceentry.create');
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
            ]);

            // optional activity log
            if (function_exists('activity')) {
                activity()->performedOn($invoice)->causedBy(Auth::user())->log('Created Invoice: ' . $invoice->InvoiceNumber);
            }

            DB::commit();
            return back()->with('success', 'Invoice created.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Invoice create failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    public function getOrders($selectedVendor): JsonResponse
    {
        $orders = DB::table('t_Orders')->where('AccountID', $selectedVendor)->get();
        return response()->json($orders);
    }

    public function getGRNs($selectedPO): JsonResponse
    {
        $grns = DB::table('t_GoodsReceipts')
            ->select(DB::raw('MIN(id) as id'), 'GRNID')
            ->where('POID', $selectedPO)
            ->where('InspectionStatus', 'p')
            ->groupBy('GRNID')
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
                    'Quantity' => $item->fUnitPriceExcl ?? '',
                ];
            });

        return response()->json([
            'supplier' => $supplier,
            'items' => $items
        ]);
    }
}
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
                            ]);

                            if (function_exists('activity')) {
                                activity()->performedOn($invoice)->causedBy(Auth::user())->log('Created Invoice: ' . $invoice->InvoiceNumber);
                            }

                            DB::commit();
                            return back()->with('success', 'Invoice created.');
                        } catch (\Throwable $e) {
                            DB::rollBack();
                            Log::error('Invoice create failed: ' . $e->getMessage());
                            return back()->with('error', 'Failed to create invoice: ' . $e->getMessage());
                        }
                    }

                    public function getOrders($selectedVendor): JsonResponse
                    {
                        $orders = DB::table('t_Orders')->where('AccountID', $selectedVendor)->get();
                        return response()->json($orders);
                    }

                    public function getGRNs($selectedPO): JsonResponse
                    {
                        $grns = DB::table('t_GoodsReceipts')
                            ->select(DB::raw('MIN(id) as id'), 'GRNID')
                            ->where('POID', $selectedPO)
                            ->where('InspectionStatus', 'p')
                            ->groupBy('GRNID')
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

                        $data = [
                            'OrderNo' => $po->OrderNo ?? '',
                            'SupplierName' => $supplier->SupplierName ?? '',
                            'OrderDate' => $po->OrderDate ?? '',
                            'items' => $items,
                        ];

                        return response()->json($data);
                    }

                    public function show($id)
                    {
                        $invoice = FinanceInvoiceEntry::findOrFail($id);
                        return view('finance.accountspayable.invoiceentry.show', compact('invoice'));
                    }

                    public function approve(Request $request, int $id, TransactionService $svc = null)
                    {
                        $validated = $request->validate(['Reason' => 'required|string|max:255']);
                        try {
                            return DB::transaction(function () use ($id, $validated, $svc) {
                                $invoice = FinanceInvoiceEntry::findOrFail($id);
                                if (strtolower((string)$invoice->ApprovalStatus) === 'posted') {
                                    return back()->with('error', "Invoice {$invoice->InvoiceNumber} is already posted.");
                                }
                                if ($svc) {
                                    $payload = ['ModuleID' => 1100000, 'ThirdPartyID' => $invoice->SupplierID, 'TransactionTypeID' => 15, 'ReferenceNumber' => $invoice->InvoiceNumber, 'TransactionDate' => $invoice->InvoiceDate ?? now()->toDateString(), 'Amount' => (float)($invoice->InvoiceAmount ?? 0), 'SourceTable' => 't_FinanceInvoiceEntries'];
                                    $result = $svc->postFromTypeMapping($payload);
                                    if (in_array($result['status'], ['success', 'exists'], true)) {
                                        $invoice->update(['ApprovalStatus' => 'posted', 'ApprovalReason' => $validated['Reason'], 'ModifiedBy' => Auth::id(), 'ModifiedOn' => now()]);
                                    }
                                } else {
                                    $invoice->update(['ApprovalStatus' => 'posted', 'ApprovalReason' => $validated['Reason'], 'ModifiedBy' => Auth::id(), 'ModifiedOn' => now()]);
                                }
                                return back()->with('success', 'Invoice processed.');
                            });
                        } catch (\Throwable $e) {
                            Log::error('AP approve error: ' . $e->getMessage());
                            return back()->with('error', 'Approval/Post failed: ' . $e->getMessage());
                        }
                    }

                    public function reject(Request $request, $id)
                    {
                        $validated = $request->validate(['Reason' => 'required|string|max:1000']);
                        try {
                            return DB::transaction(function () use ($validated, $id) {
                                $invoice = FinanceInvoiceEntry::findOrFail($id);
                                if (in_array($invoice->ApprovalStatus, ['posted', 'rejected'], true)) {
                                    return back()->with('error', "Invoice {$invoice->InvoiceNumber} is already processed.");
                                }
                                $invoice->update(['ApprovalStatus' => 'rejected', 'ApprovalReason' => $validated['Reason'], 'ModifiedBy' => Auth::id(), 'ModifiedOn' => now()]);
                                return back()->with('success', "Invoice {$invoice->InvoiceNumber} rejected successfully.");
                            });
                        } catch (\Throwable $e) {
                            Log::error('AP reject error: ' . $e->getMessage());
                            return back()->with('error', 'Approval/Post failed: ' . $e->getMessage());
                        }
                    }

                    public function edit($id)
                    {
                        $invoice = FinanceInvoiceEntry::findOrFail($id);
                        $suppliers = Supplier::select('Id', 'SupplierName')->get();
                        $orders = Order::select('Id','AccountID','Description','OrdTotExcl','OrderNo')->get();
                        $currencies = Currency::select('Id', 'Code')->get();
                        return view('finance.accountspayable.invoiceentry.edit', compact('invoice', 'suppliers', 'orders', 'currencies'));
                    }

                    public function update(Request $request, $id)
                    {
                        $invoice = FinanceInvoiceEntry::findOrFail($id);
                        $validated = $request->validate(['InvoiceNumber'=> 'required|string','SupplierID'=> 'required|integer','CurrencyID'=> 'required|integer','ExchangeRate'=> 'required|numeric|min:0','InvoiceDate'=> 'required|date','InvoiceAmount'=> 'required|numeric','Description'=> 'nullable|string|max:255',]);
                        try {
                            DB::beginTransaction();
                            $invoice->update(['InvoiceNumber'=>$validated['InvoiceNumber'],'SupplierID'=>$validated['SupplierID'],'CurrencyID'=>$validated['CurrencyID'],'ExchangeRate'=>$validated['ExchangeRate'],'InvoiceDate'=>$validated['InvoiceDate'],'InvoiceAmount'=>$validated['InvoiceAmount'],'Description'=>$validated['Description'] ?? null,'ModifiedBy'=>Auth::id()]);
                            DB::commit();
                            return redirect()->route('invoiceentry.index')->with('success', 'Invoice updated successfully');
                        } catch (\Throwable $th) {
                            DB::rollback();
                            Log::error('Failed to Update Invoice: ' . $th->getMessage());
                            return back()->withError('error', 'Failed to update Invoice: ' . $th->getMessage());
                        }
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
            'vendorName'     => $invoice->supplier->SupplierName ?? '—',
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
                    'supplier:Id,SupplierName',
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
        $validated = $request->validate([
            'Reason' => 'required|string|max:1000',
        ]);
        try {
            return DB::transaction(function () use ($validated, $id) {
                // Lock the row for update to avoid race conditions
                $invoice = FinanceInvoiceEntry::with([
                    'supplier:Id,SupplierName',
                    'currency:Id,Name,Code,Symbol',
                    'order:Id,OrderNo,Description,OrdTotExcl',
                    'grn:id,GRNID,SupplierId',
                    'createdBy:Id,Name',
                ])
                    ->lockForUpdate()
                    ->findOrFail($id);

                // If already processed, prevent duplicate rejection
                if (in_array($invoice->ApprovalStatus, ['posted', 'rejected'], true)) {
                    $apStatus=ucfirst($invoice->ApprovalStatus);
                    return back()->with('error', "Invoice {$invoice->InvoiceNumber} is already {$apStatus}.");
                }

                // Update status & reason
                $invoice->update([
                    'ApprovalStatus' => 'rejected',
                    'ApprovalReason' => $validated['Reason'],
                    'ModifiedBy'     => Auth::id(),
                    'ModifiedOn'     => now(),
                ]);

                activity('Transaction Posting')
                    ->performedOn(new FinanceInvoiceEntry())
                    ->causedBy(Auth::id())
                    ->withProperties(['Posting Transaction' => 'Rejected from Account payable Invoice'])
                    ->log('Rejected Transaction from Accounts Payable Invoice');

                return back()->with('success', "Invoice {$invoice->InvoiceNumber} rejected successfully.");
            });
        }catch (\Throwable $e) {
            Log::error('AP reject error', ['id'=>$id, 'err'=>$e->getMessage()]);
            return back()->with('error', "Approval/Post failed: ".$e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceInvoiceEntry::class);

        $invoice = FinanceInvoiceEntry::with([
            'supplier:Id,SupplierName',
            'currency:Id,Name,Code,Symbol',
            'order:Id,OrderNo,Description,OrdTotExcl',
            'grn:id,GRNID,SupplierId',
        ])->findOrFail($id);

        $suppliers = Supplier::select('Id', 'SupplierName')->get();
        $orders = Order::select('Id','AccountID','Description','OrdTotExcl','OrderNo')
            ->get();
        $currencies = Currency::select('Id', 'Code')->get();
        $grns = GoodsReceipt::select('Id', 'GRNID', 'SupplierId')
            ->get();
        $orderLines = OrderLines::select('Id','iOrderID','fQuantity','fTaxRate','fUnitPriceExcl','LineTotal')
            ->get();

        return view('finance.accountspayable.invoiceentry.edit', compact(
            'invoice', 'suppliers', 'orders', 'currencies', 'grns', 'orderLines'
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceInvoiceEntry::class);

        $invoice = FinanceInvoiceEntry::findOrFail($id);

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

        try {
            FacadesDB::beginTransaction();
            $grnItems = FacadesDB::table('t_GoodsReceipts')
                ->where('GRNID', $grnId)
                ->get();
            $sum = 0;
            foreach ($grnItems as $item) {
                $GRN_ID = $item->id;
                $itemID = $item->ItemNo;
                $grnItemQty = $item->ReceivedQTY;
                $poItemqty = $item->POQTY; //represents the unique Order
                if ($grnItemQty !== $poItemqty) {
                    return back()->with('error', 'GRN quantity does not match PO quantity for item.');
                }
                $linestotal = FacadesDB::table('t_OrderLines')
                    ->where('iStockCodeID', $itemID)
                    ->where('iOrderID', $poId)
                    ->value('LineTotal'); //represents the unique Orderline Ids based on
                $sum += $linestotal;
            }
            $invoiceTotAmount = $validated['InvoiceAmount'];
            if ($sum != $invoiceTotAmount) {
                return back()->with('error', 'Invoice amount does not match PO total amount.');
            }

            // Update invoice details
            $invoice->update([
                'InvoiceNumber' => $validated['InvoiceNumber'],
                'SupplierID' => $validated['SupplierID'],
                'CurrencyID' => $validated['CurrencyID'],
                'ExchangeRate' => $validated['ExchangeRate'],
                'POReference' => $poId,
                'GRNReference' => $GRN_ID,
                'InvoiceDate' => $validated['InvoiceDate'],
                'InvoiceAmount' => $validated['InvoiceAmount'],
                'Description' => $validated['Description'],
                'ModifiedBy' => Auth::id(),
            ]);

            // File Upload
            if ($request->hasFile('file')) {
                $invoice->newDocument(
                    ModulesEnum::Finance,
                    $request->file('file'),
                    [PermissionEnum::FinanceAccountsPayableCreate, PermissionEnum::FinanceAccountsPayableView],
                    Auth::user()
                );
            }

            activity()
                ->performedOn($invoice)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Invoice: ' . $invoice->InvoiceNumber);

            FacadesDB::commit();
            return redirect()->route('invoiceentry.index')->with('success', 'Invoice updated successfully');
        } catch (\Throwable $th) {
            FacadesDB::rollback();
            Log::error('Failed to Update Invoice: ' . $th->getMessage());
            return back()->withError('error', 'Failed to update Invoice: ' . $th->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableDelete, FinanceInvoiceEntry::class);

        $entries = FinanceInvoiceEntry::findOrFail($id);
        $entries->DeletedBy = Auth::id();
        $entries->save();
        $entries->delete();

        return redirect()->route('invoiceentry.index')->with('success', 'Invoice deleted successfully');
    }
}
