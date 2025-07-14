<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContractsLifecycleController extends Controller
{
    public function index()
    {
        // Load signed contracts
        $contracts = []; // Replace with actual query
        return view('procurement.contracts.contractlifecycle.index', compact('contracts'));
    }

    public function view($id)
    {
        return view('procurement.contracts.contractlifecycle.view', compact('id'));
    }

    public function execution($id)
    {
        return view('procurement.contracts.contractlifecycle.execution', compact('id'));
    }

    public function amend($id)
    {
        return view('procurement.contracts.contractlifecycle.amend', compact('id'));
    }
}
