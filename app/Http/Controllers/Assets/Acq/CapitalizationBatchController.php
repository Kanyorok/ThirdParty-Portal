<?php

namespace App\Http\Controllers\Assets\Acq;

use App\Http\Controllers\Controller;
use App\Models\Assets\Acq\{CapitalizationBatch, CapitalizationLine};

class CapitalizationBatchController extends Controller
{
    public function index()
    {
        $rows = CapitalizationBatch::orderByDesc('CreatedOn')->paginate(20);

        return view('assets.acq.batches.index', compact('rows'));
    }

    public function show(int $id)
    {
        $batch = CapitalizationBatch::findOrFail($id);
        $lines = CapitalizationLine::where('BatchID', $id)->get();

        return view('assets.acq.batches.show', compact('batch', 'lines'));
    }

    public function destroy(int $id)
    {
        CapitalizationLine::where('BatchID', $id)->delete();
        CapitalizationBatch::where('Id', $id)->delete();

        return redirect()->route('assets.acq.cap-batches.index')->with('success', 'Batch deleted.');
    }
}
