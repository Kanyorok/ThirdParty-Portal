<?php
namespace App\Http\Controllers\Assets\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Assets\Accounting\AssetImprovement;
use App\Models\Assets\Master\{AssetBookValue, AssetHistory};
use App\Models\Assets\Settings\AssetBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImprovementController extends Controller
{
    public function index(Request $request)
    {
        $book = $request->get('book');
        $rows = AssetImprovement::when($book, fn($q)=>$q->where('BookID',$book))
                ->orderByDesc('DocDate')->paginate(20);
        $books = AssetBook::orderBy('Name')->get();
        return view('assets.acc.improv.index', compact('rows','books','book'));
    }

    public function create()
    { $books = AssetBook::orderBy('Name')->get(); return view('assets.acc.improv.create', compact('books')); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'AssetID'     => 'required|integer|exists:t_Assets,Id',
            'BookID'      => 'required|integer|exists:t_AssetBooks,Id',
            'DocNo'       => 'nullable|max:50',
            'DocDate'     => 'required|date',
            'Description' => 'required|max:200',
            'Amount'      => 'required|numeric|min:0.01',
            'Treatment'   => 'required|in:CAPEX,EXP',
        ]);

        DB::transaction(function() use ($data) {
            $imp = AssetImprovement::create($data + ['Posted'=>0]);

            if ($data['Treatment'] === 'CAPEX') {
                $abv = AssetBookValue::where('AssetID',$data['AssetID'])->where('BookID',$data['BookID'])->lockForUpdate()->firstOrFail();
                $abv->AcquisitionCost = round((float)$abv->AcquisitionCost + (float)$data['Amount'],2);
                $abv->NBV             = round((float)$abv->NBV + (float)$data['Amount'],2);
                $abv->save();
                $imp->Posted = 1; $imp->save();

                AssetHistory::create([
                    'AssetID'=>$data['AssetID'],'EventType'=>'ImprovementCap','EventDate'=>$data['DocDate'],
                    'Reference'=>$data['DocNo'] ?? 'IMPROV','Remarks'=> $data['Description'],'CreatedOn'=>now()
                ]);
            } else {
                // EXP: just record, no NBV change
                AssetHistory::create([
                    'AssetID'=>$data['AssetID'],'EventType'=>'ImprovementExp','EventDate'=>$data['DocDate'],
                    'Reference'=>$data['DocNo'] ?? 'IMPROV-EXP','Remarks'=> $data['Description'],'CreatedOn'=>now()
                ]);
            }
        });

        return redirect()->route('assets.acc.improv.index')->with('success','Improvement captured.');
    }
}
