@extends('layouts.app')
@section('title', 'Tender Details')
@section('content')
<div class="container">
    <h2>{{ $tender->Title }}</h2>
    <p><strong>Tender Number:</strong> {{ $tender->TenderNumber }}</p>
    <p><strong>Procurement Mode:</strong> {{ $tender->procurementMode->Name ?? '—' }}</p>
    <p><strong>Estimated Value:</strong> {{ number_format($tender->EstimatedValue, 2) }} {{ $tender->Currency }}</p>
    <p><strong>Start Date:</strong> {{ $tender->stages->first()?->StartDate ?? 'N/A' }}</p>

    <hr>

    <h4>📅 Timeline Stages</h4>
    @if($tender->stages->count())
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Stage</th>
                    <th>Duration (Days)</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tender->stages as $stage)
                    <tr>
                        <td>{{ $stage->Stage }}</td>
                        <td>{{ $stage->DurationDays }}</td>
                        <td>{{ \Carbon\Carbon::parse($stage->StartDate)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($stage->EndDate)->format('d M Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No stages generated for this tender yet.</p>
    @endif
</div>
@endsection
