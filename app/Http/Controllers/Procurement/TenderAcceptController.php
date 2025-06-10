<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\TenderCommitteeMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenderAcceptController extends Controller
{
    //
        public function index()
    {
        $user_id = Auth::id();
        $tender = TenderCommitteeMember::where('UserID', $user_id)->where('Response', 0)->with(['tender', 'createdBy'])->first();
        return view('procurement.tendering.bidopeningandevaluation.memberresponse.index', compact('tender'));
    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.memberresponse.create');
    }

    public function store(Request $request)
    {

        try {
            DB::beginTransaction();
            TenderCommitteeMember::where('UserID', Auth::id())->update([
                'Response' => $request->response,
            ]);

            DB::commit();
            return back()->with('success', 'Response saved successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', 'An error occurred. Please try again later.');
        }
    }
}
