@extends('layouts.app')

@section('title', 'Exit Clearances')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Exit Clearances</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.exit.requests.show', $exit->Id) }}">Back</a>
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

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><strong>Exit No:</strong> {{ $exit->ExitNo }}</div>
                <div class="col-md-4"><strong>Employee:</strong> {{ $exit->employee?->FirstName }} {{ $exit->employee?->LastName }}</div>
                <div class="col-md-4"><strong>Status:</strong> {{ $exit->Status }}</div>
                <div class="col-md-4"><strong>Exit Type:</strong> {{ $exit->exitType?->Name ?? '-' }}</div>
                <div class="col-md-4"><strong>Effective Date:</strong> {{ $exit->EffectiveExitDate?->format('Y-m-d') ?? '-' }}</div>
                <div class="col-md-4"><strong>Policy:</strong> {{ $exit->policy?->Name ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Cleared By</th>
                            <th>Cleared On</th>
                            <th>Details</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exit->clearances as $clearance)
                            @php
                                $deptName = $clearance->department?->Name ?? 'Department';
                                $items = $checklistItems[$deptName] ?? collect();
                            @endphp
                            <tr>
                                <td>{{ $deptName }}</td>
                                <td>{{ $clearance->Status }}</td>
                                <td>{{ $clearance->clearedByUser?->Name ?? '-' }}</td>
                                <td>{{ $clearance->ClearedOn ? $clearance->ClearedOn->format('Y-m-d H:i') : '-' }}</td>
                                <td>
                                    <div class="mb-2">
                                        {{ $clearance->Details ?? $clearance->Remarks ?? '-' }}
                                    </div>
                                    @if($items->isNotEmpty())
                                        <div class="small text-muted">Checklist:</div>
                                        <ul class="small mb-0">
                                            @foreach($items as $item)
                                                <li>{{ $item->ItemName }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('hr.exit.clearances.update', [$exit->Id, $clearance->Id]) }}" method="POST" class="d-inline-flex gap-2 align-items-center">
                                        @csrf
                                        <select name="Status" class="form-select form-select-sm">
                                            @foreach(['Pending','Cleared','Waived','On Hold'] as $status)
                                                <option value="{{ $status }}" @selected($clearance->Status === $status)>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        <textarea name="Details" class="form-control form-control-sm" rows="2" placeholder="Details (equipment returned, access disabled, etc.)">{{ old('Details', $clearance->Details ?? $clearance->Remarks ?? '') }}</textarea>
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Update</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No clearance items available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
