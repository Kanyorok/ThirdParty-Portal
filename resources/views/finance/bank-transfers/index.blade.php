@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Bank Transfers</h4>
  <a href="{{ route('finance.banktransfers.create') }}" class="btn btn-primary">New Transfer</a>
</div>

@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

<div class="table-responsive">
<table class="table table-sm table-striped align-middle">
  <thead class="table-light">
    <tr>
      <th>#</th>
      <th>Date</th>
      <th>From</th>
      <th>To</th>
      <th>Amount</th>
      <th>Status</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    @forelse($rows as $t)
      <tr>
        <td>{{ $t->TransferID }}</td>
        <td>{{ \Illuminate\Support\Carbon::parse($t->DocDate)->format('Y-m-d') }}</td>
        <td>{{ optional($t->fromAccount->bank)->BankName }} — {{ $t->fromAccount->AccountNumber }}</td>
        <td>{{ optional($t->toAccount->bank)->BankName }} — {{ $t->toAccount->AccountNumber }}</td>
        <td>{{ number_format($t->Amount,2) }} {{ $t->currency?->Code }}</td>
        <td><span class="badge bg-{{ $t->Status === 'Posted' ? 'success' : ($t->Status === 'Draft' ? 'secondary' : 'danger') }}">{{ $t->Status }}</span></td>
        <td class="text-end">
          <a href="{{ route('finance.banktransfers.show',$t->TransferID) }}" class="btn btn-sm btn-outline-primary">Open</a>
        </td>
      </tr>
    @empty
      <tr><td colspan="7" class="text-center text-muted">No transfers yet.</td></tr>
    @endforelse
  </tbody>
</table>
</div>

{{ $rows->links() }}
@endsection
