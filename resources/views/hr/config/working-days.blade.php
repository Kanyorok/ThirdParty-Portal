@extends('layouts.app')

@section('title', 'Working Days & Hours')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Working Days & Standard Hours</h2>
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

    @php
        $activeDays = collect(old('day', $days->filter(fn($d) => $d->IsWorking ?? false)->keys()->all()));
    @endphp

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('hr.config.workingdays.update') }}">
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
                                @php $row = $days[$idx] ?? null; @endphp
                                <tr>
                                    <td>{{ $dayName }}</td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="day[]" value="{{ $idx }}" id="day_{{ $idx }}"
                                                @checked($activeDays->contains($idx))>
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
