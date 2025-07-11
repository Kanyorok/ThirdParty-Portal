<?php

namespace App\Http\Controllers\Insurance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PolicyTypeController extends Controller
{
    public function index()
    {
        $types = DB::table('t_PolicyTypes')->orderBy('PolicyTypeName')->get();
        return view('insurance.policymanagement.policy-types.index', compact('types'));
    }

    public function store(Request $request)
    {
        DB::table('t_PolicyTypes')->insert([
            'PolicyTypeName' => $request->PolicyTypeName,
            'Description' => $request->Description,
            'Status' => $request->Status,
            'CreatedAt' => now(),
            'CreatedBy' => Auth::id()
        ]);

        return redirect()->route('insurance.policymanagement.policy-types.index')->with('success', 'Policy type added.');
    }
}
