<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Procurement\Supplier;
use App\Models\ThirdParty\ThirdParties;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceTaxRuleConfiguration;
use App\Services\Finance\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class InvoiceEntryV2Controller extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableView, FinanceInvoiceEntry::class);
        // Build query with filters
        $query = FinanceInvoiceEntry::with(['thirdParty', 'currency']);

        // Apply filters if provided
        if ($request->filled('vendor_name')) {
            $query->whereHas('thirdParty', function($q) use ($request) {
                $q->where('ThirdPartyName', 'like', '%' . $request->vendor_name . '%')
                  ->orWhere('TradingName', 'like', '%' . $request->vendor_name . '%');
            });
        }

        if ($request->filled('invoice_number')) {
            $query->where('InvoiceNumber', 'like', '%' . $request->invoice_number . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('InvoiceDate', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('InvoiceDate', '<=', $request->date_to);
        }

        if ($request->filled('amount_min')) {
            $query->where('InvoiceAmount', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('InvoiceAmount', '<=', $request->amount_max);
        }

        if ($request->filled('approval_status') && $request->approval_status !== 'all') {
            $query->where('ApprovalStatus', $request->approval_status);
        }

        // Apply sorting
        $sortField = $request->sort_by ?? 'CreatedOn';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        // Paginate results
        $perPage = $request->per_page ?? 15;
        $invoices = $query->paginate($perPage)->withQueryString();

        // Get filter options for dropdowns
        $approvalStatuses = FinanceInvoiceEntry::distinct()
            ->pluck('ApprovalStatus')
            ->filter()
            ->unique()
            ->values();

        return view('finance.accountspayable.invoiceentry.index', compact(
            'invoices',
            'approvalStatuses'
        ));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceInvoiceEntry::class);
        $paymentMethods = CodeDetail::where('CodeID', 'PaymentMethod')
            ->orderBy('Description')
            ->get(['ID', 'Value', 'Description']);

        // Get default currency (ID 56 if exists, otherwise first available)
        $defaultCurrency = $this->getDefaultCurrency();

        return view('finance.accountspayable.invoiceentry.create-v2', compact('paymentMethods', 'defaultCurrency'));
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableView, FinanceInvoiceEntry::class);
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
                    'OrdTotExcl' => $orderData->OrdTotExcl ?? 0,
                    'TaxPercentage' => $orderData->TaxPercentage ?? 0,
                    'TaxID' => $orderData->TaxID ?? null
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
                    DB::raw('COALESCE(ol.fQuantity, 0) as Quantity'),
                    DB::raw('COALESCE(ol.fUnitPriceExcl, 0) as UnitCost')
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
            'amount'         => number_format((float)($invoice->TotalAmount ?? 0), 2),
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
        $this->authorize(PermissionEnum::FinanceAccountsPayableView, FinanceInvoiceEntry::class);
        try {
            // Support both Select2 (q) and our previous (search_term) parameter styles
            $q = trim((string) ($request->input('search_term') ?? $request->input('q') ?? ''));

            if (mb_strlen($q) < 2) {
                return response()->json([
                    'results' => [],
                    'suppliers' => [],
                    'message' => 'Please type at least 2 characters'
                ], 200);
            }

            // Quick search in ThirdParties and join with Suppliers
            // Collapse duplicates by ThirdPartyID (choose a stable SupplierID via MIN)
            $suppliers = DB::table('t_ThirdParties as tp')
                ->join('t_Suppliers as s', 's.ThirdPartyID', '=', 'tp.Id')
                ->where(function($query) use ($q) {
                    $query->where('tp.RegistrationNumber', 'like', "%{$q}%")
                          ->orWhere('tp.Email', 'like', "%{$q}%")
                          ->orWhere('tp.Phone', 'like', "%{$q}%")
                          ->orWhere('tp.ThirdPartyName', 'like', "%{$q}%")
                          ->orWhere('tp.TradingName', 'like', "%{$q}%");
                })
                ->select(
                    DB::raw('MIN(s.Id) as SupplierID'),
                    'tp.Id as ThirdPartyID',
                    'tp.ThirdPartyName',
                    'tp.TradingName',
                    'tp.RegistrationNumber',
                    'tp.Email',
                    'tp.Phone'
                )
                ->groupBy(
                    'tp.Id',
                    'tp.ThirdPartyName',
                    'tp.TradingName',
                    'tp.RegistrationNumber',
                    'tp.Email',
                    'tp.Phone'
                )
                ->orderBy('tp.TradingName')
                ->limit(20)
                ->get()
                ->unique('ThirdPartyID')
                ->values();

            // Build common representation
            $mapped = $suppliers->map(function($supplier) {
                $name = $supplier->TradingName ?: $supplier->ThirdPartyName;
                $display = trim($name) !== '' ? $name : 'Unknown Supplier';
                $displayText = $display .
                    ' (' . ($supplier->RegistrationNumber ?? '—') . ') - ' .
                    ($supplier->Email ?? '—');

                return [
                    'SupplierID' => $supplier->SupplierID,
                    'ThirdPartyID' => $supplier->ThirdPartyID,
                    'Name' => $display,
                    'RegistrationNumber' => $supplier->RegistrationNumber,
                    'Email' => $supplier->Email,
                    'Phone' => $supplier->Phone,
                    'DisplayText' => $displayText,
                ];
            });

            // Return both legacy and Select2-friendly formats to avoid breaking callers
            // Ensure uniqueness in results by ThirdPartyID to avoid Select2 duplicates
            $results = $mapped
                ->unique('ThirdPartyID')
                ->values()
                ->map(function ($s) {
                    return [
                        'id' => $s['SupplierID'],
                        'text' => $s['DisplayText'],
                        'supplier' => $s,
                    ];
                });

            return response()->json([
                'suppliers' => $mapped,
                'results' => $results,
                'pagination' => [ 'more' => false ],
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
        $this->authorize(PermissionEnum::FinanceAccountsPayableView, FinanceInvoiceEntry::class);
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
                        'tp.Phone',
                        'tp.PhysicalAddress',
                        'tp.BusinessType',
                        's.Active_Status'
                    )
                    ->first();
            } else {
                $request->validate(['search_term' => 'required|string|min:2']);
                $q = trim((string) $request->search_term);

                // Search in ThirdParties and join with Suppliers - Search in all relevant fields
                $supplier = DB::table('t_ThirdParties as tp')
                    ->join('t_Suppliers as s', 's.ThirdPartyID', '=', 'tp.Id')
                    ->where(function($query) use ($q) {
                        $query->where('tp.RegistrationNumber', $q)
                              ->orWhere('tp.Email', $q)
                              ->orWhere('tp.Phone', $q)
                              ->orWhere('tp.ThirdPartyName', 'like', "%{$q}%")
                              ->orWhere('tp.TradingName', 'like', "%{$q}%")
                              ->orWhere('tp.PhysicalAddress', 'like', "%{$q}%");
                    })
                    ->where('s.Active_Status', 1) // Only active suppliers
                    ->select(
                        's.Id as SupplierID',
                        'tp.Id as ThirdPartyID',
                        'tp.ThirdPartyName',
                        'tp.TradingName',
                        'tp.RegistrationNumber',
                        'tp.Email',
                        'tp.Phone',
                        'tp.PhysicalAddress',
                        'tp.BusinessType',
                        's.Active_Status'
                    )
                    ->first();
            }

            if (!$supplier) {
                return response()->json(['error' => 'Supplier not found. Please check the registration number, email, or phone number.'], 404);
            }

            // Get default currency for orders that don't have currency set
            $defaultCurrency = $this->getDefaultCurrency();

            // Get IDs of POs that are already referenced in invoices to avoid duplicates
            $existingPOIds = FinanceInvoiceEntry::pluck('POReference')->toArray();

            // Get related Purchase Orders for this ThirdParty via Supplier join, excluding those already used in invoices
            $orders = DB::table('t_Orders as o')
                ->join('t_Suppliers as s', 'o.AccountID', '=', 's.Id')
                ->where('s.ThirdPartyID', '=', (int) $supplier->ThirdPartyID)
                ->whereNotIn('o.Id', $existingPOIds)
                ->select(
                    'o.Id',
                    'o.OrderNo',
                    'o.Description',
                    DB::raw('COALESCE(o.OrdTotExcl, 0) as OrdTotExcl'),
                    DB::raw('COALESCE(o.OrdDiscAmnt, 0) as OrdDiscAmnt'),
                    DB::raw('COALESCE(o.TaxPercentage, 0) as TaxPercentage'),
                    'o.OrderDate'
                )
                ->orderByRaw(Schema::hasColumn('t_Orders', 'OrderDate') ? 'OrderDate desc' : 'Id desc')
                ->distinct()
                ->get()
                ->map(function($order) use ($defaultCurrency) {
                    // TODO: Uncomment when CurrencyID column is available
                    // $currency = $order->CurrencyID ?
                    //     Currency::find($order->CurrencyID) :
                    //     $defaultCurrency;

                    // For now, manually set to currency ID 56 (or default)
                    $currency = Currency::find(56) ?: $defaultCurrency;

                    $ordTotExcl = (float)$order->OrdTotExcl;
                    $taxPercentage = (float)$order->TaxPercentage;
                    $ordTotTax = $ordTotExcl * ($taxPercentage / 100);
                    $ordTotIncl = $ordTotExcl + $ordTotTax;

                    return [
                        'Id' => $order->Id,
                        'OrderNo' => $order->OrderNo,
                        'Description' => $order->Description ?? '',
                        'OrdTotExcl' => number_format($ordTotExcl, 2),
                        'OrdDiscAmnt' => number_format((float)$order->OrdDiscAmnt, 2),
                        'OrdTotTax' => number_format($ordTotTax, 2),
                        'OrdTotIncl' => number_format($ordTotIncl, 2),
                        // Backward compatibility: treat TotalAmount as inclusive amount
                        'TotalAmount' => number_format($ordTotIncl, 2),
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
                ->join('t_Suppliers as s', 'o.AccountID', '=', 's.Id')
                ->where('s.ThirdPartyID', '=', (int) $supplier->ThirdPartyID)
                ->where('gr.InspectionStatus', '=', 'p') // Posted only
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
                    'Address' => $supplier->PhysicalAddress,
                    'BusinessType' => $supplier->BusinessType,
                    'IsActive' => (bool) $supplier->Active_Status
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
        $this->authorize(PermissionEnum::FinanceAccountsPayableView, FinanceInvoiceEntry::class);
        try {
            $rows = DB::table('t_OrderLines as ol')
                ->leftJoin('t_Items as i', 'ol.iStockCodeID', '=', 'i.Id')
                ->leftJoin('t_FinanceTaxRuleConfiguration as trc', 'ol.TaxID', '=', 'trc.Id')
                ->leftJoin('t_FinanceTaxType as tt', 'trc.TaxTypeId', '=', 'tt.Id')
                ->where('ol.iOrderID', $orderId)
                ->select(
                    DB::raw('COALESCE(i.ItemName, ol.cDescription, \'Unknown Item\') as ItemName'),
                    DB::raw('COALESCE(i.ItemDescription, ol.cDescription, \'\') as ItemDescription'),
                    DB::raw('COALESCE(ol.fQuantity, 0) as fQuantity'),
                    DB::raw('COALESCE(ol.fUnitPriceExcl, 0) as fUnitPriceExcl'),
                    DB::raw('COALESCE(ol.fUnitPriceIncl, 0) as fUnitPriceIncl'),
                    DB::raw('COALESCE(ol.fLineDiscount, 0) as fLineDiscount'),
                    DB::raw('COALESCE(ol.LineTotal, 0) as LineTotal'),
                    'ol.TaxPercentage',
                    'tt.TaxTypeName'
                )
                ->get();

            return $rows->map(function ($r) {
                $quantity = (float)($r->fQuantity ?? 0);
                $unitExcl = (float)($r->fUnitPriceExcl ?? 0);
                $unitIncl = (float)($r->fUnitPriceIncl ?? 0);
                $discount = (float)($r->fLineDiscount ?? 0); // assume line amount
                // Use TaxPercentage only as per new requirement
                $taxRate = (float)($r->TaxPercentage ?? 0);
                $taxName = $r->TaxTypeName ?? 'Tax';

                $lineExcl = $quantity * $unitExcl;
                if ($unitIncl > 0) {
                    $lineInclGiven = $quantity * $unitIncl;
                    $lineTax = max(0.0, $lineInclGiven - max(0.0, $lineExcl - $discount));
                    $lineIncl = $r->LineTotal !== null ? (float)$r->LineTotal : $lineInclGiven;
                } else {
                    $lineTax = max(0.0, max(0.0, $lineExcl - $discount) * ($taxRate / 100.0));
                    $lineIncl = $r->LineTotal !== null ? (float)$r->LineTotal : max(0.0, $lineExcl - $discount + $lineTax);
                }

                return [
                    'ItemName' => $r->ItemName ?? 'Unknown Item',
                    'Description' => $r->ItemDescription ?? '',
                    'Quantity' => $quantity,
                    'UnitPriceExcl' => $unitExcl,
                    'UnitPriceIncl' => $unitIncl,
                    'Discount' => $discount,
                    'TaxRate' => $taxRate,
                    'TaxName' => $taxName,
                    'TaxAmount' => $lineTax,
                    'LineExclusive' => $lineExcl,
                    'LineInclusive' => $lineIncl,
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
        $this->authorize(PermissionEnum::FinanceAccountsPayableView, FinanceInvoiceEntry::class);
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
        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceInvoiceEntry::class);
        try {
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
                'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'TaxID' => 'nullable|exists:t_FinanceTaxRuleConfiguration,Id',
                'CurrencyID' => 'nullable|exists:t_Currencies,Id',
                'ExchangeRate' => 'nullable|numeric',
            ]);

            DB::beginTransaction();

            //Calculate Tax Amount and get Tax Percentage
            $taxAmount = 0;
            $taxPercentage = 0.0;
            if ($validated['TaxID']) {
                $taxConfig = FinanceTaxRuleConfiguration::find($validated['TaxID']);
                if ($taxConfig) {
                    $taxPercentage = (float)$taxConfig->Rate;
                    $taxAmount = round($validated['Amount'] * ($taxPercentage / 100), 2);
                }
            }

            $invoice = FinanceInvoiceEntry::create([
                //'ThirdPartyID' => $validated['ThirdPartyID'], // Store in correct field for relationship
                'SupplierID' => $validated['ThirdPartyID'], // Also store SupplierID separately if needed
                'POId' => $validated['POReference'], // This is actually the PO ID from the form
                'POReference'=>  $validated['POReference'],
                'GRNId' => 1,//$validated['GRNReference'], //Set to one to avoid data type conversion since with po we can get the grn
                'GRNReference'=>1,//$validated['GRNReference'],
                'InvoiceNumber' => $validated['InvoiceNumber'],
                'InvoiceDate' => $validated['InvoiceDate'],
                'DueDate' => $validated['DueDate'],
                // Store inclusive amount for posting/approval
                'InvoiceAmount' => (function() use ($validated) {
                    // Calculate based on PO if available
                    if (!empty($validated['POReference'])) {
                        $order = DB::table('t_Orders')->where('Id', (int)$validated['POReference'])->first();
                        if ($order) {
                            $excl = (float)($order->OrdTotExcl ?? 0);
                            $taxPct = (float)($order->TaxPercentage ?? 0);
                            $taxAmt = $excl * ($taxPct / 100);
                            return $excl + $taxAmt;
                        }
                    }
                    // Fallback to form amount (assumed inclusive if no PO logic)
                    return (float)$validated['Amount'];
                })(),
                'TaxAmount' => $taxAmount,
                'TaxPercentage' => $taxPercentage ?? 0.0,
                'Description' => $validated['Description'],
                'TaxID' => 1,//$validated['TaxID'] ?? 1,
                'TotalAmount' => (function() use ($validated) {
                    if (!empty($validated['POReference'])) {
                        $order = DB::table('t_Orders')->where('Id', (int)$validated['POReference'])->first();
                        if ($order) {
                            $excl = (float)($order->OrdTotExcl ?? 0);
                            $taxPct = (float)($order->TaxPercentage ?? 0);
                            $taxAmt = $excl * ($taxPct / 100);
                            return $excl + $taxAmt;
                        }
                    }
                    return (float)$validated['Amount'];
                })(),
                'CurrencyID' => $validated['CurrencyID'] ?? 56, // Set to default currency
                'ExchangeRate' => $validated['ExchangeRate'] ?? 1.0, // Set exchange rate to 1
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

            return redirect()->route('invoiceentry.show', $invoice->Id)
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
        $this->authorize(PermissionEnum::FinanceAccountsPayableUpdate, FinanceInvoiceEntry::class);
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

            return redirect()->route('invoiceentry.show', $invoice->Id)
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
        $this->authorize(PermissionEnum::FinanceAccountsPayableUpdate, FinanceInvoiceEntry::class);
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

        return view('finance.accountspayable.invoiceentry.edit-v2', compact('invoice', 'suppliers', 'orders', 'currencies', 'grns'));
    }

    /**
     * Delete invoice
     */
    public function destroy($id)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableDelete, FinanceInvoiceEntry::class);
            try {
            DB::beginTransaction();

            $invoice = FinanceInvoiceEntry::findOrFail($id);

            // Check if invoice can be deleted (only draft invoices can be deleted)
            if ($invoice->ApprovalStatus !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft invoices can be deleted. Approved or posted invoices cannot be removed.'
                ], 422);
            }

            // Get invoice number for logging
            $invoiceNumber = $invoice->InvoiceNumber;

            // Delete associated documents first
            if (method_exists($invoice, 'documents')) {
                $documents = $invoice->documents();
                if ($documents) {
                    foreach ($documents->get() as $document) {
                        // Use the document service to properly delete the document
                        if (method_exists($document, 'delete')) {
                            $document->delete();
                        }
                    }
                }
            }

            // Delete the invoice
            $invoice->delete();

            DB::commit();

            Log::info('Invoice deleted successfully', [
                'invoice_id' => $id,
                'invoice_number' => $invoiceNumber,
                'deleted_by' => Auth::id()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice deleted successfully'
                ]);
            }

            return redirect()->route('invoiceentry.index')
                ->with('success', 'Invoice deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting invoice: ' . $e->getMessage(), [
                'invoice_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete invoice: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
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
