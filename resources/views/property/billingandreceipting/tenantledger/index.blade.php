@extends('layouts.app')
@section('title', 'Tenant Ledger Statement')
@section('content')
    <form method="GET" action="{{ route('tenantledger.index') }}" class="row g-3 mb-3">
    <div class="col-md-4">
        <label>Select Tenant</label>
        <select name="tenant_id" class="form-select">
            <option value="">-- Select Tenant --</option>
            @foreach($newleases as $lease)
                <option value="{{ $lease->Id }}" {{ request('tenant_id') == $lease->Id ? 'selected' : '' }}>
                    {{ $lease->tenant->TenantName }} ({{ $lease->LeaseNumber ?? 'N/A' }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label>From</label>
        <input type="date" name="from" class="form-control" value="{{ request('from') }}">
    </div>

    <div class="col-md-3">
        <label>To</label>
        <input type="date" name="to" class="form-control" value="{{ request('to') }}">
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">View</button>
    </div>
    </form>

    @if($ledger && count($ledger) > 0)
  <!-- Ledger Table -->
  <table class="table table-bordered align-middle mt-3">
    <thead class="table-light">
      <tr>
        <th>Date</th>
        <th>Reference</th>
        <th>Description</th>
        <th class="text-end">Debit (KES)</th>
        <th class="text-end">Credit (KES)</th>
        <th class="text-end">Balance</th>
      </tr>
    </thead>
    <tbody>
    @php $balance = 0; $totalDebit = 0; $totalCredit = 0; @endphp
    @foreach($ledger as $entry)
        @php
            $debit = $entry['type'] === 'debit' ? $entry['amount'] : 0;
            $credit = $entry['type'] === 'credit' ? $entry['amount'] : 0;
            $balance += $debit - $credit;
            $totalDebit += $debit;
            $totalCredit += $credit;
        @endphp
        <tr>
            <td>{{ $entry['date'] }}</td>
            <td>{{ $entry['reference'] }}</td>
            <td>{{ $entry['description'] }}</td>
            <td class="text-end">{{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
            <td class="text-end">{{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
            <td class="text-end">{{ number_format($balance, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot class="table-light">
      <tr>
        <th colspan="3" class="text-end">Total</th>
          <th class="text-end">{{ number_format($totalDebit, 2) }}</th>
          <th class="text-end">{{ number_format($totalCredit, 2) }}</th>
          <th class="text-end">{{ number_format($balance, 2) }}</th>
      </tr>
    </tfoot>
  </table>

  <div class="text-end mt-3">
      <button onclick="window.print()" class="btn btn-outline-secondary">🖨️ Print</button>
      <a href="{{ route('tenantledger.pdf', request()->all()) }}" class="btn btn-outline-primary">⬇ Export PDF</a>
  </div>
    @else
        <p class="text-muted">No data available for the selected filters.</p>
        @endif
</div>
@endsection
