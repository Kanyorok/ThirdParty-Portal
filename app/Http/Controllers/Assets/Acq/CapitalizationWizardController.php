<?php

namespace App\Http\Controllers\Assets\Acq;

use App\Http\Controllers\Controller;
use App\Models\Assets\Acq\{CWIPLine, CapitalizationBatch, CapitalizationLine};
use App\Models\Assets\Master\{Asset, AssetBookValue, AssetHistory};
use App\Models\Assets\Settings\{FixedAssetClass};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CapitalizationWizardController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->get('project');
        $lines = CWIPLine::when($projectId, fn ($q) => $q->where('ProjectID', $projectId))
                ->where('Status', 'Open')->where('IsCapitalizable', 1)
                ->orderByDesc('CreatedOn')->paginate(20);
        $books = \App\Models\Assets\Settings\AssetBook::orderBy('Name')->get();
        $classes = FixedAssetClass::orderBy('Name')->get();
        $locations = \App\Models\Assets\Settings\AssetLocation::orderBy('Site')->get();

        return view('assets.acq.wizard.index', compact('lines', 'books', 'classes', 'locations', 'projectId'));
    }

    public function createBatch(Request $request)
    {
        $data = $request->validate([
            'Mode' => 'required|in:SINGLE,PARTIAL,MULTI',
            'BatchDate' => 'required|date',
            'Remarks' => 'nullable|max:250',
            'Lines' => 'required|array|min:1',
            'Lines.*.CWIPLineID' => 'required|integer|exists:t_CWIPLines,Id',
            'Lines.*.AssetName' => 'required|max:200',
            'Lines.*.AssetCode' => 'nullable|max:50',
            'Lines.*.ClassID' => 'required|integer|exists:t_FixedAssetClasses,Id',
            'Lines.*.LocationID' => 'nullable|integer|exists:t_AssetLocations,Id',
            'Lines.*.BookID' => 'required|integer|exists:t_AssetBooks,Id',
            'Lines.*.DepStartDate' => 'nullable|date',
            'Lines.*.CapitalizeAmt' => 'required|numeric|min:0.01',
            'Lines.*.ResidualPct' => 'nullable|numeric|min:0|max:100',
            'Lines.*.Notes' => 'nullable|max:250',
        ]);

        $batch = null;
        DB::transaction(function () use (&$batch, $data) {
            $batch = CapitalizationBatch::create([
                'BatchNo' => $this->nextBatchNo(),
                'BatchDate' => $data['BatchDate'],
                'Mode' => $data['Mode'],
                'Status' => 'Draft',
                'Remarks' => $data['Remarks'] ?? null,
            ]);
            foreach ($data['Lines'] as $L) {
                CapitalizationLine::create([
                    'BatchID' => $batch->Id,
                    'SourceType' => 'CWIP',
                    'CWIPLineID' => $L['CWIPLineID'],
                    'AssetCode' => $L['AssetCode'] ?? null,
                    'AssetName' => $L['AssetName'],
                    'ClassID' => $L['ClassID'],
                    'LocationID' => $L['LocationID'] ?? null,
                    'BookID' => $L['BookID'],
                    'DepStartDate' => $L['DepStartDate'] ?? null,
                    'CapitalizeAmt' => $L['CapitalizeAmt'],
                    'ResidualPct' => $L['ResidualPct'] ?? null,
                    'Notes' => $L['Notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('assets.acq.cap-batches.show', $batch->Id)->with('success', 'Batch created.');
    }

    public function post(int $batchId)
    {
        $batch = CapitalizationBatch::findOrFail($batchId);
        if ($batch->Status !== 'Draft') {
            return back()->withErrors('Only Draft batches can be posted.');
        }
        DB::transaction(function () use ($batch) {
            $lines = CapitalizationLine::where('BatchID', $batch->Id)->get();
            foreach ($lines as $L) {
                // Generate code using rules (fallback to provided AssetCode if any)
                $ctx = [
                    'ClassID' => $L->ClassID,
                    'LocationID' => $L->LocationID,
                    'BookID' => $L->BookID,
                    'Date' => $L->DepStartDate ?? now()->toDateString(),
                ];
                $code = $L->AssetCode ?: $num->nextAssetCode($ctx);

                // Create Asset if not already linked
                $asset = Asset::create([
                    'AssetCode' => $L->AssetCode ?: $this->genAssetCode(),
                    'AssetName' => $L->AssetName,
                    'ClassID' => $L->ClassID,
                    'LocationID' => $L->LocationID,
                    'Status' => 'Active',
                    'CapitalizationDate' => $L->DepStartDate,
                    'IsActive' => 1,
                ]);
                $L->AssetID = $asset->Id;
                $L->save();

                // Create per-book value
                AssetBookValue::create([
                    'AssetID' => $asset->Id,
                    'BookID' => $L->BookID,
                    'AcquisitionCost' => $L->CapitalizeAmt,
                    'DepMethod' => null, 'UsefulLifeMonths' => null,'ResidualPct' => $L->ResidualPct,
                    'DepStartDate' => $L->DepStartDate,
                    'AccumDep' => 0, 'NBV' => $L->CapitalizeAmt,
                ]);

                // Mark CWIP line (partial or full)
                if ($L->CWIPLineID) {
                    $cw = \App\Models\Assets\Acq\CWIPLine::find($L->CWIPLineID);
                    // If full amount equals CWIP line total, close; else leave as Open (partial)
                    if (round((float)$cw->TotalCost, 2) <= round((float)$L->CapitalizeAmt, 2)) {
                        $cw->Status = 'Capitalized';
                        $cw->save();
                    }
                }

                // History
                AssetHistory::create([
                    'AssetID' => $asset->Id,'EventType' => 'Capitalized','EventDate' => now(),
                    'Reference' => $batch->BatchNo,'Remarks' => 'Capitalization posted','CreatedOn' => now(),
                ]);
            }
            $batch->Status = 'Posted';
            $batch->PostedOn = now();
            $batch->save();
        });

        return redirect()->route('assets.acq.cap-batches.show', $batch->Id)->with('success', 'Batch posted.');
    }

    private function nextBatchNo(): string
    {
        $last = CapitalizationBatch::orderByDesc('Id')->value('BatchNo');
        $n = 1;
        if ($last && preg_match('/CAP-(\d+)/', $last, $m)) {
            $n = intval($m[1]) + 1;
        }

        return 'CAP-' . str_pad((string)$n, 4, '0', STR_PAD_LEFT);
    }
}
