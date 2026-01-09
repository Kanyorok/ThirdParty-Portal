@extends('layouts.app')

@section('title', 'KPI Goal Set')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">KPI Goal Set</h2>
            <div class="text-muted">{{ $goal->employee?->FirstName }} {{ $goal->employee?->LastName }} • {{ $goal->period?->Name }}</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('hr.kpi.goals.index') }}">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap gap-4">
            <div><strong>Status:</strong> {{ $goal->Status }}</div>
            <div><strong>Total Weight:</strong> {{ number_format((float)$goal->TotalWeight, 2) }}</div>
            <div><strong>Submitted:</strong> {{ $goal->SubmittedOn ? $goal->SubmittedOn->format('Y-m-d') : '-' }}</div>
        </div>
    </div>

    @if($perspectiveGroups->isNotEmpty())
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0">Perspective Summary</h5>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Perspective</th>
                            <th class="text-end">Configured Weight</th>
                            <th class="text-end">Item Weight Total</th>
                            <th class="text-end">Items</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($perspectiveGroups as $perspectiveId => $items)
                            @php
                                $perspectiveName = $perspectiveId && $perspectives->has($perspectiveId)
                                    ? $perspectives[$perspectiveId]->Name
                                    : 'Unassigned';
                                $configWeight = $weightMap->get($perspectiveId)?->Weight;
                                $itemWeight = $items->sum(fn($item) => (float)$item->Weight);
                            @endphp
                            <tr>
                                <td>{{ $perspectiveName }}</td>
                                <td class="text-end">{{ $configWeight !== null ? number_format((float)$configWeight, 4) : '-' }}</td>
                                <td class="text-end">{{ number_format((float)$itemWeight, 2) }}</td>
                                <td class="text-end">{{ $items->count() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white">
            <h5 class="mb-0">Goal Items</h5>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>KPI</th>
                        <th class="text-end">Annual Target</th>
                        <th class="text-end">Period Target</th>
                        <th class="text-end">Weight</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($perspectiveGroups as $perspectiveId => $items)
                        @php
                            $perspectiveName = $perspectiveId && $perspectives->has($perspectiveId)
                                ? $perspectives[$perspectiveId]->Name
                                : 'Unassigned';
                        @endphp
                        <tr class="table-light">
                            <td colspan="5"><strong>{{ $perspectiveName }}</strong></td>
                        </tr>
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $item->kpiItem?->Name }}</td>
                                <td class="text-end">{{ $item->AnnualTarget !== null ? number_format((float)$item->AnnualTarget, 2) : '-' }}</td>
                                <td class="text-end">{{ $item->PeriodTarget !== null ? number_format((float)$item->PeriodTarget, 2) : ($item->TargetValue !== null ? number_format((float)$item->TargetValue, 2) : '-') }}</td>
                                <td class="text-end">{{ number_format((float)$item->Weight, 2) }}</td>
                                <td>{{ $item->Notes }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No goal items.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        @if(in_array($goal->Status, ['Draft','Returned','Rejected'], true))
            <a class="btn btn-outline-secondary" href="{{ route('hr.kpi.goals.edit', $goal->Id) }}">Edit</a>
            <form method="POST" action="{{ route('hr.kpi.goals.submit', $goal->Id) }}">
                @csrf
                <button class="btn btn-primary" type="submit">Submit</button>
            </form>
        @endif
        @if($goal->Status === 'Submitted')
            <form method="POST" action="{{ route('hr.kpi.goals.approve', $goal->Id) }}">
                @csrf
                <button class="btn btn-success" type="submit">Approve</button>
            </form>
            <form method="POST" action="{{ route('hr.kpi.goals.return', $goal->Id) }}" class="d-flex gap-2">
                @csrf
                <input type="text" name="RejectionReason" class="form-control form-control-sm" placeholder="Return reason">
                <button class="btn btn-outline-warning" type="submit">Return</button>
            </form>
            <form method="POST" action="{{ route('hr.kpi.goals.reject', $goal->Id) }}" class="d-flex gap-2">
                @csrf
                <input type="text" name="RejectionReason" class="form-control form-control-sm" placeholder="Reject reason">
                <button class="btn btn-outline-danger" type="submit">Reject</button>
            </form>
        @endif
    </div>
</div>
@endsection
