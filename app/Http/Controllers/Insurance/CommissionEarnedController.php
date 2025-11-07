<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Insurance\BancassuranceClaim;
use Illuminate\Http\Request;

class CommissionEarnedController extends Controller
{
public function index(Request $request)
{
    $claims = BancassuranceClaim::all();

    return view('bancassurance.commissions.earned.index', compact('claims'));
}

}