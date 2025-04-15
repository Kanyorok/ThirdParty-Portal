<?php

namespace App\Http\Controllers\CRM\Base;

use App\Http\Controllers\Controller;
use App\Models\Notes;
use App\Services\PartyService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class NotesController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->only('show');
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): View | JsonResponse
    {
        if ($request->ajax()) {
            return Datatables::of(Notes::query()->lock('WITH(NOLOCK)')->with(['party'])->select('*'))->addIndexColumn()
                ->addColumn('action', function (Notes $notes) {
                    return '<button type="button" data-click_url="' . route('notes.show', [$notes->NoteID]) . '" data-summary_title="notes summary" class="btn btn-info btn-sm click-summary-data"><i class="fas fa-eye"></i> details</button>';
                })->editColumn('party', function (Notes $notes) {
                    return (new PartyService($notes->party))->getDTRow();
                })->editColumn('CreatedOn', function (Notes $notes) {
                    return $notes->CreatedOn?->format('F d, Y h:i A');
                })->editColumn('Notes', function (Notes $notes) {
                    return Str::limit($notes->Notes);
                })->rawColumns(['action', 'party'])->make();
        }

        return view('crm.base.notes.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Notes $note): View
    {
        return view('crm.base.notes.show')->with('note', $note)
            ->with('party', $note->party);
    }
}
