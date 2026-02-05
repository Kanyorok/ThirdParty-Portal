@extends('layouts.app')

@section('title', 'Working Days & Hours')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">Working Days & Hours</h2>
            <div class="text-muted">{{ $employee->FirstName }} {{ $employee->LastName }} ({{ $employee->EmployeeNo }})</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('hr.employees.show', $employee->EmployeeNo) }}">Back</a>
            @if($hasOverrides)
                <form method="POST" action="{{ route('hr.employees.working-days.destroy', $employee->EmployeeNo) }}"
                      onsubmit="return confirm('Reset this employee to company working days?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">Reset to Company Defaults</button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="alert alert-info">
        @if($hasOverrides)
            This employee has a custom schedule. Reset to use company defaults.
        @else
            This employee uses company working days & hours. Save to apply a custom schedule.
        @endif
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('hr.employees.working-days.update', $employee->EmployeeNo) }}">
                @csrf
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Day</th>
                                <th style="width: 15%;">Is Working</th>
                                <th style="width: 20%;">Day Length</th>
                                <th style="width: 25%;">Start Time</th>
                                <th style="width: 25%;">End Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($weekdays as $idx => $dayName)
                                @php
                                    $row = $effectiveDays[$idx] ?? null;
                                    $defaultWorking = ($idx >= 1 && $idx <= 5);
                                    $oldDays = old('day');
                                    $isWorking = $oldDays !== null
                                        ? in_array($idx, $oldDays)
                                        : (bool)($row?->IsWorking ?? $defaultWorking);
                                @endphp
                                <tr>
                                    <td>{{ $dayName }}</td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="day[]" value="{{ $idx }}" id="day_{{ $idx }}"
                                                @checked($isWorking)>
                                            <label class="form-check-label" for="day_{{ $idx }}">Working</label>
                                        </div>
                                    </td>
                                    <td>
                                        <select name="day_fraction[{{ $idx }}]" class="form-select">
                                            @foreach(['1' => 'Full Day', '0.5' => 'Half Day'] as $val => $label)
                                                <option value="{{ $val }}" @selected(old("day_fraction.$idx", $row->DayFraction ?? 1) == $val)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="time" name="start_time[{{ $idx }}]" class="form-control"
                                            value="{{ old("start_time.$idx", $row->StartTime ?? '') }}">
                                    </td>
                                    <td>
                                        <input type="time" name="end_time[{{ $idx }}]" class="form-control"
                                            value="{{ old("end_time.$idx", $row->EndTime ?? '') }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Save Working Days</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
