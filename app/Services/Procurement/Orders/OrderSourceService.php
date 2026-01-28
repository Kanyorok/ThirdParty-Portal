<?php

namespace App\Services\Procurement\Orders;

use App\Models\Procurement\ConsolidatedProcurementPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderSourceService
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    public function getAwardedRFQs()
    {
        $awardedFromRFQAward = collect([]);

        try {
            // Join t_RFQResponse to find all potential suppliers for approved RFQs
            // Not just "awarded" ones, but any approved RFQ with a response.
            // Logic: Approved RFQs are those with orders? No, Approved RFQs are ready for PO.
            // Current Logic: Fetch from t_RFQAward.
            // BUT previous change: We want ALL responses for approved RFQs.

            // 1. Get Approved RFQs (from t_RFQ where Status is Approved? or just t_RFQAward?)
            // Actually, the previous controller logic joined t_RFQAward.
            // AND we wanted to enable selecting un-awarded but responded RFQs.

            // Replicating Controller Logic:
            // "Fetch all approved RFQs (via Responses)"
            $awardedFromRFQAward = DB::table('t_RFQResponse as r')
               ->join('t_RFQ as q', 'r.RFQId', '=', 'q.Id')
               ->join('t_ThirdParties as tp', 'r.SupplierId', '=', 'tp.Id') // r.SupplierId is ThirdPartyId
               // Controller had: DB::table('t_RFQAward as a')...
               // WAIT. My previous edit to controller CHANGED it to query t_RFQResponse.

               // Let's perform the query exactly as the Controller had it (after my fix).
               ->select(
                   'q.Id',
                   'q.RFQNumber',
                   'r.SupplierId', // ThirdPartyId
                   'tp.Id as ThirdPartyId',
                   DB::raw("COALESCE(tp.TradingName, tp.ThirdPartyName, '') as SupplierName"),
                   DB::raw("COALESCE(tp.PhysicalAddress, '') as Address")
               )
               ->distinct()
               ->get();
        } catch (\Throwable $e) {
            Log::warning('Skipping RFQ fetch', ['error' => $e->getMessage()]);
        }

        return $awardedFromRFQAward
            ->filter(function ($r) {
                return ! $this->orderService->isSourceExhausted('RFQ', $r->Id);
            })
            ->values();
    }

    public function getAwardedTenders()
    {
        $awardedTenders = DB::table('t_TenderAwards as ta')
            ->join('t_Tenders as t', 'ta.TenderID', '=', 't.Id')
            ->leftJoin('t_Suppliers as s', 's.Id', '=', 'ta.WinningSupplierID')
            ->leftJoin('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
            ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
            ->where('ta.AwardStatus', 'Approved')
            ->where(function ($q) {
                $q->whereNull('ta.ContractStatus')
                    ->orWhere('ta.ContractStatus', '')
                    ->orWhere('ta.ContractStatus', 'No Contract Required');
            })
            ->select(
                't.Id',
                't.TenderNo',
                't.Title',
                'ta.WinningSupplierID as SupplierId',
                DB::raw('tp.Id as ThirdPartyId'),
                DB::raw("COALESCE(tp.TradingName, '') as SupplierName"),
                DB::raw("COALESCE(tp.PhysicalAddress, '') as Address")
            )
            ->get();

        return $awardedTenders
            ->filter(function ($t) {
                return ! $this->orderService->isSourceExhausted('TENDER', $t->Id);
            })
            ->values();
    }

    public function getTenderItems($tenderId)
    {
        // Check if tender exists
        $tender = DB::table('t_Tenders')->where('Id', $tenderId)->first();
        if (! $tender) {
            throw new \Exception('Tender not found');
        }

        $items = DB::table('t_TenderItems as ti')
            ->join('t_Items as i', 'ti.ItemID', '=', 'i.Id')
            ->leftJoin('t_Pricing as ip', 'i.Id', '=', 'ip.ItemID')
            ->where('ti.TenderID', $tenderId)
            ->select(
                'i.Id as itemCode',
                'i.ItemName as itemName',
                'ti.QtyToTender as quantity',
                DB::raw('COALESCE(ip.ActualPrice, 0) as unitPrice'),
                DB::raw('(ti.QtyToTender * COALESCE(ip.ActualPrice, 0)) as lineTotal'),
                'ti.Remarks as description'
            )
            ->distinct()
            ->get();

        $ordered = $this->orderService->getOrderedQuantities('TENDER', $tenderId);

        return $items->map(function ($item) use ($ordered) {
            $prev = $ordered[$item->itemCode] ?? 0;
            $item->quantity = max(0, $item->quantity - $prev);
            $item->lineTotal = $item->quantity * $item->unitPrice;

            return $item;
        })->filter(function ($item) {
            return $item->quantity > 0;
        })->values();
    }

    public function getContractItems($contractId, $type = 'tender')
    {
        if ($type === 'rfq') {
            $contract = DB::table('t_RFQAward')->where('Id', $contractId)->first();

            if (! $contract) {
                $contract = DB::table('t_RFQAward')->where('RFQId', $contractId)->first();
            }

            if (! $contract) {
                throw new \Exception('RFQ Contract not found');
            }

            $supplier = DB::table('t_Suppliers as s')
                ->leftJoin('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
                ->where('s.Id', $contract->SupplierId)
                ->select('sm.ThirdPartyId')
                ->first();

            $thirdPartyId = $supplier ? $supplier->ThirdPartyId : $contract->SupplierId;

            $response = DB::table('t_RFQResponse')
                ->where('RFQId', $contract->RFQId)
                ->where('SupplierId', $thirdPartyId)
                ->orderByDesc('CreatedOn')
                ->first();

            $responseId = $response ? $response->Id : null;

            $items = DB::table('t_RFQLines as rl')
                ->join('t_Items as i', 'rl.ItemId', '=', 'i.Id')
                ->leftJoin('t_ResponseItems as ri', function ($join) use ($responseId) {
                    $join->on('ri.ItemName', '=', 'rl.ItemName')
                         ->where('ri.RfqResponseId', '=', $responseId);
                })
                ->where('rl.RFQId', $contract->RFQId)
                ->select(
                    'i.Id as itemCode',
                    'i.ItemName as itemName',
                    'rl.Quantity as quantity',
                    DB::raw('COALESCE(ri.QuotedPrice, 0) as unitPrice'),
                    DB::raw('(rl.Quantity * COALESCE(ri.QuotedPrice, 0)) as lineTotal'),
                    'rl.ItemName as description',
                    DB::raw('0 as tax'),
                    DB::raw('0 as discount')
                )
                ->get();

            $orderedContract = $this->orderService->getOrderedQuantities('CONTRACT-RFQ', $contractId);
            $orderedRFQ = $this->orderService->getOrderedQuantities('RFQ', $contract->RFQId);

            return $items->map(function ($item) use ($orderedContract, $orderedRFQ) {
                $prevContract = $orderedContract[$item->itemCode] ?? 0;
                $prevRFQ = $orderedRFQ[$item->itemCode] ?? 0;
                $item->quantity = max(0, $item->quantity - $prevContract - $prevRFQ);
                $item->lineTotal = $item->quantity * $item->unitPrice;

                return $item;
            })->filter(function ($item) {
                return $item->quantity > 0;
            })->values();
        } else {
            // Tender Logic
            $contract = DB::table('t_TenderAwards')->where('Id', $contractId)->first();
            if (! $contract) {
                throw new \Exception('Contract not found');
            }

            if (! empty($contract->TenderID)) {
                $items = $this->getTenderItems($contract->TenderID);
                $orderedContract = $this->orderService->getOrderedQuantities('CONTRACT', $contractId);

                return $items->map(function ($item) use ($orderedContract) {
                    $prev = $orderedContract[$item->itemCode] ?? 0;
                    $item->quantity = max(0, $item->quantity - $prev);
                    $item->lineTotal = $item->quantity * $item->unitPrice;

                    return $item;
                })->filter(function ($item) {
                    return $item->quantity > 0;
                })->values();
            }

            return collect([]);
        }
    }

    public function getContracts()
    {
        // Get active tender contracts
        $tenderContracts = DB::table('t_TenderAwards as ta')
            ->leftJoin('t_Suppliers as s', 's.Id', '=', 'ta.WinningSupplierID')
            ->leftJoin('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
            ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
            ->where('ta.ContractStatus', 'Executed')
            ->whereNotNull('ta.ContractRef')
            ->where('ta.ContractRef', '!=', '')
            ->orderByDesc('ta.ContractApprovedOn')
            ->select([
                'ta.Id as Id',
                'ta.ContractRef as ContractRef',
                'ta.WinningSupplierID as SupplierId',
                DB::raw('tp.Id as ThirdPartyId'),
                DB::raw("tp.TradingName as SupplierName"),
                DB::raw("COALESCE(tp.PhysicalAddress, '') as Address"),
                'ta.ContractStatus',
                DB::raw("'tender' as AwardType"),
            ])
            ->get();

        // Get active RFQ contracts
        $rfqContracts = collect();

        try {
            $rfqContracts = DB::table('t_RFQAward as ra')
                ->join('t_RFQ as r', 'ra.RFQId', '=', 'r.Id')
                ->leftJoin('t_Suppliers as s', 's.Id', '=', 'ra.SupplierId')
                ->leftJoin('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
                ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
                ->where('ra.ContractStatus', 'Executed')
                ->whereNotNull('ra.ContractRef')
                ->where('ra.ContractRef', '!=', '')
                ->orderByDesc('ra.ContractApprovedOn')
                ->select([
                    'ra.Id as Id',
                    'ra.ContractRef as ContractRef',
                    'ra.SupplierId as SupplierId',
                    DB::raw('tp.Id as ThirdPartyId'),
                    DB::raw("tp.TradingName as SupplierName"),
                    DB::raw("COALESCE(tp.PhysicalAddress, '') as Address"),
                    'ra.ContractStatus',
                    DB::raw("'rfq' as AwardType"),
                ])
                ->get();
        } catch (\Exception $e) {
            Log::warning('Failed to fetch RFQ contracts: ' . $e->getMessage());
        }

        return $tenderContracts->merge($rfqContracts)
            ->filter(function ($c) {
                if (($c->AwardType ?? '') === 'rfq') {
                    // RFQ Contract (t_RFQAward id)
                    return ! $this->orderService->isSourceExhausted('CONTRACT-RFQ', $c->Id);
                } else {
                    // Tender Contract (t_TenderAwards id)
                    return ! $this->orderService->isSourceExhausted('CONTRACT', $c->Id);
                }
            })
            ->values();
    }

    public function getDirectPlans()
    {
        $directMethod = DB::table('t_CodeDetails')
            ->where('CodeID', 'ProcurementMethod')
            ->where(function ($q) {
                $q->where('Description', 'LIKE', '%Direct%')
                  ->orWhere('Value', 'Like', '%Direct%');
            })
            ->first();

        $methodId = $directMethod ? $directMethod->ID : null;

        if (! $methodId) {
            return collect([]);
        }

        $planIds = DB::table('t_PlanLineItem')
            ->where('ProcurementMethod', $methodId)
            ->pluck('PlanID')
            ->unique()
            ->toArray();

        if (empty($planIds)) {
            return collect([]);
        }

        $plans = ConsolidatedProcurementPlan::query()
            ->whereIn('PlanID', $planIds)
            ->where('Status', 'Ap')
            ->get()
            ->filter(function ($plan) {
                return ! $this->orderService->isSourceExhausted('PLAN', $plan->PlanID);
            })
            ->map(function ($plan) use ($methodId) {
                $pending = DB::table('t_PlanLineItem')
                       ->where('PlanID', $plan->PlanID)
                       ->where('ProcurementMethod', $methodId)
                       ->count();

                return [
                    'PlanID' => $plan->PlanID,
                    'Title' => $plan->Title ?? $plan->Description ?? ('Plan #' . $plan->PlanID),
                    'FiscalYear' => $plan->FiscalYear,
                    'PendingItems' => $pending,
                ];
            })
            ->values();

        return $plans;
    }

    public function getDirectPlanItems($planId)
    {
        // specific lookup to avoid matching 'Tender' or other methods containing 'D'
        $directMethod = DB::table('t_CodeDetails')
            ->where('CodeID', 'ProcurementMethod')
            ->where(function ($q) {
                $q->where('Description', 'LIKE', 'Direct Purchase%')
                  ->orWhere('Description', 'LIKE', 'Direct Procurement%');
            })
            ->value('ID');

        // Fallback if not found (try strictly 'Direct')
        if (! $directMethod) {
            $directMethod = DB::table('t_CodeDetails')
               ->where('CodeID', 'ProcurementMethod')
               ->where('Description', 'Direct')
               ->value('ID');
        }

        $items = DB::table('t_PlanLineItem as pli')
            ->join('t_Items as i', 'pli.ItemID', '=', 'i.Id')
            ->where('pli.PlanID', $planId)
            ->where('pli.ProcurementMethod', $directMethod)
            ->whereNull('pli.DeletedOn')
            ->select(
                'i.Id as itemCode',
                'i.ItemName as itemName',
                'pli.MergedQty as quantity',
                DB::raw('COALESCE(i.ItemPrice, 0) as unitPrice'),
                'i.ItemDescription as description',
                'i.UOM as uom',
                'i.ItemType as itemType'
            )
            ->distinct()
            ->get();

        $ordered = $this->orderService->getOrderedQuantities('PLAN', $planId);

        return $items->map(function ($item) use ($ordered) {
            $code = $item->itemCode;
            $prev = $ordered[$code] ?? 0;
            $remaining = max(0, $item->quantity - $prev);

            return [
                'itemCode' => $code,
                'itemName' => $item->itemName,
                'description' => $item->description ?? $item->itemName,
                'quantity' => $remaining,
                'unitPrice' => (float)$item->unitPrice,
                'uom' => $item->uom,
                'itemType' => $item->itemType,
            ];
        })->filter(function ($item) {
            return $item['quantity'] > 0;
        })->values();
    }

    public function getRFQItems($rfqId, $supplierId = null)
    {
        // Base Items from RFQ Lines joined with Master Items
        $items = DB::table('t_RFQLines as rl')
            ->join('t_Items as i', 'rl.ItemId', '=', 'i.Id')
            ->where('rl.RFQId', $rfqId)
            ->select(
                'i.Id as itemCode',
                'i.ItemName as itemName', // Use Master Name
                'rl.Quantity as quantity',
                'i.ItemDescription as description', // Fixed column name
                'i.UOM as uom',
                'i.ItemType as itemType'
            )
            ->get();

        // If Supplier is provided, try to fetch quoted prices
        $quotedPrices = [];
        if ($supplierId) {
            $response = DB::table('t_RFQResponse')
               ->where('RFQId', $rfqId)
               ->where('SupplierId', $supplierId)
               ->first();

            if ($response) {
                $quotedPrices = DB::table('t_ResponseItems')
                   ->where('RfqResponseId', $response->Id)
                   ->pluck('QuotedPrice', 'ItemName')
                   ->toArray();
            }
        }

        // Calculate remaining quantities
        $ordered = $this->orderService->getOrderedQuantities('RFQ', $rfqId);

        return $items->map(function ($item) use ($ordered, $quotedPrices) {
            $code = $item->itemCode;
            $prev = $ordered[$code] ?? 0;
            $remaining = max(0, $item->quantity - $prev);

            // Determine Price
            // Check quoted prices by name
            $price = 0;
            if (isset($quotedPrices[$item->itemName])) {
                $price = $quotedPrices[$item->itemName];
            }

            return [
                'itemCode' => $code,
                'itemName' => $item->itemName,
                'description' => $item->description ?? $item->itemName,
                'quantity' => $remaining,
                'unitPrice' => (float)$price,
                'uom' => $item->uom,
                'itemType' => $item->itemType,
            ];
        })->filter(function ($item) {
            return $item['quantity'] > 0;
        })->values();
    }
}
