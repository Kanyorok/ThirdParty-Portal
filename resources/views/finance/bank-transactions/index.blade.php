@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Bank Transactions</h4>
  <a href="{{ route('finance.banktransactions.create') }}" class="btn btn-primary">New Bank Transaction</a>
</div>

@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

<div class="table-responsive">
<table class="table table-sm table-striped align-middle">
  <thead class="table-light">
    <tr>
      <th>#</th>
      <th>Date</th>
      <th>Bank</th>
      <th>Txn Type</th>
      <th>Amount</th>
      <th>Status</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    @forelse($rows as $r)
      <tr>
        <td>{{ $r->BankTxnID }}</td>
        <td>{{ \Illuminate\Support\Carbon::parse($r->DocDate)->format('Y-m-d') }}</td>
        <td>{{ optional($r->bankAccount->bank)->BankName }} — {{ $r->bankAccount?->AccountNumber }}</td>
        <td>{{ $r->txnType?->Code }} — {{ $r->txnType?->Name }}</td>
        <td>{{ number_format($r->Amount,2) }} {{ $r->currency?->Code }}</td>
        <td>
          <span class="badge bg-{{ $r->Status === 'Posted' ? 'success' : ($r->Status === 'Draft' ? 'secondary' : 'danger') }}">
            {{ $r->Status }}
          </span>
        </td>
        <td class="text-end">
          <a href="{{ route('finance.banktransactions.show',$r->BankTxnID) }}" class="btn btn-sm btn-outline-primary">Open</a>
        </td>
      </tr>
    @empty
      <tr><td colspan="7" class="text-center text-muted">No bank transactions yet.</td></tr>
    @endforelse
  </tbody>
</table>
</div>

{{ $rows->links() }}
@endsection
