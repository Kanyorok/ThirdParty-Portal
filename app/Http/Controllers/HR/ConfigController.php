<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Finance\SystemBankSetting;
use App\Models\HR\WorkingDaySetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConfigController extends Controller
{
    public function orgProfile()
    {
        $profile = SystemBankSetting::query()->orderByDesc('Id')->first();
        return view('hr.config.org', compact('profile'));
    }

    public function orgProfileUpdate(Request $request)
    {
        $data = $request->validate([
            'BankName'      => ['required', 'string', 'max:150'],
            'ShortName'     => ['nullable', 'string', 'max:50'],
            'BankCode'      => ['nullable', 'string', 'max:50'],
            'SwiftCode'     => ['nullable', 'string', 'max:50'],
            'ClearingCode'  => ['nullable', 'string', 'max:50'],
            'Address1'      => ['nullable', 'string', 'max:255'],
            'Address2'      => ['nullable', 'string', 'max:255'],
            'ZipCode'       => ['nullable', 'string', 'max:50'],
            'Phone1'        => ['nullable', 'string', 'max:50'],
            'EmailID'       => ['nullable', 'email', 'max:150'],
            'Website'       => ['nullable', 'string', 'max:150'],
            'BankRegNumber' => ['nullable', 'string', 'max:100'],
        ]);

        $profile = SystemBankSetting::query()->orderByDesc('Id')->first();
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        if ($profile) {
            $profile->update($data);
        } else {
            $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
            $data['CreatedOn'] = now();
            $data['IsActive'] = 1;
            SystemBankSetting::create($data);
        }

        return redirect()->route('hr.config.org.index')->with('success', 'Organization profile saved.');
    }

    public function workingDays(\Illuminate\Http\Request $request)
    {
        $days = WorkingDaySetting::orderBy('DayOfWeek')->get()->keyBy('DayOfWeek');
        $weekdays = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

        $forceEdit = $request->boolean('edit');

        if ($days->isNotEmpty() && !$forceEdit) {
            return view('hr.config.working-days-view', compact('days', 'weekdays'));
        }

        return view('hr.config.working-days', compact('days', 'weekdays'));
    }

    public function workingDaysView()
    {
        $days = WorkingDaySetting::orderBy('DayOfWeek')->get()->keyBy('DayOfWeek');
        $weekdays = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

        return view('hr.config.working-days-view', compact('days', 'weekdays'));
    }

    public function workingDaysUpdate(Request $request)
    {
        $weekdays = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $checked = $request->input('day', []);
        $startTimes = $request->input('start_time', []);
        $endTimes = $request->input('end_time', []);
        $fractions = $request->input('day_fraction', []);

        foreach (range(0, 6) as $day) {
            $isWorking = in_array($day, $checked);
            $start = $startTimes[$day] ?? null;
            $end = $endTimes[$day] ?? null;
            $fractionInput = $fractions[$day] ?? 1;
            $fraction = (float)$fractionInput;

            if ($isWorking) {
                $request->validate([
                    "start_time.$day" => ['required', 'date_format:H:i'],
                    "end_time.$day"   => ['required', 'date_format:H:i', "after:start_time.$day"],
                ], [], ['start_time.' . $day => "{$weekdays[$day]} start time", 'end_time.' . $day => "{$weekdays[$day]} end time"]);
                $request->validate([
                    "day_fraction.$day" => ['required', 'in:1,0.5'],
                ]);
                // Guard against legacy value "0" from the old UI where half-day posted as 0
                if ($fraction <= 0) {
                    $fraction = 0.5;
                }
            } else {
                $fraction = 0.0;
            }

            $record = WorkingDaySetting::firstOrNew(['DayOfWeek' => $day]);
            if (!$record->exists) {
                $record->CreatedBy = $request->user()->Id ?? $request->user()->id ?? null;
                $record->CreatedOn = now();
            }
            $record->IsWorking = $isWorking;
            $record->DayFraction = $fraction;
            $record->StartTime = $isWorking ? $start : null;
            $record->EndTime = $isWorking ? $end : null;
            $record->ModifiedBy = $request->user()->Id ?? $request->user()->id ?? null;
            $record->ModifiedOn = now();
            $record->save();
        }

        return redirect()->route('hr.config.workingdays.view')->with('success', 'Working days updated.');
    }

}
