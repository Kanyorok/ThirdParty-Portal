<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Models\PropertyManagement\PropertyPaymentFrequency;
use Illuminate\Http\Request;

class PropertyPaymentFrequencyController extends Controller
{
    public function index()
    {
        $properties = PropertyPaymentFrequency::all();

        return view('property.tenantmanagement.leasemanagement.paymentfrequency.index', compact('properties'));
    }

    public function create()
    {
        return view('property.tenantmanagement.leasemanagement.paymentfrequency.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'FrequencyName' => 'required|string|max:50',
            'FrequencyCode' => 'required|string|max:50',
            'NumberOfMonths' => 'required|integer',
            'Description' => 'required|string|max:250',
        ]);
        $property = PropertyPaymentFrequency::create([
            'FrequencyName' => $request->FrequencyName,
            'FrequencyCode' => $request->FrequencyCode,
            'NumberOfMonths' => $request->NumberOfMonths,
            'Description' => $request->Description,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);

        return redirect()->route('paymentfrequency.index')->with('success', 'Payment frequency created successfully');
    }
}
