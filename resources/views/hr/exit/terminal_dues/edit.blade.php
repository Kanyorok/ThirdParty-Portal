@extends('layouts.app')

@section('title', 'Terminal Dues')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Terminal Dues - {{ $exit->ExitNo }}</h2>
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
                <div class="col-md-4"><strong>Employee:</strong> {{ $exit->employee?->FirstName }} {{ $exit->employee?->LastName }}</div>
                <div class="col-md-4"><strong>Exit Type:</strong> {{ $exit->exitType?->Name ?? '-' }}</div>
                <div class="col-md-4"><strong>Effective Date:</strong> {{ $exit->EffectiveExitDate?->format('Y-m-d') ?? '-' }}</div>
            </div>
            <div class="mt-3 d-flex flex-wrap gap-2">
                <form action="{{ route('hr.exit.terminal-dues.generate', $exit->Id) }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-primary" type="submit">Generate Dues</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h5 class="mb-3">Dues Lines</h5>
            @php
                $totalEarnings = $exit->terminalDues->where('IsEarning', true)->sum('Amount');
                $totalDeductions = $exit->terminalDues->where('IsEarning', false)->sum('Amount');
                $netTotal = $totalEarnings - $totalDeductions;
            @endphp
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Component</th>
                            <th>Type</th>
                            <th>Taxable</th>
                            <th>Amount</th>
                            <th>Notes</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exit->terminalDues as $line)
                            <tr>
                                <td>{{ $line->ComponentName }}</td>
                                <td>{{ $line->IsEarning ? 'Earning' : 'Deduction' }}</td>
                                <td>{{ $line->IsTaxable ? 'Yes' : 'No' }}</td>
                                <td>{{ number_format($line->Amount, 2) }}</td>
                                <td>{{ $line->Notes ?? '-' }}</td>
                                <td class="text-end">
                                    <form action="{{ route('hr.exit.terminal-dues.destroy', [$exit->Id, $line->Id]) }}" method="POST" onsubmit="return confirm('Remove this line?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No terminal dues lines recorded.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Totals</th>
                            <th>{{ number_format($netTotal, 2) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Add Line</h5>
            <form action="{{ route('hr.exit.terminal-dues.store', $exit->Id) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Code</label>
                        <input type="text" name="ComponentCode" class="form-control" value="{{ old('ComponentCode') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Component Name *</label>
                        <input type="text" name="ComponentName" class="form-control" value="{{ old('ComponentName') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Amount *</label>
                        <input type="number" step="0.01" name="Amount" class="form-control" value="{{ old('Amount') }}" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsEarning" value="1" id="isEarning" @checked(old('IsEarning', true))>
                            <label class="form-check-label" for="isEarning">Earning</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsTaxable" value="1" id="isTaxable" @checked(old('IsTaxable'))>
                            <label class="form-check-label" for="isTaxable">Taxable</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="Notes" class="form-control" rows="3">{{ old('Notes') }}</textarea>
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Add Line</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
