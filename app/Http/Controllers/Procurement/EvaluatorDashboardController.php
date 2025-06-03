<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\TenderCommitteeMember;
use App\Models\procurement\TenderSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluatorDashboardController extends Controller
{
    //

     public function index()
    {
        $tenderSuppliers=TenderSupplier::with(['tender','supplier'])->get();
        $data=[];
        foreach($tenderSuppliers as $item){
            array_push($data,[
                'tender'=>$item->tender->Title,
                'tenderID'=>$item->tender->Id,
                'supplier'=>$item->supplier->SupplierName,
                'Role'=>TenderCommitteeMember::where('TenderID',$item->tender->Id)->where('UserID',Auth::id())->pluck('Role')->first(),
                'HasEvaluated'=>TenderCommitteeMember::where('TenderID',$item->tender->Id)->where('UserID',Auth::id())->pluck('HasEvaluated')->first(),
                'supplierID'=>$item->supplier->Id,
                //'Status'=>TenderCommitteeMember::where('TenderID',$item->tender->Id)->where('UserID',Auth::id())->pluck('HasEvaluated')->first(),
            ]);

        }
        return view('procurement.tendering.bidopeningandevaluation.evaluationdashboard.index',compact('data'));
    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.evaluationdashboard.create');
    }
}
