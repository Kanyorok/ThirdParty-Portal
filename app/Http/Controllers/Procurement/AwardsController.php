<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AwardsController extends Controller
{
    //
    public function index()
    {
        return view('procurement.awards.index');
    }

    public function create(){
        return view('procurement.awards.create');
    }

    public function view_tender($id)
    {

        return view('procurement.awards.award_tender', compact('id'));

    }

    public function view_rfq($id)
    {
        return view('procurement.awards.award_rfq', compact('id'));
    }

}
