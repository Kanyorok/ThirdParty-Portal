<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\APInvoiceMilestone;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Procurement\ContractMilestone;
use App\Models\Procurement\ContractPenaltyRule;
use App\Models\Procurement\RFQAward;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\TenderAward;
use App\Models\ThirdParty\ThirdParties;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceTaxRuleConfiguration;
use App\Services\Finance\ContractInvoiceEligibilityService;
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
        $contractOptions = $this->buildContractOptions();

        return view('finance.accountspayable.invoiceentry.create-v2', compact('paymentMethods', 'defaultCurrency', 'contractOptions'));
    }

    public function getContracts()
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceInvoiceEntry::class);
        return response()->json($this->buildContractOptions());
    }

    public function getContractMilestones(string $type, int $id)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceInvoiceEntry::class);

        if (!in_array($type, ['tender', 'rfq'], true)) {
            return response()->json(['error' => 'Invalid contract type.'], 422);
        }

        $milestonesCollection = ContractMilestone::with('checklistItems')
            ->where('ContractSourceType', $type)
            ->where('ContractSourceID', $id)
            ->orderBy('MilestoneNo')
            ->get();
        $contractValue = $this->resolveContractValue($type, $id);

        $billedByMilestone = $this->getMilestoneBilledTotals(
            $milestonesCollection->pluck('Id')->map(fn ($v) => (int) $v)->all(),
            $type,
            $id
        );

        $milestones = $milestonesCollection->map(function ($m) use ($billedByMilestone, $contractValue) {
                $requiredTotal = $m->checklistItems->where('Required', true)->count();
                $requiredDone = $m->checklistItems->where('Required', true)->where('IsFulfilled', true)->count();
                $workflowEligible = $m->Status === 'Waived'
                    || ($m->Status === 'Accepted' && $requiredTotal === $requiredDone);
                $valueAmount = $this->getMilestoneTargetAmount($m, $contractValue);
                $billedAmount = (float) ($billedByMilestone[(int) $m->Id] ?? 0);
                $remainingAmount = round(max(0, $valueAmount - $billedAmount), 2);
                $eligible = $workflowEligible && $remainingAmount > 0;

                return [
                    'Id' => $m->Id,
                    'MilestoneNo' => $m->MilestoneNo,
                    'Title' => $m->Title,
                    'Status' => $m->Status,
                    'PlannedDueDate' => $m->PlannedDueDate ? $m->PlannedDueDate->format('Y-m-d') : null,
                    'ValueType' => $m->ValueType,
                    'ValuePercent' => $m->ValuePercent,
                    'ValueAmount' => $valueAmount,
                    'ConfiguredValueAmount' => $m->ValueAmount,
                    'BilledAmount' => $billedAmount,
                    'RemainingAmount' => $remainingAmount,
                    'BillableAmount' => $remainingAmount,
                    'IsFullyBilled' => $remainingAmount <= 0.0,
                    'RequiredChecklistTotal' => $requiredTotal,
                    'RequiredChecklistFulfilled' => $requiredDone,
                    'ChecklistItems' => $m->checklistItems->map(function ($item) {
                        return [
                            'Id' => $item->Id,
                            'ItemDescription' => $item->ItemDescription,
                            'Required' => (bool) $item->Required,
                            'IsFulfilled' => (bool) $item->IsFulfilled,
                            'Notes' => $item->Notes,
                        ];
                    })->values(),
                    'Eligible' => $eligible,
                ];
            });

        $contractTotals = $this->getContractBillingSummary($type, $id);

        return response()->json([
            'contract_type' => $type,
            'contract_id' => $id,
            'milestones' => $milestones,
            'totals' => $contractTotals,
        ]);
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableView, FinanceInvoiceEntry::class);
        // Load invoice with relationships like the original controller
        $invoice = FinanceInvoiceEntry::with([
            'thirdParty:Id,ThirdPartyName,TradingName',
            'currency:Id,Name,Code,Symbol',
            'createdBy:Id,Name',
            'milestoneAllocations.milestone.checklistItems',
        ])->findOrFail($id);
        $isContractInvoice = strtoupper((string) ($invoice->InvoiceSourceType ?? 'PO')) === 'CONTRACT';
        if ($isContractInvoice) {
            app(ContractInvoiceEligibilityService::class)->refreshInvoiceHoldStatus($invoice);
            $invoice->refresh();
            $invoice->load([
                'thirdParty:Id,ThirdPartyName,TradingName',
                'currency:Id,Name,Code,Symbol',
                'createdBy:Id,Name',
                'milestoneAllocations.milestone.checklistItems',
            ]);
        }

        // Create mock order relationship if POId exists
        if ($invoice->POId) {
            $orderData = DB::table('t_Orders')->where('Id', $invoice->POId)->first();
            if ($orderData) {
                $invoice->order = (object)[
                    'Id' => $orderData->Id,
                    'OrderNo' => $orderData->OrderNo,
                    'Description' => $orderData->Description ?? '',
                    'OrderDate' => $orderData->OrderDate ?? null,
                    'OrdTotExcl' => $orderData->OrdTotExcl ?? 0,
                    'OrdDiscAmnt' => $orderData->OrdDiscAmnt ?? 0,
                    'OrdTotIncl' => $orderData->OrdTotIncl ?? null,
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
                    'POID' => $grnData->POID ?? null,
                    'ReceivedDate' => $grnData->ReceivedDate ?? null,
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
        $contractReference = null;
        $sourceReferenceLabel = null;
        $contractMilestoneDetails = collect();
        $matchedPoData = null;
        $matchedGrnData = null;
        $matchedGrnItems = collect();
        $matchedGrnTotals = [
            'ordered_qty' => 0.0,
            'received_qty' => 0.0,
        ];

        if ($isContractInvoice) {
            if (($invoice->ContractSourceType ?? null) === 'tender') {
                $contractReference = TenderAward::where('Id', (int) ($invoice->ContractSourceID ?? 0))
                        ->value('ContractRef')
                    ?: ('TENDER-CONTRACT-' . (int) ($invoice->ContractSourceID ?? 0));
            } elseif (($invoice->ContractSourceType ?? null) === 'rfq') {
                $contractReference = RFQAward::where('Id', (int) ($invoice->ContractSourceID ?? 0))
                        ->value('ContractRef')
                    ?: ('RFQ-CONTRACT-' . (int) ($invoice->ContractSourceID ?? 0));
            }

            $poItems = $invoice->milestoneAllocations
                ->sortBy(function ($allocation) {
                    return (int) ($allocation->milestone->MilestoneNo ?? PHP_INT_MAX);
                })
                ->values()
                ->map(function ($allocation) {
                    $milestone = $allocation->milestone;
                    $title = $milestone
                        ? ('M' . ($milestone->MilestoneNo ?? '?') . ' - ' . ($milestone->Title ?? ('Milestone ' . $allocation->MilestoneID)))
                        : ('Milestone ' . $allocation->MilestoneID);

                    $descriptionParts = [];
                    if ($milestone && !empty($milestone->PlannedDueDate)) {
                        $descriptionParts[] = 'Due ' . \Carbon\Carbon::parse($milestone->PlannedDueDate)->format('d M Y');
                    }
                    if ($milestone && !empty($milestone->Status)) {
                        $descriptionParts[] = 'Status: ' . $milestone->Status;
                    }

                    return (object) [
                        'ItemName' => $title,
                        'Description' => implode(' | ', $descriptionParts),
                        'Quantity' => 1,
                        'UnitCost' => (float) ($allocation->BilledAmount ?? 0),
                    ];
                });

            $poSub = $poItems->sum(fn ($li) => (float) ($li->UnitCost ?? 0) * (float) ($li->Quantity ?? 0));
            $sourceReferenceLabel = 'From Contract: ' . ($contractReference ?: 'N/A');

            $contractMilestoneDetails = $invoice->milestoneAllocations
                ->sortBy(function ($allocation) {
                    return (int) ($allocation->milestone->MilestoneNo ?? PHP_INT_MAX);
                })
                ->values()
                ->map(function ($allocation) {
                    $milestone = $allocation->milestone;
                    $checklistItems = $milestone?->checklistItems ?? collect();

                    $requiredTotal = $checklistItems->where('Required', true)->count();
                    $requiredDone = $checklistItems->where('Required', true)->where('IsFulfilled', true)->count();

                    return (object) [
                        'MilestoneID' => (int) ($allocation->MilestoneID ?? 0),
                        'MilestoneNo' => (int) ($milestone->MilestoneNo ?? 0),
                        'Title' => $milestone->Title ?? ('Milestone ' . (int) ($allocation->MilestoneID ?? 0)),
                        'Status' => $milestone->Status ?? null,
                        'PlannedDueDate' => $milestone?->PlannedDueDate,
                        'BilledAmount' => (float) ($allocation->BilledAmount ?? 0),
                        'RequiredChecklistTotal' => (int) $requiredTotal,
                        'RequiredChecklistFulfilled' => (int) $requiredDone,
                        'ChecklistItems' => $checklistItems->values(),
                    ];
                });
        } elseif ($invoice->order ?? false) {
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
            $sourceReferenceLabel = 'From PO: ' . ($invoice->order->OrderNo ?? '—');

            $ordTotExcl = (float) ($invoice->order->OrdTotExcl ?? 0);
            $ordTaxPct = (float) ($invoice->order->TaxPercentage ?? 0);
            $ordTotIncl = (float) ($invoice->order->OrdTotIncl ?? 0);
            if ($ordTotIncl <= 0) {
                $ordTotIncl = round($ordTotExcl + ($ordTotExcl * ($ordTaxPct / 100)), 2);
            }

            $matchedPoData = (object) [
                'OrderNo' => $invoice->order->OrderNo ?? null,
                'OrderDate' => $invoice->order->OrderDate ?? null,
                'OrdTotExcl' => $ordTotExcl,
                'OrdDiscAmnt' => (float) ($invoice->order->OrdDiscAmnt ?? 0),
                'OrdTotIncl' => $ordTotIncl,
                'TaxPercentage' => $ordTaxPct,
            ];

            if ($invoice->grn ?? false) {
                $matchedGrnRows = DB::table('t_GoodsReceipts as gr')
                    ->leftJoin('t_Items as i', 'gr.iStockCodeID', '=', 'i.Id')
                    ->where('gr.GRNID', (string) ($invoice->grn->GRNID ?? ''))
                    ->select(
                        'gr.GRNID',
                        'gr.POID',
                        'gr.ReceivedDate',
                        DB::raw("COALESCE(i.ItemName, 'Item') as ItemName"),
                        DB::raw('COALESCE(gr.POQTY, 0) as POQTY'),
                        DB::raw('COALESCE(gr.ReceivedQTY, 0) as ReceivedQTY')
                    )
                    ->get();

                if ($matchedGrnRows->isNotEmpty()) {
                    $firstGrn = $matchedGrnRows->first();
                    $matchedGrnData = (object) [
                        'GRNID' => $firstGrn->GRNID,
                        'POID' => $firstGrn->POID,
                        'ReceivedDate' => $firstGrn->ReceivedDate,
                    ];

                    $matchedGrnItems = $matchedGrnRows->map(function ($row) {
                        return (object) [
                            'ItemName' => $row->ItemName,
                            'POQTY' => (float) ($row->POQTY ?? 0),
                            'ReceivedQTY' => (float) ($row->ReceivedQTY ?? 0),
                        ];
                    })->values();

                    $matchedGrnTotals = [
                        'ordered_qty' => (float) $matchedGrnItems->sum('POQTY'),
                        'received_qty' => (float) $matchedGrnItems->sum('ReceivedQTY'),
                    ];
                } else {
                    $matchedGrnData = (object) [
                        'GRNID' => $invoice->grn->GRNID ?? null,
                        'POID' => $invoice->grn->POID ?? null,
                        'ReceivedDate' => $invoice->grn->ReceivedDate ?? null,
                    ];
                }
            }
        } else {
            $sourceReferenceLabel = 'From PO: —';
        }

        $invoiceBeforeTax = round((float) ($invoice->InvoiceAmount ?? $poSub), 2);
        $invoiceTaxPct = round((float) ($invoice->TaxPercentage ?? 0), 4);
        $invoiceTaxAmount = round((float) ($invoice->TaxAmount ?? 0), 2);
        $invoiceAfterTax = round((float) ($invoice->TotalAmount ?? ($invoiceBeforeTax + $invoiceTaxAmount)), 2);

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
            'isContractInvoice' => $isContractInvoice,
            'contractReference' => $contractReference,
            'sourceReferenceLabel' => $sourceReferenceLabel,
            'invoiceBeforeTax' => $invoiceBeforeTax,
            'invoiceTaxPct' => $invoiceTaxPct,
            'invoiceTaxAmount' => $invoiceTaxAmount,
            'invoiceAfterTax' => $invoiceAfterTax,
            'contractMilestoneDetails' => $contractMilestoneDetails,
            'matchedPoData' => $matchedPoData,
            'matchedGrnData' => $matchedGrnData,
            'matchedGrnItems' => $matchedGrnItems,
            'matchedGrnTotals' => $matchedGrnTotals,
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

            // Quick search in ThirdParties through SupplierMaster:
            // t_Suppliers -> t_SupplierMaster -> t_ThirdParties
            // Collapse duplicates by ThirdPartyID (choose a stable SupplierID via MIN)
            $suppliers = DB::table('t_Suppliers as s')
                ->join('t_SupplierMaster as sm', 'sm.Id', '=', 's.SupplierMasterId')
                ->join('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
                ->where(function($query) use ($q) {
                    $query->where('tp.RegistrationNumber', 'like', "%{$q}%")
                          ->orWhere('tp.Email', 'like', "%{$q}%")
                          ->orWhere('tp.Phone', 'like', "%{$q}%")
                          ->orWhere('tp.ThirdPartyName', 'like', "%{$q}%")
                          ->orWhere('tp.TradingName', 'like', "%{$q}%");
                })
                ->select(
                    DB::raw('MIN(s.Id) as SupplierID'),
                    'sm.ThirdPartyId as ThirdPartyID',
                    'tp.ThirdPartyName',
                    'tp.TradingName',
                    'tp.RegistrationNumber',
                    'tp.Email',
                    'tp.Phone'
                )
                ->groupBy(
                    'sm.ThirdPartyId',
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
                $supplier = DB::table('t_Suppliers as s')
                    ->join('t_SupplierMaster as sm', 'sm.Id', '=', 's.SupplierMasterId')
                    ->join('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
                    ->where('s.Id', $supplierId)
                    ->select(
                        's.Id as SupplierID',
                        'sm.ThirdPartyId as ThirdPartyID',
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
                $supplier = DB::table('t_Suppliers as s')
                    ->join('t_SupplierMaster as sm', 'sm.Id', '=', 's.SupplierMasterId')
                    ->join('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
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
                        'sm.ThirdPartyId as ThirdPartyID',
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
                ->join('t_SupplierMaster as sm', 'sm.Id', '=', 's.SupplierMasterId')
                ->where('sm.ThirdPartyId', '=', (int) $supplier->ThirdPartyID)
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
                ->join('t_SupplierMaster as sm', 'sm.Id', '=', 's.SupplierMasterId')
                ->where('sm.ThirdPartyId', '=', (int) $supplier->ThirdPartyID)
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
        $sourceType = strtoupper((string) $request->input('InvoiceSourceType', 'PO'));

        try {
            DB::beginTransaction();

            if ($sourceType === 'CONTRACT') {
                $validated = $request->validate([
                    'InvoiceSourceType' => 'required|in:PO,CONTRACT',
                    'ContractSourceType' => 'required|in:tender,rfq',
                    'ContractSourceID' => 'required|integer',
                    'MilestoneIDs' => 'required|array|min:1',
                    'MilestoneIDs.*' => 'required|integer|exists:t_ContractMilestones,Id',
                    'InvoiceNumber' => 'required|string|max:255',
                    'InvoiceDate' => 'required|date',
                    'DueDate' => 'required|date',
                    'Amount' => 'required|numeric|min:0.01',
                    'Description' => 'nullable|string',
                    'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                    'TaxID' => 'nullable|integer',
                    'CurrencyID' => 'nullable|exists:t_Currencies,Id',
                    'ExchangeRate' => 'nullable|numeric|min:0.0001',
                ]);

                $contractType = strtolower($validated['ContractSourceType']);
                $contractId = (int) $validated['ContractSourceID'];
                $milestoneIds = array_values(array_unique(array_map('intval', $validated['MilestoneIDs'] ?? [])));
                $requestedTaxId = !empty($validated['TaxID']) ? (int) $validated['TaxID'] : null;
                if (!empty($requestedTaxId) && !FinanceTaxRuleConfiguration::where('Id', $requestedTaxId)->exists()) {
                    $requestedTaxId = null;
                }

                $party = $this->resolveContractParty($contractType, $contractId);
                if (!$party) {
                    throw ValidationException::withMessages([
                        'ContractSourceID' => 'Could not resolve the contract supplier for this contract.',
                    ]);
                }

                $milestones = ContractMilestone::with('checklistItems')
                    ->where('ContractSourceType', $contractType)
                    ->where('ContractSourceID', $contractId)
                    ->whereIn('Id', $milestoneIds)
                    ->orderBy('MilestoneNo')
                    ->get();

                if ($milestones->count() !== count($milestoneIds)) {
                    throw ValidationException::withMessages([
                        'MilestoneIDs' => 'One or more selected milestones do not belong to this contract.',
                    ]);
                }

                $contractValue = $this->resolveContractValue($contractType, $contractId);
                $billedByMilestone = $this->getMilestoneBilledTotals($milestoneIds, $contractType, $contractId);
                $remainingByMilestone = [];
                foreach ($milestones as $milestone) {
                    $milestoneValue = $this->getMilestoneTargetAmount($milestone, $contractValue);
                    $alreadyBilled = (float) ($billedByMilestone[(int) $milestone->Id] ?? 0);
                    $remainingByMilestone[(int) $milestone->Id] = round(max(0, $milestoneValue - $alreadyBilled), 2);
                }

                $selectedBillableTotal = round(array_sum($remainingByMilestone), 2);
                if ($selectedBillableTotal <= 0) {
                    throw ValidationException::withMessages([
                        'MilestoneIDs' => 'Selected milestones are already fully billed.',
                    ]);
                }

                $invoiceAmount = round((float) $validated['Amount'], 2);
                if ($invoiceAmount - $selectedBillableTotal > 0.009) {
                    throw ValidationException::withMessages([
                        'Amount' => 'Invoice amount exceeds selected milestones remaining billable value (' . number_format($selectedBillableTotal, 2) . ').',
                    ]);
                }

                $eligibility = $this->evaluateContractEligibility($contractType, $contractId, $milestones, (float) $validated['Amount']);
                $contractTaxId = $this->resolveContractTaxId($contractType, $contractId);
                $taxIdToUse = $contractTaxId ?? $requestedTaxId;
                $tax = $this->computeTax((float) $validated['Amount'], $taxIdToUse);
                [$fallbackPoId, $fallbackGrnId] = $this->resolveFallbackReferences((int) ($party['supplier_id'] ?? 0));

                if ($fallbackPoId === null || $fallbackGrnId === null) {
                    throw ValidationException::withMessages([
                        'ContractSourceID' => 'Cannot create contract invoice because fallback PO/GRN references could not be generated.',
                    ]);
                }

                $invoicePayload = [
                    'SupplierID' => $party['supplier_ref'],
                    'POId' => $fallbackPoId,
                    'POReference' => $fallbackPoId,
                    'GRNId' => $fallbackGrnId,
                    'GRNReference' => $fallbackGrnId,
                    'InvoiceNumber' => $validated['InvoiceNumber'],
                    'InvoiceDate' => $validated['InvoiceDate'],
                    'DueDate' => $validated['DueDate'],
                    'InvoiceAmount' => (float) $validated['Amount'],
                    'TaxAmount' => $tax['TaxAmount'],
                    'TaxPercentage' => $tax['TaxPercentage'],
                    'Description' => $validated['Description'] ?? null,
                    'TaxID' => $taxIdToUse,
                    'TotalAmount' => $tax['TotalAmount'],
                    'CurrencyID' => $validated['CurrencyID'] ?? 56,
                    'ExchangeRate' => $validated['ExchangeRate'] ?? 1.0,
                    'Status' => 'Draft',
                    'InvoiceSourceType' => 'CONTRACT',
                    'ContractSourceType' => $contractType,
                    'ContractSourceID' => $contractId,
                    'MilestoneEligibilityStatus' => $eligibility['eligible'] ? 'Eligible' : 'Pending',
                    'IsOnHold' => !$eligibility['eligible'],
                    'HoldReason' => $eligibility['hold_reason'],
                    'HoldSetBy' => !$eligibility['eligible'] ? Auth::id() : null,
                    'HoldSetOn' => !$eligibility['eligible'] ? now() : null,
                    'PenaltySuggestedAmount' => $eligibility['penalty_suggested_amount'],
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ];

                if (Schema::hasColumn('t_FinanceInvoiceEntry', 'ThirdPartyID')) {
                    $invoicePayload['ThirdPartyID'] = $party['third_party_id'];
                }

                $invoice = FinanceInvoiceEntry::create($invoicePayload);

                $remainingToAllocate = $invoiceAmount;
                foreach ($milestones as $milestone) {
                    if ($remainingToAllocate <= 0) {
                        break;
                    }

                    $cap = (float) ($remainingByMilestone[(int) $milestone->Id] ?? 0);
                    if ($cap <= 0) {
                        continue;
                    }

                    $allocation = round(min($cap, $remainingToAllocate), 2);
                    if ($allocation <= 0) {
                        continue;
                    }

                    APInvoiceMilestone::create([
                        'FinanceInvoiceID' => $invoice->Id,
                        'MilestoneID' => $milestone->Id,
                        'BilledAmount' => $allocation,
                    ]);

                    $remainingToAllocate = round($remainingToAllocate - $allocation, 2);
                }

                if ($remainingToAllocate > 0.009) {
                    throw ValidationException::withMessages([
                        'Amount' => 'Invoice amount could not be fully allocated to selected milestones remaining value.',
                    ]);
                }

                if ($request->hasFile('attachment')) {
                    $invoice->newDocument(
                        ModulesEnum::Finance,
                        $request->file('attachment'),
                        [PermissionEnum::FinanceAccountsPayableCreate, PermissionEnum::FinanceAccountsPayableView],
                        Auth::user()
                    );
                }

                DB::commit();

                $flashKey = $eligibility['eligible'] ? 'success' : 'warning';
                $message = $eligibility['eligible']
                    ? 'Contract invoice created successfully.'
                    : 'Contract invoice created and placed on hold: ' . $eligibility['hold_reason'];

                return redirect()->route('invoiceentry.show', $invoice->Id)->with($flashKey, $message);
            }

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
                'ExchangeRate' => 'nullable|numeric|min:0.0001',
            ]);

            $tax = $this->computeTax((float) $validated['Amount'], $validated['TaxID'] ?? null);

            $grnReferenceId = DB::table('t_GoodsReceipts')
                ->where('GRNID', $validated['GRNReference'])
                ->value('id');

            if (!$grnReferenceId) {
                $grnReferenceId = DB::table('t_GoodsReceipts')->value('id');
            }

            if (!$grnReferenceId) {
                throw ValidationException::withMessages([
                    'GRNReference' => 'No valid GRN reference could be resolved for invoice creation.',
                ]);
            }

            $poAmount = (function () use ($validated) {
                if (!empty($validated['POReference'])) {
                    $order = DB::table('t_Orders')->where('Id', (int) $validated['POReference'])->first();
                    if ($order) {
                        $excl = (float) ($order->OrdTotExcl ?? 0);
                        $taxPct = (float) ($order->TaxPercentage ?? 0);
                        return $excl + ($excl * ($taxPct / 100));
                    }
                }
                return (float) $validated['Amount'];
            })();

            $invoice = FinanceInvoiceEntry::create([
                'SupplierID' => $validated['ThirdPartyID'],
                'POId' => (int) $validated['POReference'],
                'POReference' => (int) $validated['POReference'],
                'GRNId' => (int) $grnReferenceId,
                'GRNReference' => (int) $grnReferenceId,
                'InvoiceNumber' => $validated['InvoiceNumber'],
                'InvoiceDate' => $validated['InvoiceDate'],
                'DueDate' => $validated['DueDate'],
                'InvoiceAmount' => (float) $poAmount,
                'TaxAmount' => $tax['TaxAmount'],
                'TaxPercentage' => $tax['TaxPercentage'],
                'Description' => $validated['Description'] ?? null,
                'TaxID' => $validated['TaxID'] ?? null,
                'TotalAmount' => (float) $poAmount,
                'CurrencyID' => $validated['CurrencyID'] ?? 56,
                'ExchangeRate' => $validated['ExchangeRate'] ?? 1.0,
                'Status' => 'Draft',
                'InvoiceSourceType' => 'PO',
                'ContractSourceType' => null,
                'ContractSourceID' => null,
                'MilestoneEligibilityStatus' => null,
                'IsOnHold' => false,
                'HoldReason' => null,
                'HoldSetBy' => null,
                'HoldSetOn' => null,
                'PenaltySuggestedAmount' => 0,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            if ($request->hasFile('attachment')) {
                $invoice->newDocument(
                    ModulesEnum::Finance,
                    $request->file('attachment'),
                    [PermissionEnum::FinanceAccountsPayableCreate, PermissionEnum::FinanceAccountsPayableView],
                    Auth::user()
                );
            }

            DB::commit();

            Log::info('Invoice V2 created successfully', ['invoice_id' => $invoice->Id, 'source_type' => 'PO']);

            return redirect()->route('invoiceentry.show', $invoice->Id)
                ->with('success', 'Invoice created successfully');
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation error creating invoice', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error creating invoice', ['error' => $th->getMessage()]);
            return back()->with('error', 'Failed to create invoice: ' . $th->getMessage())->withInput();
        }
    }

    private function buildContractOptions()
    {
        $invoiceEligibleStatuses = ['Approved', 'Ap', 'Executed', 'Ex', 'Contract Approved'];

        $tenderContracts = TenderAward::with(['tender', 'winningSupplier.thirdParty', 'contractTaxRule.taxType'])
            ->whereIn('ContractStatus', $invoiceEligibleStatuses)
            ->get()
            ->map(function ($award) {
                $summary = $this->getContractBillingSummary('tender', (int) $award->Id);
                $resolvedTaxId = $this->resolveContractTaxId('tender', (int) $award->Id);
                $taxRule = $resolvedTaxId ? $award->contractTaxRule : null;
                return [
                    'type' => 'tender',
                    'id' => $award->Id,
                    'reference' => $award->ContractRef ?: ('TENDER-CONTRACT-' . $award->Id),
                    'title' => $award->tender?->Title ?? 'Tender Contract',
                    'supplier' => $this->resolveSupplierNameById((int) ($award->WinningSupplierID ?? 0)),
                    'value' => (float) ($award->ContractValue ?? 0),
                    'contract_total_value' => $summary['contract_total'],
                    'billed_value' => $summary['billed_total'],
                    'remaining_value' => $summary['remaining_total'],
                    'active_for_invoicing' => $summary['remaining_total'] > 0 || $summary['contract_total'] <= 0,
                    'status' => $award->ContractStatus,
                    'tax_id' => $resolvedTaxId,
                    'tax_name' => $taxRule?->taxType?->TaxTypeName,
                    'tax_rate' => $taxRule?->Rate,
                ];
            })
            ->filter(fn ($contract) => (bool) ($contract['active_for_invoicing'] ?? false));

        $rfqContracts = RFQAward::with(['rfq', 'supplier.supplierMaster.party', 'contractTaxRule.taxType'])
            ->whereIn('ContractStatus', $invoiceEligibleStatuses)
            ->get()
            ->map(function ($award) {
                $summary = $this->getContractBillingSummary('rfq', (int) $award->Id);
                $resolvedTaxId = $this->resolveContractTaxId('rfq', (int) $award->Id);
                $taxRule = $resolvedTaxId ? $award->contractTaxRule : null;
                return [
                    'type' => 'rfq',
                    'id' => $award->Id,
                    'reference' => $award->ContractRef ?: ('RFQ-CONTRACT-' . $award->Id),
                    'title' => $award->rfq?->Subject ?? 'RFQ Contract',
                    'supplier' => $this->resolveSupplierNameById((int) ($award->SupplierId ?? 0)),
                    'value' => (float) ($award->ContractValue ?? 0),
                    'contract_total_value' => $summary['contract_total'],
                    'billed_value' => $summary['billed_total'],
                    'remaining_value' => $summary['remaining_total'],
                    'active_for_invoicing' => $summary['remaining_total'] > 0 || $summary['contract_total'] <= 0,
                    'status' => $award->ContractStatus,
                    'tax_id' => $resolvedTaxId,
                    'tax_name' => $taxRule?->taxType?->TaxTypeName,
                    'tax_rate' => $taxRule?->Rate,
                ];
            })
            ->filter(fn ($contract) => (bool) ($contract['active_for_invoicing'] ?? false));

        return $tenderContracts
            ->merge($rfqContracts)
            ->sortBy('reference')
            ->values();
    }

    private function resolveContractParty(string $type, int $contractId): ?array
    {
        if ($type === 'tender') {
            $award = TenderAward::find($contractId);
            if (!$award) {
                return null;
            }
            $supplierId = (int) ($award->WinningSupplierID ?? 0);
        } else {
            $award = RFQAward::find($contractId);
            if (!$award) {
                return null;
            }
            $supplierId = (int) ($award->SupplierId ?? 0);
        }

        if ($supplierId <= 0) {
            return null;
        }

        $thirdPartyId = DB::table('t_Suppliers as s')
            ->join('t_SupplierMaster as sm', 'sm.Id', '=', 's.SupplierMasterId')
            ->where('s.Id', $supplierId)
            ->value('sm.ThirdPartyId');

        // Fallback: some contract records may already carry SupplierMaster.Id.
        if (!$thirdPartyId) {
            $thirdPartyId = DB::table('t_SupplierMaster')
                ->where('Id', $supplierId)
                ->value('ThirdPartyId');
        }

        // Keep backward compatibility with existing AP behavior where SupplierID stores ThirdPartyID.
        $supplierRef = (int) ($thirdPartyId ?: $supplierId);

        return [
            'supplier_id' => $supplierId,
            'third_party_id' => (int) ($thirdPartyId ?: $supplierRef),
            'supplier_ref' => $supplierRef,
        ];
    }

    private function resolveContractTaxId(string $type, int $contractId): ?int
    {
        if ($type === 'tender') {
            $taxId = TenderAward::where('Id', $contractId)->value('ContractTaxID');
        } else {
            $taxId = RFQAward::where('Id', $contractId)->value('ContractTaxID');
        }

        $taxId = $taxId ? (int) $taxId : null;
        if (!$taxId) {
            return null;
        }

        $exists = FinanceTaxRuleConfiguration::where('Id', $taxId)->exists();
        return $exists ? $taxId : null;
    }

    private function getMilestoneBilledTotals(array $milestoneIds, ?string $contractType = null, ?int $contractId = null): array
    {
        $milestoneIds = array_values(array_unique(array_filter(array_map('intval', $milestoneIds), fn ($id) => $id > 0)));
        if (empty($milestoneIds)) {
            return [];
        }

        $query = DB::table('t_APInvoiceMilestones as aim')
            ->join('t_FinanceInvoiceEntry as fi', 'fi.Id', '=', 'aim.FinanceInvoiceID')
            ->whereIn('aim.MilestoneID', $milestoneIds)
            ->whereNull('fi.DeletedOn');

        if (Schema::hasColumn('t_FinanceInvoiceEntry', 'InvoiceSourceType')) {
            $query->where('fi.InvoiceSourceType', 'CONTRACT');
        }
        if (!empty($contractType) && Schema::hasColumn('t_FinanceInvoiceEntry', 'ContractSourceType')) {
            $query->where('fi.ContractSourceType', $contractType);
        }
        if (!empty($contractId) && Schema::hasColumn('t_FinanceInvoiceEntry', 'ContractSourceID')) {
            $query->where('fi.ContractSourceID', (int) $contractId);
        }

        return $query
            ->groupBy('aim.MilestoneID')
            ->select('aim.MilestoneID', DB::raw('SUM(COALESCE(aim.BilledAmount, 0)) as BilledTotal'))
            ->pluck('BilledTotal', 'aim.MilestoneID')
            ->map(fn ($v) => (float) $v)
            ->toArray();
    }

    private function resolveContractValue(string $contractType, int $contractId): float
    {
        if ($contractType === 'tender') {
            $value = TenderAward::where('Id', $contractId)->value('ContractValue');
        } else {
            $value = RFQAward::where('Id', $contractId)->value('ContractValue');
        }

        return (float) ($value ?? 0);
    }

    private function getMilestoneTargetAmount(ContractMilestone $milestone, float $contractValue): float
    {
        if (strtoupper((string) $milestone->ValueType) === 'PERCENT') {
            $percent = (float) ($milestone->ValuePercent ?? 0);
            return round(max(0, $contractValue * ($percent / 100)), 2);
        }

        return round(max(0, (float) ($milestone->ValueAmount ?? 0)), 2);
    }

    private function getContractBillingSummary(string $contractType, int $contractId): array
    {
        $contractValue = $this->resolveContractValue($contractType, $contractId);
        $milestones = ContractMilestone::query()
            ->where('ContractSourceType', $contractType)
            ->where('ContractSourceID', $contractId)
            ->get(['Id', 'ValueType', 'ValuePercent', 'ValueAmount']);

        $contractTotal = round(
            $milestones->sum(fn ($m) => $this->getMilestoneTargetAmount($m, $contractValue)),
            2
        );

        $billedQuery = DB::table('t_APInvoiceMilestones as aim')
            ->join('t_FinanceInvoiceEntry as fi', 'fi.Id', '=', 'aim.FinanceInvoiceID')
            ->whereNull('fi.DeletedOn');

        if (Schema::hasColumn('t_FinanceInvoiceEntry', 'InvoiceSourceType')) {
            $billedQuery->where('fi.InvoiceSourceType', 'CONTRACT');
        }
        if (Schema::hasColumn('t_FinanceInvoiceEntry', 'ContractSourceType')) {
            $billedQuery->where('fi.ContractSourceType', $contractType);
        }
        if (Schema::hasColumn('t_FinanceInvoiceEntry', 'ContractSourceID')) {
            $billedQuery->where('fi.ContractSourceID', $contractId);
        }

        $billedTotal = (float) $billedQuery->sum(DB::raw('COALESCE(aim.BilledAmount, 0)'));
        $remainingTotal = round(max(0, $contractTotal - $billedTotal), 2);

        return [
            'contract_total' => round($contractTotal, 2),
            'billed_total' => round($billedTotal, 2),
            'remaining_total' => $remainingTotal,
        ];
    }

    private function evaluateContractEligibility(string $contractType, int $contractId, $milestones, float $invoiceAmount): array
    {
        $reasons = [];
        $eligible = true;
        $penaltySuggested = 0.0;
        $today = now()->startOfDay();

        $contractRule = ContractPenaltyRule::where('ContractSourceType', $contractType)
            ->where('ContractSourceID', $contractId)
            ->whereNull('MilestoneID')
            ->where('IsActive', true)
            ->latest('Id')
            ->first();

        foreach ($milestones as $milestone) {
            $requiredTotal = $milestone->checklistItems->where('Required', true)->count();
            $requiredDone = $milestone->checklistItems->where('Required', true)->where('IsFulfilled', true)->count();
            $isAccepted = in_array($milestone->Status, ['Accepted', 'Waived'], true);
            $isWaived = $milestone->Status === 'Waived';

            if (!$isAccepted || (!$isWaived && $requiredTotal !== $requiredDone)) {
                $eligible = false;
                $reasons[] = "M{$milestone->MilestoneNo} - {$milestone->Title} not accepted/complete.";
            }

            $rule = ContractPenaltyRule::where('ContractSourceType', $contractType)
                ->where('ContractSourceID', $contractId)
                ->where('MilestoneID', $milestone->Id)
                ->where('IsActive', true)
                ->latest('Id')
                ->first() ?: $contractRule;

            if ($rule && $milestone->PlannedDueDate) {
                $graceDays = (int) ($rule->GraceDays ?? 0);
                $dueDate = \Carbon\Carbon::parse($milestone->PlannedDueDate)->addDays($graceDays)->startOfDay();
                if ($today->greaterThan($dueDate) && !$isAccepted) {
                    $delayDays = $dueDate->diffInDays($today);
                    $penaltySuggested += $this->computeMilestonePenalty($rule, $delayDays, $invoiceAmount);
                }
            }
        }

        return [
            'eligible' => $eligible,
            'hold_reason' => $eligible ? null : implode(' ', $reasons),
            'penalty_suggested_amount' => round($penaltySuggested, 2),
        ];
    }

    private function computeMilestonePenalty(ContractPenaltyRule $rule, int $delayDays, float $invoiceAmount): float
    {
        $base = 0.0;
        if ($rule->PenaltyType === 'PER_DAY_DELAY') {
            $base = ((float) $rule->Rate) * $delayDays;
        } elseif ($rule->PenaltyType === 'PERCENT') {
            $base = $invoiceAmount * (((float) $rule->Rate) / 100);
        } else {
            $base = (float) ($rule->Rate ?? 0);
        }

        if (!empty($rule->CapAmount)) {
            $base = min($base, (float) $rule->CapAmount);
        }
        if (!empty($rule->CapPercent)) {
            $capPercentAmount = $invoiceAmount * (((float) $rule->CapPercent) / 100);
            $base = min($base, $capPercentAmount);
        }

        return max(0, $base);
    }

    private function computeTax(float $amount, $taxId): array
    {
        $taxAmount = 0.0;
        $taxPercentage = 0.0;

        if (!empty($taxId)) {
            $taxConfig = FinanceTaxRuleConfiguration::find($taxId);
            if ($taxConfig) {
                $taxPercentage = (float) $taxConfig->Rate;
                $taxAmount = round($amount * ($taxPercentage / 100), 2);
            }
        }

        return [
            'TaxAmount' => $taxAmount,
            'TaxPercentage' => $taxPercentage,
            'TotalAmount' => round($amount + $taxAmount, 2),
        ];
    }

    private function resolveFallbackReferences(?int $preferredSupplierId = null): array
    {
        $poId = DB::table('t_Orders')->orderBy('Id')->value('Id');
        if (!$poId) {
            $poId = $this->createContractFallbackOrder();
        }

        $grnId = DB::table('t_GoodsReceipts')->orderBy('id')->value('id');
        if (!$grnId) {
            $grnId = $this->createContractFallbackGrn($preferredSupplierId, $poId ? (int) $poId : null);
        }

        return [$poId ? (int) $poId : null, $grnId ? (int) $grnId : null];
    }

    private function createContractFallbackOrder(): ?int
    {
        $actorId = Auth::id() ?: DB::table('t_Users')->orderBy('Id')->value('Id');
        if (!$actorId) {
            Log::warning('Unable to create contract fallback order: no user available.');
            return null;
        }

        $now = now();
        $orderNo = 'CONTRACT-FB-' . $now->format('YmdHis');

        try {
            return (int) DB::table('t_Orders')->insertGetId([
                'OrderNo' => $orderNo,
                'Description' => 'System fallback order for contract invoices',
                'OrderDate' => $now,
                'CreatedBy' => (int) $actorId,
                'CreatedOn' => $now,
                'ModifiedBy' => (int) $actorId,
                'ModifiedOn' => $now,
            ], 'Id');
        } catch (\Throwable $e) {
            Log::error('Failed to create contract fallback order', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function createContractFallbackGrn(?int $preferredSupplierId, ?int $poId): ?int
    {
        $actorId = Auth::id() ?: DB::table('t_Users')->orderBy('Id')->value('Id');
        if (!$actorId) {
            Log::warning('Unable to create contract fallback GRN: no user available.');
            return null;
        }

        $supplierId = $this->resolveFallbackSupplierId($preferredSupplierId);
        if (!$supplierId) {
            Log::warning('Unable to create contract fallback GRN: no supplier available.', ['preferred_supplier_id' => $preferredSupplierId]);
            return null;
        }

        $now = now();
        $poCode = $poId ? (string) (DB::table('t_Orders')->where('Id', $poId)->value('OrderNo') ?: $poId) : 'CONTRACT-FB';
        $grnCode = 'GRN-FB-' . $now->format('YmdHis');

        try {
            return (int) DB::table('t_GoodsReceipts')->insertGetId([
                'GRNID' => $grnCode,
                'POID' => $poCode,
                'SupplierId' => (int) $supplierId,
                'ReceivedDate' => $now,
                'InspectionStatus' => 'p',
                'CreatedBy' => (int) $actorId,
                'CreatedOn' => $now,
                'ModifiedBy' => (int) $actorId,
                'ModifiedOn' => $now,
            ], 'id');
        } catch (\Throwable $e) {
            Log::error('Failed to create contract fallback GRN', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function resolveFallbackSupplierId(?int $preferredSupplierId): ?int
    {
        if (!empty($preferredSupplierId)) {
            $direct = DB::table('t_Suppliers')->where('Id', (int) $preferredSupplierId)->value('Id');
            if ($direct) {
                return (int) $direct;
            }

            $fromMaster = DB::table('t_Suppliers')
                ->where('SupplierMasterId', (int) $preferredSupplierId)
                ->orderBy('Id')
                ->value('Id');
            if ($fromMaster) {
                return (int) $fromMaster;
            }
        }

        $first = DB::table('t_Suppliers')->orderBy('Id')->value('Id');
        return $first ? (int) $first : null;
    }

    private function resolveSupplierNameById(int $supplierId): string
    {
        if ($supplierId <= 0) {
            return 'N/A';
        }

        $row = DB::table('t_Suppliers as s')
            ->leftJoin('t_SupplierMaster as sm', 'sm.Id', '=', 's.SupplierMasterId')
            ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
            ->where('s.Id', $supplierId)
            ->selectRaw("COALESCE(tp.TradingName, tp.ThirdPartyName, 'N/A') as SupplierName")
            ->first();

        return $row->SupplierName ?? 'N/A';
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
            ->leftJoin('t_SupplierMaster as sm', 'sm.Id', '=', 's.SupplierMasterId')
            ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
            ->select(
                's.Id',
                'sm.ThirdPartyId as ThirdPartyID',
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
