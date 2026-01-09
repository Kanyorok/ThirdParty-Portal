<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQClarification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RFQClarificationController extends Controller
{
    // Full-screen page
   public function page()
{
    // Preload minimal filters: approved RFQs and suppliers (optional)
    $rfqs = DB::table('t_RFQ')->where('Status', 'Approved')->orderByDesc('Id')->get(['Id','RFQNumber']);
    
    $suppliers = DB::table('t_ThirdParties as tp')
        ->join('t_SupplierMaster as sm', 'sm.ThirdPartyId', '=', 'tp.Id')
        ->join('t_Suppliers as s', 's.SupplierMasterId', '=', 'sm.Id')
        ->whereNull('tp.DeletedOn')
        ->whereNull('sm.DeletedOn')
        ->whereNull('s.DeletedOn')
        ->where('s.Active_Status', 1)
        ->groupBy('tp.Id','tp.TradingName')
        ->orderBy('tp.TradingName')
        ->get(['tp.Id','tp.TradingName']);
        
    return view('procurement.rfqclarifications.index', compact('rfqs','suppliers'));
}

    // List clarifications across RFQs (filters)
    public function listAll(Request $request): \Illuminate\Http\JsonResponse
    {
        $rfqId = (int) $request->query('rfqId', 0);
        $supplierId = (int) $request->query('supplierId', 0); // ThirdPartyId
        $answered = $request->query('answered'); // yes|no|null
        $q = trim((string) $request->query('q', ''));

        $rows = RFQClarification::query()
            ->join('t_RFQ as r', 'r.Id', '=', 't_RFQClarifications.RFQId')
            ->where('r.Status', 'Approved')
            ->when($rfqId > 0, fn($x) => $x->where('t_RFQClarifications.RFQId', $rfqId))
            ->when($answered === 'yes', fn($x) => $x->whereNotNull('t_RFQClarifications.Answer'))
            ->when($answered === 'no', fn($x) => $x->whereNull('t_RFQClarifications.Answer'))
            ->when($q !== '', function ($x) use ($q) {
                $x->where(function ($w) use ($q) {
                    $w->where('t_RFQClarifications.Question', 'like', "%$q%")
                      ->orWhere('t_RFQClarifications.Answer', 'like', "%$q%");
                });
            })
            ->whereNull('t_RFQClarifications.DeletedOn')
            ->orderByDesc('t_RFQClarifications.Id')
            ->get(['t_RFQClarifications.*']);

        // Decorate
        $rfqNumbers = DB::table('t_RFQ')->whereIn('Id', $rows->pluck('RFQId')->unique())->pluck('RFQNumber','Id');
        $supplierNames = DB::table('t_SupplierMaster as s')
            ->join('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyId')
            ->whereIn('s.Id', $rows->pluck('SupplierId')->unique())
            ->pluck('tp.TradingName', 's.Id');
        $lineInfo = DB::table('t_RFQLines')->whereIn('Id', $rows->pluck('RFQLineId')->filter())->get(['Id','ItemName'])->keyBy('Id');

        $data = $rows->map(function ($r) use ($rfqNumbers, $supplierNames, $lineInfo) {
            return [
                'Id' => $r->Id,
                'RFQId' => $r->RFQId,
                'RFQNumber' => $rfqNumbers[$r->RFQId] ?? null,
                'SupplierId' => $r->SupplierId,
                'SupplierName' => $supplierNames[$r->SupplierId] ?? null,
                'RFQLineId' => $r->RFQLineId,
                'ItemName' => optional($lineInfo->get($r->RFQLineId))->ItemName,
                'Question' => $r->Question,
                'Answer' => $r->Answer,
                'CreatedOn' => $r->CreatedOn,
            ];
        });

        return response()->json(['data' => $data]);
    }
    // List clarifications for an RFQ (procurement-side)
    public function index(Request $request, int $rfqId): JsonResponse
    {
        $this->authorize('viewAny', RFQClarification::class);

        $supplierId = (int) $request->query('supplierId', 0);

        $rows = RFQClarification::query()
            ->where('RFQId', $rfqId)
            ->when($supplierId > 0, fn($q) => $q->where('SupplierId', $supplierId))
            ->whereNull('DeletedOn')
            ->orderByDesc('Id')
            ->get(['Id','RFQId','SupplierId','RFQLineId','Question','Answer','CreatedOn']);

        // Decorate with supplier and line info
        $supplierNames = DB::table('t_SupplierMaster as s')
            ->join('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
            ->whereIn('s.Id', $rows->pluck('SupplierId')->filter())
            ->pluck('tp.TradingName', 's.Id');

        $lineInfo = DB::table('t_RFQLines')
            ->whereIn('Id', $rows->pluck('RFQLineId')->filter())
            ->get(['Id','ItemName','UOM','Quantity'])
            ->keyBy('Id');

        $data = $rows->map(function ($r) use ($supplierNames, $lineInfo) {
            $line = $r->RFQLineId ? ($lineInfo[$r->RFQLineId] ?? null) : null;
            return [
                'Id' => $r->Id,
                'RFQId' => $r->RFQId,
                'SupplierId' => $r->SupplierId,
                'SupplierName' => $supplierNames[$r->SupplierId] ?? null,
                'RFQLineId' => $r->RFQLineId,
                'ItemName' => $line->ItemName ?? null,
                'UOM' => $line->UOM ?? null,
                'Quantity' => $line->Quantity ?? null,
                'Question' => $r->Question,
                'Answer' => $r->Answer,
                'CreatedOn' => $r->CreatedOn,
            ];
        });

        return response()->json(['data' => $data]);
    }

    // Respond to a clarification (procurement-side)
    public function respond(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:t_RFQClarifications,Id',
            'answer' => 'required|string',
        ]);

        $clar = RFQClarification::findOrFail($validated['id']);
        $clar->Answer = $validated['answer'];
        $clar->ModifiedBy = Auth::id();
        $clar->save();

        return response()->json(['ok' => true]);
    }
}
