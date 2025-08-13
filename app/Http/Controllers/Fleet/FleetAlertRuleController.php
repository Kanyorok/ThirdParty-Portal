<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetAlertRule;

class FleetAlertRuleController extends Controller
{
    public function index()
    {
        $rules = FleetAlertRule::orderBy('CreatedOn', 'desc')->get();
        return view('fleet.alert_rules.index', compact('rules'));
    }

    public function create()
    {
        return view('fleet.alert_rules.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'AlertName'      => 'required|string|max:100',
            'TriggerType'    => 'required|in:Mileage,Date,Schedule',
            'TriggerValue'   => 'nullable|integer',
            'FrequencyDays'  => 'nullable|integer|min:1',
            'EscalationDays' => 'nullable|integer|min:0',
            'Description'    => 'nullable|string|max:255',
        ]);

        FleetAlertRule::create([
            ...$validated,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('fleet.alert_rules.index')->with('success', 'Alert rule created successfully.');
    }

    public function edit($id)
    {
        $rule = FleetAlertRule::findOrFail($id);
        return view('fleet.alert_rules.edit', compact('rule'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'AlertName'      => 'required|string|max:100',
            'TriggerType'    => 'required|in:Mileage,Date,Schedule',
            'TriggerValue'   => 'nullable|integer',
            'FrequencyDays'  => 'nullable|integer|min:1',
            'EscalationDays' => 'nullable|integer|min:0',
            'Description'    => 'nullable|string|max:255',
        ]);

        $rule = FleetAlertRule::findOrFail($id);
        $rule->update($validated);

        return redirect()->route('fleet.alert_rules.index')->with('success', 'Alert rule updated.');
    }
}
