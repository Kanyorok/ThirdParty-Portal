<?php

namespace App\Http\Controllers\Bancassurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerCommunicationController extends Controller
{
    //
public function create($customerId)
{
    $customer = DB::table('t_BancassuranceCustomers')->where('Id', $customerId)->first();
    $employees = DB::table('t_Employees')->get();

    if (!$customer) {
        return redirect()->back()->with('error', 'Customer not found.');
    }

    return view('bancassurance.customers.communication.create', compact('customer', 'employees'));
}

public function store(Request $request, $customerId)
{
    $request->validate([
        'ContactDate' => 'required|date',
        'ContactType' => 'required|string',
        'Summary' => 'required|string|max:255',
        'Notes' => 'nullable|string',
        'HandledBy' => 'nullable|integer'
    ]);

    DB::table('t_BancassuranceCustomerContacts')->insert([
        'CustomerID' => $customerId,
        'ContactDate' => $request->ContactDate,
        'ContactType' => $request->ContactType,
        'Summary' => $request->Summary,
        'Notes' => $request->Notes,
        'HandledBy' => $request->HandledBy,
        'CreatedBy' => auth()->id(),
        'CreatedAt' => now()
    ]);

    return redirect()->route('bancassurance.customers.portfolio', $customerId)
        ->with('success', 'Communication logged successfully.');
}
public function index($customerId)
{
    $customer = DB::table('t_BancassuranceCustomers')->where('Id', $customerId)->first();

    $logs = DB::table('t_BancassuranceCustomerContacts as c')
        ->leftJoin('t_Employees as e', 'c.HandledBy', '=', 'e.Id')
        ->where('c.CustomerID', $customerId)
        ->orderByDesc('c.ContactDate')
        ->select(
            'c.*',
            DB::raw("CONCAT(e.FirstName, ' ', e.LastName) as HandledByName")
        )
        ->get();

    return view('bancassurance.customers.communication.index', compact('customer', 'logs'));
}
}
