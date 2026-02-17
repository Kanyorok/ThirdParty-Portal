<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeWorkingDaySetting;
use App\Models\HR\WorkingDaySetting;
use Illuminate\Http\Request;

class EmployeeWorkingDayController extends Controller
{
    public function edit(Employee $employee)
    {
        $weekdays = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $defaults = WorkingDaySetting::orderBy('DayOfWeek')->get()->keyBy('DayOfWeek');
        $overrides = EmployeeWorkingDaySetting::where('EmployeeID', $employee->Id)
            ->orderBy('DayOfWeek')
            ->get()
            ->keyBy('DayOfWeek');

        $effectiveDays = collect();
        foreach (range(0, 6) as $day) {
            $effectiveDays[$day] = $overrides[$day] ?? $defaults[$day] ?? null;
        }

        $hasOverrides = $overrides->isNotEmpty();

        return view('hr.employees.working-days', compact(
            'employee',
            'weekdays',
            'defaults',
            'overrides',
            'effectiveDays',
            'hasOverrides'
        ));
    }

    public function update(Request $request, Employee $employee)
    {
        if ($employee->Status === 'Exited') {
            return redirect()
                ->route('hr.employees.show', $employee->Id)
                ->withErrors(['status' => 'Exited employees are read-only.']);
        }

        $weekdays = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $checked = $request->input('day', []);
        $startTimes = $request->input('start_time', []);
        $endTimes = $request->input('end_time', []);
        $fractions = $request->input('day_fraction', []);
        $userId = $request->user()->Id ?? $request->user()->id ?? null;

        foreach (range(0, 6) as $day) {
            $isWorking = in_array($day, $checked);
            $start = $startTimes[$day] ?? null;
            $end = $endTimes[$day] ?? null;
            $fractionInput = $fractions[$day] ?? 1;
            $fraction = (float)$fractionInput;

            if ($isWorking) {
                $request->validate([
                    "start_time.$day" => ['required', 'date_format:H:i'],
                    "end_time.$day" => ['required', 'date_format:H:i', "after:start_time.$day"],
                ], [], ['start_time.' . $day => "{$weekdays[$day]} start time", 'end_time.' . $day => "{$weekdays[$day]} end time"]);
                $request->validate([
                    "day_fraction.$day" => ['required', 'in:1,0.5'],
                ]);
                if ($fraction <= 0) {
                    $fraction = 0.5;
                }
            } else {
                $fraction = 0.0;
            }

            $record = EmployeeWorkingDaySetting::firstOrNew([
                'EmployeeID' => $employee->Id,
                'DayOfWeek' => $day,
            ]);
            if (! $record->exists) {
                $record->CreatedBy = $userId;
                $record->CreatedOn = now();
            }
            $record->IsWorking = $isWorking;
            $record->DayFraction = $fraction;
            $record->StartTime = $isWorking ? $start : null;
            $record->EndTime = $isWorking ? $end : null;
            $record->ModifiedBy = $userId;
            $record->ModifiedOn = now();
            $record->save();
        }

        return redirect()
            ->route('hr.employees.working-days.edit', $employee->Id)
            ->with('success', 'Employee working days updated.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->Status === 'Exited') {
            return redirect()
                ->route('hr.employees.show', $employee->Id)
                ->withErrors(['status' => 'Exited employees are read-only.']);
        }

        EmployeeWorkingDaySetting::where('EmployeeID', $employee->Id)->delete();

        return redirect()
            ->route('hr.employees.working-days.edit', $employee->Id)
            ->with('success', 'Employee working days reset to company defaults.');
    }
}
