@extends('layouts.app')
@section('title', 'Communication Log')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">📞 Communication Log for {{ $customer->FullName }}</h4>

    <a href="{{ route('bancassurance.customers.communication.create', $customer->Id) }}" class="btn btn-success mb-3">
        ➕ New Communication Log
    </a>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Summary</th>
                <th>Handled By</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($log->ContactDate)->format('d-M-Y H:i') }}</td>
                    <td>{{ $log->ContactType }}</td>
                    <td>{{ $log->Summary }}</td>
                    <td>{{ $log->HandledByName ?? '—' }}</td>
                    <td>{{ $log->Notes }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No communication logs found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
