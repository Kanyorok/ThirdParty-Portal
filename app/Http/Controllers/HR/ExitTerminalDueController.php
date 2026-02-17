<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Exit\ExitRequest;
use App\Models\HR\Exit\ExitTerminalDue;
use App\Services\HR\ExitService;
use Illuminate\Http\Request;

class ExitTerminalDueController extends Controller
{
    public function index()
    {
        $exits = ExitRequest::with('employee')->orderByDesc('CreatedOn')->paginate(30);
        return view('hr.exit.terminal_dues.index', compact('exits'));
    }

    public function edit($exitId)
    {
        $exit = ExitRequest::with(['employee', 'terminalDues'])->findOrFail($exitId);
        return view('hr.exit.terminal_dues.edit', compact('exit'));
    }

    public function store(Request $request, $exitId)
    {
        $exit = ExitRequest::findOrFail($exitId);
        $data = $request->validate([
            'ComponentCode' => ['nullable', 'string', 'max:50'],
            'ComponentName' => ['required', 'string', 'max:150'],
            'Amount' => ['required', 'numeric'],
            'IsEarning' => ['sometimes', 'boolean'],
            'IsTaxable' => ['sometimes', 'boolean'],
            'Notes' => ['nullable', 'string'],
        ]);

        ExitTerminalDue::create([
            'ExitID' => $exit->Id,
            'ComponentCode' => $data['ComponentCode'] ?? null,
            'ComponentName' => $data['ComponentName'],
            'Amount' => $data['Amount'],
            'IsEarning' => $request->boolean('IsEarning', true),
            'IsTaxable' => $request->boolean('IsTaxable', false),
            'Notes' => $data['Notes'] ?? null,
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.exit.terminal-dues.edit', $exit->Id)->with('success', 'Terminal due line added.');
    }

    public function generate($exitId, ExitService $exitService)
    {
        $exit = ExitRequest::with('employee')->findOrFail($exitId);
        $components = $exitService->buildTerminalDues($exit);

        foreach ($components as $component) {
            ExitTerminalDue::updateOrCreate(
                [
                    'ExitID' => $exit->Id,
                    'ComponentCode' => $component['code'],
                ],
                [
                    'ComponentName' => $component['name'],
                    'Amount' => $component['amount'],
                    'IsEarning' => $component['isEarning'],
                    'IsTaxable' => $component['isTaxable'],
                    'ModifiedBy' => auth()->id(),
                    'ModifiedOn' => now(),
                    'CreatedBy' => auth()->id(),
                    'CreatedOn' => now(),
                ]
            );
        }

        return redirect()->route('hr.exit.terminal-dues.edit', $exit->Id)->with('success', 'Terminal dues generated.');
    }

    public function destroy($exitId, $lineId)
    {
        $exit = ExitRequest::findOrFail($exitId);
        $line = ExitTerminalDue::where('ExitID', $exit->Id)->where('Id', $lineId)->firstOrFail();
        $line->delete();

        return redirect()->route('hr.exit.terminal-dues.edit', $exit->Id)->with('success', 'Terminal due line removed.');
    }
}
