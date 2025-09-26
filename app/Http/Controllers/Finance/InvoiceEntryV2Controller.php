<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Procurement\Supplier;
use App\Models\ThirdParty\ThirdParties;
use App\Models\Core\CodeDetail;
use App\Models\Core\Currency;
use App\Services\Finance\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InvoiceEntryV2Controller extends Controller
{
    public function index()
    {
        $invoices = FinanceInvoiceEntry::with(['thirdParty'])
            ->orderBy('CreatedOn', 'desc')
            ->paginate(15);

        return view('finance.accountspayable.invoiceentry.index', compact('invoices'));
    }

    public function create()
    {
        $paymentMethods = CodeDetail::where('CodeID', 'PaymentMethod')
            ->orderBy('Description')
            ->get(['ID', 'Value', 'Description']);

        // Get default currency (ID 56 if exists, otherwise first available)
        $defaultCurrency = $this->getDefaultCurrency();

        return view('finance.accountspayable.invoiceentry.create-v2', compact('paymentMethods', 'defaultCurrency'));
    }

    public function show($id)
    {
        // Load invoice with relationships like the original controller
        $invoice = FinanceInvoiceEntry::with([
            'thirdParty:Id,ThirdPartyName,TradingName',
            'currency:Id,Name,Code,Symbol',
            'createdBy:Id,Name'
        ])->findOrFail($id);

        // Create mock order relationship if POId exists
        if ($invoice->POId) {
            $orderData = DB::table('t_Orders')->where('Id', $invoice->POId)->first();
            if ($orderData) {
                $invoice->order = (object)[
                    'Id' => $orderData->Id,
                    'OrderNo' => $orderData->OrderNo,
                    'Description' => $orderData->Description ?? '',
                    'OrdTotExcl' => $orderData->OrdTotExcl ?? 0
                ];
            }
        }

        // Create mock grn relationship if GRNId exists
        if ($invoice->GRNId) {
            $grnData = DB::table('t_GoodsReceipts')->where('id', $invoice->GRNId)->first();
            if ($grnData) {
                $invoice->grn = (object)[
                    'id' => $grnData->id,
                    'GRNID' => $grnData->GRNID,
                    'SupplierId' => $grnData->SupplierId ?? null
                ];
                // Log::info('GRN data loaded', ['grn_id' => $grnData->GRNID]);
            } else {
                // Log::info('No GRN data found for GRNId', ['GRNId' => $invoice->GRNId]);
            }
        } else {
            // Log::info('No GRNId in invoice', ['GRNId' => $invoice->GRNId]);
        }

        $poItems = collect();
        $poSub = 0.0;

        if ($invoice->order ?? false) {
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

            $poSub = $poItems->sum(fn($li) => (float)($li->UnitCost ?? 0) * (float)($li->Quantity ?? 0));
        }

        // Prepare view data exactly like original controller
        $viewData = [
            'currencyCode'   => $invoice->currency->Code ?? 'KSh',
            'currencySymbol' => $invoice->currency->Symbol ?? 'KSh',
            'invNo'          => $invoice->InvoiceNumber ?? '—',
            'invDate'        => $invoice->InvoiceDate
                ? \Carbon\Carbon::parse($invoice->InvoiceDate)->format('d M Y')
                : '—',
            'amount'         => number_format((float)($invoice->InvoiceAmount ?? 0), 2),
            'exRate'         => $invoice->ExchangeRate ?? 1.0,
            'vendorName'     => ($invoice->thirdParty->TradingName ?? $invoice->thirdParty->ThirdPartyName) ?? '—',
            'poNo'           => $invoice->order->OrderNo ?? '—',
            'grnNo'          => $invoice->grn->GRNID ?? '—',
            'poSub'          => $poSub,
        ];

        // Remove debug logging in production
        // Log::info('View data prepared', ['poNo' => $viewData['poNo'], 'grnNo' => $viewData['grnNo']]);

        return view('finance.accountspayable.invoiceentry.show', compact('invoice', 'poItems') + $viewData);
    }

    /**
     * AJAX: Quick supplier search for dropdown suggestions
     */
    public function quickSearchSuppliers(Request $request)
    {
        try {
            $request->validate([
                'search_term' => 'required|string|min:2'
            ]);

            $q = trim((string) $request->search_term);

            // Quick search in ThirdParties and join with Suppliers
            $suppliers = DB::table('t_ThirdParties as tp')
                ->join('t_Suppliers as s', 's.ThirdPartyID', '=', 'tp.Id')
                ->where(function($query) use ($q) {
                    $query->where('tp.RegistrationNumber', 'like', "%{$q}%")
                          ->orWhere('tp.Email', 'like', "%{$q}%")
                          ->orWhere('tp.Phone', 'like', "%{$q}%");
                })
                ->select(
                    's.Id as SupplierID',
                    'tp.Id as ThirdPartyID',
                    'tp.ThirdPartyName',
                    'tp.TradingName',
                    'tp.RegistrationNumber',
                    'tp.Email',
                    'tp.Phone'
                )
                ->limit(10) // Limit for performance
                ->get();

            return response()->json([
                'suppliers' => $suppliers->map(function($supplier) {
                    return [
                        'SupplierID' => $supplier->SupplierID,
                        'ThirdPartyID' => $supplier->ThirdPartyID,
                        'Name' => $supplier->TradingName ?: $supplier->ThirdPartyName,
                        'RegistrationNumber' => $supplier->RegistrationNumber,
                        'Email' => $supplier->Email,
                        'Phone' => $supplier->Phone,
                        'DisplayText' => ($supplier->TradingName ?: $supplier->ThirdPartyName) .
                                       ' (' . $supplier->RegistrationNumber . ') - ' .
                                       $supplier->Email
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Error in quickSearchSuppliers: ' . $e->getMessage());
            return response()->json(['error' => 'Search failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Find supplier + related POs and GRNs
     */
    public function findSupplier(Request $request)
    {
        try {
            // This method can accept either search_term or supplier_id
            if ($request->has('supplier_id')) {
                $request->validate(['supplier_id' => 'required|integer']);
                $supplierId = $request->supplier_id;

                // Get supplier details by ID
                $supplier = DB::table('t_ThirdParties as tp')
                    ->join('t_Suppliers as s', 's.ThirdPartyID', '=', 'tp.Id')
                    ->where('s.Id', $supplierId)
                    ->select(
                        's.Id as SupplierID',
                        'tp.Id as ThirdPartyID',
                        'tp.ThirdPartyName',
                        'tp.TradingName',
                        'tp.RegistrationNumber',
                        'tp.Email',
                        'tp.Phone'
                    )
                    ->first();
            } else {
                $request->validate(['search_term' => 'required|string|min:2']);
                $q = trim((string) $request->search_term);

                // Search in ThirdParties and join with Suppliers - LIMITED TO EMAIL, REG, PHONE ONLY
                $supplier = DB::table('t_ThirdParties as tp')
                    ->join('t_Suppliers as s', 's.ThirdPartyID', '=', 'tp.Id')
                    ->where(function($query) use ($q) {
                        $query->where('tp.RegistrationNumber', $q)
                              ->orWhere('tp.Email', $q)
                              ->orWhere('tp.Phone', $q)
                              ->orWhere('tp.RegistrationNumber', 'like', "%{$q}%")
                              ->orWhere('tp.Email', 'like', "%{$q}%")
                              ->orWhere('tp.Phone', 'like', "%{$q}%");
                    })
                    ->select(
                        's.Id as SupplierID',
                        'tp.Id as ThirdPartyID',
                        'tp.ThirdPartyName',
                        'tp.TradingName',
                        'tp.RegistrationNumber',
                        'tp.Email',
                        'tp.Phone'
                    )
                    ->first();
            }

            if (!$supplier) {
                return response()->json(['error' => 'Supplier not found. Please check the registration number, email, or phone number.'], 404);
            }

            // Get default currency for orders that don't have currency set
            $defaultCurrency = $this->getDefaultCurrency();

            // Get related Purchase Orders
            $orders = DB::table('t_Orders')
                ->where('AccountID', $supplier->SupplierID)
                ->where('Status', '!=', 'Draft') // Only approved/posted orders
                ->select('Id', 'OrderNo', 'Description', 'OrdTotExcl as TotalAmount', 'OrderDate'
                    // TODO: Uncomment when CurrencyID column is added to t_Orders table
                    // , 'CurrencyID'
                )
                ->orderBy('OrderDate', 'desc')
                ->get()
                ->map(function($order) use ($defaultCurrency) {
                    // TODO: Uncomment when CurrencyID column is available
                    // $currency = $order->CurrencyID ?
                    //     Currency::find($order->CurrencyID) :
                    //     $defaultCurrency;

                    // For now, manually set to currency ID 56 (or default)
                    $currency = Currency::find(56) ?: $defaultCurrency;

                    return [
                        'Id' => $order->Id,
                        'OrderNo' => $order->OrderNo,
                        'Description' => $order->Description ?? '',
                        'TotalAmount' => number_format((float)$order->TotalAmount, 2),
                        'OrderDate' => $order->OrderDate ? \Carbon\Carbon::parse($order->OrderDate)->format('d M Y') : '',
                        'Currency' => [
                            'Id' => $currency->Id ?? $defaultCurrency->Id,
                            'Code' => $currency->Code ?? $defaultCurrency->Code,
                            'Symbol' => $currency->Symbol ?? $defaultCurrency->Symbol
                        ],
                        'OrderLines' => $this->getOrderLines($order->Id)
                    ];
                });

            // Get GRNs for this supplier
            $grns = DB::table('t_GoodsReceipts as gr')
                ->join('t_Orders as o', 'gr.POID', '=', 'o.OrderNo')
                ->where('gr.SupplierId', $supplier->SupplierID)
                ->where('gr.InspectionStatus', 'p') // Posted only
                ->select(
                    'gr.GRNID',
                    'gr.POID as OrderNo',
                    'gr.ReceivedDate',
                    DB::raw('SUM(gr.POQTY) as TotalOrderedQty'),
                    DB::raw('SUM(gr.ReceivedQTY) as TotalReceivedQty')
                )
                ->groupBy('gr.GRNID', 'gr.POID', 'gr.ReceivedDate')
                ->orderBy('gr.ReceivedDate', 'desc')
                ->get()
                ->map(function($grn) {
                    return [
                        'GRNID' => $grn->GRNID,
                        'OrderNo' => $grn->OrderNo,
                        'ReceivedDate' => $grn->ReceivedDate ? \Carbon\Carbon::parse($grn->ReceivedDate)->format('d M Y') : '',
                        'TotalOrderedQty' => $grn->TotalOrderedQty ?? 0,
                        'TotalReceivedQty' => $grn->TotalReceivedQty ?? 0,
                        'Items' => $this->getGRNItems($grn->GRNID)
                    ];
                });

            return response()->json([
                'supplier' => [
                    'SupplierID' => $supplier->SupplierID,
                    'ThirdPartyID' => $supplier->ThirdPartyID,
                    'Name' => $supplier->TradingName ?: $supplier->ThirdPartyName,
                    'RegistrationNumber' => $supplier->RegistrationNumber,
                    'Email' => $supplier->Email,
                    'Phone' => $supplier->Phone,
                    'IsActive' => true // Default to active since we don't have this field
                ],
                'orders' => $orders,
                'grns' => $grns,
                'defaultCurrency' => [
                    'Id' => $defaultCurrency->Id,
                    'Code' => $defaultCurrency->Code,
                    'Symbol' => $defaultCurrency->Symbol
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in findSupplier: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Search failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get order lines for a specific order
     */
    private function getOrderLines($orderId)
    {
        try {
            return DB::table('t_OrderLines as ol')
                ->leftJoin('t_Items as i', 'ol.iStockCodeID', '=', 'i.Id')
                ->where('ol.iOrderID', $orderId)
                ->select(
                    DB::raw('COALESCE(i.ItemName, ol.cDescription, \'Unknown Item\') as ItemName'),
                    DB::raw('COALESCE(ol.fQuantity, 0) as Quantity'),
                    DB::raw('COALESCE(ol.fUnitPriceExcl, 0) as UnitPrice'),
                    DB::raw('COALESCE(ol.fQuantity, 0) * COALESCE(ol.fUnitPriceExcl, 0) as LineTotal')
                )
                ->get()
                ->map(function($line) {
                    return [
                        'ItemName' => $line->ItemName ?? 'Unknown Item',
                        'Quantity' => $line->Quantity ?? 0,
                        'UnitPrice' => number_format((float)$line->UnitPrice, 2),
                        'LineTotal' => number_format((float)$line->LineTotal, 2)
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error fetching order lines: ' . $e->getMessage(), [
                'orderId' => $orderId,
                'trace' => $e->getTraceAsString()
            ]);
            return collect([]); // Return empty collection on error
        }
    }

    /**
     * Get GRN items for a specific GRN
     */
    private function getGRNItems($grnId)
    {
        try {
            return DB::table('t_GoodsReceipts as gr')
                ->leftJoin('t_Items as i', 'gr.ItemNo', '=', 'i.Id')
                ->where('gr.GRNID', $grnId)
                ->select(
                    DB::raw('COALESCE(i.ItemName, \'Unknown Item\') as ItemName'),
                    DB::raw('COALESCE(gr.POQTY, 0) as POQTY'),
                    DB::raw('COALESCE(gr.ReceivedQTY, 0) as ReceivedQTY')
                )
                ->get()
                ->map(function($item) {
                    return [
                        'ItemName' => $item->ItemName ?? 'Unknown Item',
                        'POQTY' => $item->POQTY ?? 0,
                        'ReceivedQTY' => $item->ReceivedQTY ?? 0
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error fetching GRN items: ' . $e->getMessage(), [
                'grnId' => $grnId,
                'trace' => $e->getTraceAsString()
            ]);
            return collect([]); // Return empty collection on error
        }
    }

    /**
     * Store the invoice
     */
    public function store(Request $request)
    {
        try {
            Log::info('Invoice V2 store method called', ['request_data' => $request->all()]);

            $validated = $request->validate([
                'ThirdPartyID' => 'required|exists:t_ThirdParties,Id',
                'SupplierID' => 'required|exists:t_Suppliers,Id',
                'POReference' => 'required',
                'GRNReference' => 'required',
                'InvoiceNumber' => 'required|string|max:255',
                'InvoiceDate' => 'required|date',
                'DueDate' => 'required|date',
                'Amount' => 'required|numeric|min:0.01',
                'Description' => 'nullable|string',
                'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'
            ]);

            DB::beginTransaction();

            $invoice = FinanceInvoiceEntry::create([
                'ThirdPartyID' => $validated['ThirdPartyID'], // Store in correct field for relationship
                'SupplierID' => $validated['SupplierID'], // Also store SupplierID separately if needed
                'POId' => $validated['POReference'], // This is actually the PO ID from the form
                'POReference'=>  $validated['POReference'],
                'GRNId' => 1,//$validated['GRNReference'], //Set to one to avoid data type conversion since with po we can get the grn
                'GRNReference'=>1,//$validated['GRNReference'],
                'InvoiceNumber' => $validated['InvoiceNumber'],
                'InvoiceDate' => $validated['InvoiceDate'],
                'DueDate' => $validated['DueDate'],
                'InvoiceAmount' => $validated['Amount'],
                'Description' => $validated['Description'],
                'CurrencyID' => 56, // Set to default currency
                'ExchangeRate' => 1.0, // Set exchange rate to 1
                'Status' => 'Draft',
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now()
            ]);

            //File Upload
            if ($request->hasFile('attachment')) {
                $invoice->newDocument(
                    ModulesEnum::Finance, // or ModulesEnum::INVOICE if you have it
                    $request->file('attachment'),
                    [PermissionEnum::FinanceAccountsPayableCreate, PermissionEnum::FinanceAccountsPayableView], // Permissions
                    Auth::user()
                );
            }

            DB::commit();

            Log::info('Invoice V2 created successfully', ['invoice_id' => $invoice->Id]);

            return redirect()->route('finance.invoiceentry-v2.show', $invoice->Id)
                ->with('success', 'Invoice created successfully');

        }catch(\Throwable $th){
            return $th->getMessage();
        }
        catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation error creating invoice: ', $e->errors());
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating invoice: ' . $e->getMessage());
            return back()->with('error', 'Failed to create invoice: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update invoice
     */
    public function update(Request $request, $id)
    {
        try {
            $invoice = FinanceInvoiceEntry::findOrFail($id);

            $validated = $request->validate([
                'ThirdPartyID' => 'required|exists:t_ThirdParties,Id',
                'SupplierID' => 'required|exists:t_Suppliers,Id',
                'POReference' => 'required',
                'GRNReference' => 'required',
                'InvoiceNumber' => 'required|string|max:255',
                'InvoiceDate' => 'required|date',
                'DueDate' => 'required|date',
                'Amount' => 'required|numeric|min:0.01',
                'Description' => 'nullable|string'
            ]);

            $invoice->update([
                'SupplierID' => $validated['ThirdPartyID'],
                'POId' => $validated['POReference'], // This is actually the PO ID from the form
                'GRNId' => $validated['GRNReference'], // This is actually the GRN ID from the form
                'InvoiceNumber' => $validated['InvoiceNumber'],
                'InvoiceDate' => $validated['InvoiceDate'],
                'DueDate' => $validated['DueDate'],
                'InvoiceAmount' => $validated['Amount'],
                'Description' => $validated['Description'],
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now()
            ]);

            return redirect()->route('finance.invoiceentry-v2.show', $invoice->Id)
                ->with('success', 'Invoice updated successfully');

        } catch (\Exception $e) {
            Log::error('Error updating invoice: ' . $e->getMessage());
            return back()->with('error', 'Failed to update invoice: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Edit invoice
     */
    public function edit($id)
    {
        $invoice = FinanceInvoiceEntry::with(['thirdParty'])->findOrFail($id);

        // Suppliers: fetch from suppliers joined to third parties for the dropdown
        $suppliers = DB::table('t_Suppliers as s')
            ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
            ->select(
                's.Id',
                's.ThirdPartyID',
                DB::raw("COALESCE(tp.TradingName, tp.ThirdPartyName, 'Unknown Supplier') as SupplierName")
            )
            ->get();

        $orders = DB::table('t_Orders')->select('Id','AccountID','Description','OrdTotExcl','OrderNo')->get();

        $currencies = Currency::select('Id', 'Code')->get();

        $grns = DB::table('t_GoodsReceipts')->select('Id', 'GRNID', 'SupplierId')->get();

        return view('finance.accountspayable.invoiceentry.edit', compact('invoice', 'suppliers', 'orders', 'currencies', 'grns'));
    }

    /**
     * Get default currency (ID 56 if exists, otherwise first available)
     * Here i hav implemented a methos that will help get currency which is passed in the PO section.
     */
    private function getDefaultCurrency()
    {
        try {
            // Try to get currency with ID 56 first
            $currency = Currency::find(56);

            if ($currency) {
                return $currency;
            }

            // If ID 56 doesn't exist, try to find KES (Kenyan Shilling)
            $currency = Currency::where('Code', 'KES')->first();

            if ($currency) {
                return $currency;
            }

            // If KES doesn't exist, get the first available currency
            $currency = Currency::first();

            if ($currency) {
                return $currency;
            }

            // If no currencies exist, create a fallback object
            return (object) [
                'Id' => 56,
                'Code' => 'KES',
                'Symbol' => 'KSh',
                'Name' => 'Kenyan Shilling'
            ];

        } catch (\Exception $e) {
            Log::error('Error getting default currency: ' . $e->getMessage());

            // Return fallback currency
            return (object) [
                'Id' => 56,
                'Code' => 'KES',
                'Symbol' => 'KSh',
                'Name' => 'Kenyan Shilling'
            ];
        }
    }
}
