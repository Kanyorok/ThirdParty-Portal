@extends('layouts.app')
@section('title', 'View Counsel')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">👨‍⚖️ Counsel Details</h4>
    <ul class="list-group">
        <li class="list-group-item"><strong>Name:</strong> {{ $counsel->CounselName }}</li>
        <li class="list-group-item"><strong>Firm:</strong> {{ $counsel->FirmName }}</li>
        <li class="list-group-item"><strong>Email:</strong> {{ $counsel->Email }}</li>
        <li class="list-group-item"><strong>Phone:</strong> {{ $counsel->Phone }}</li>
        <li class="list-group-item"><strong>Role:</strong> {{ $counsel->Role }}</li>
        <li class="list-group-item"><strong>External:</strong> {{ $counsel->IsExternal ? 'Yes' : 'No' }}</li>
        <li class="list-group-item"><strong>Assigned On:</strong> {{ \Carbon\Carbon::parse($counsel->AssignedOn)->format('d M Y') }}</li>
        <li class="list-group-item"><strong>Remarks:</strong> {{ $counsel->Remarks }}</li>
    </ul>

    <a href="{{ route('legal.disputes.counsels.index', $case->ID) }}" class="btn btn-secondary mt-3">↩️ Back to List</a>
</div>
@endsection
