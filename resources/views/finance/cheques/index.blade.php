@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Cheques</h4>
  <div class="btn-group">
    <a href="{{ route('finance.cheques.issued.create') }}" class="btn btn-outline-primary">New Issued Cheque</a>
    <a href="{{ route('finance.cheques.received.create') }}" class="btn btn-outline-success">New Received Cheque</a>
  </div>
</div>

<form method="GET" class="row g-2 align-items-end mb-3">
  <div class="col-sm-3">
    <label class="form-label">Direction</label>
    <select name="dir" class="form-select" onchange="this.form.submit()">
      @php $dir = strtoupper($dir ?? 'ISSUED'); @endphp
      <option value="ISSUED"  @selected($dir==='ISSUED')>ISSUED</option>
      <option value="RECEIVED" @selected($dir==='RECEIVED')>RECEIVED</option>
    </select>
  </div>
  <div class="col-sm-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select" onchange="this.form.submit()">
      <option value="">(Any)</option>
      @foreach(['Draft','Issued','OnHand','Deposited','Cleared','Bounced','Cancelled'] as $s)
        <option value="{{ $s }}" @selected(($status ?? '')===$s)>{{ $s }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-sm-auto">
    <button class="btn btn-secondary">Filter</button>
  </div>
</form>

@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

<div class="table-responsive">
<table class="table table-sm table-striped align-middle">
  <thead class="table-light">
    <tr>
      <th>#</th>
      <th>Dir</th>
      <th>Bank</th>
      <th>Cheque No.</th>
      <th>Cheque Date</th>
      <th>Amount</th>
      <th>Party</th>
      <th>Status</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    @forelse($rows as $r)
      <tr>
        <td>{{ $r->ChequeID }}</td>
        <td>{{ $r->Direction }}</td>
        <td>
          {{ optional($r->bankAccount?->bank)->BankName ?? '-' }}<br>
          <small class="text-muted">{{ $r->bankAccount?->AccountNumber }}</small>
        </td>
        <td>
          {{ $r->ChequeNumber }}
          @if($r->chequeBook) <small class="text-muted d-block">Book: {{ $r->chequeBook->BookName ?? $r->chequeBook->ChequeBookID }}</small> @endif
        </td>
        <td>{{ $r->ChequeDate ? \Illuminate\Support\Carbon::parse($r->ChequeDate)->format('Y-m-d') : '-' }}</td>
        <td>{{ number_format($r->Amount,2) }} {{ $r->currency?->Code }}</td>
        <td>{{ $r->PartyName ?? '-' }}</td>
        <td><span class="badge bg-{{ $r->Status==='Cleared'?'success':($r->Status==='Bounced'?'danger':($r->Status==='Deposited'?'info':($r->Status==='OnHand'?'warning text-dark':($r->Status==='Issued'?'secondary':'dark')))) }}">{{ $r->Status }}</span></td>
        <td class="text-end">
          <a href="{{ route('finance.cheques.show',$r->ChequeID) }}" class="btn btn-sm btn-outline-primary">Open</a>
        </td>
      </tr>
    @empty
      <tr><td colspan="9" class="text-center text-muted">No cheques.</td></tr>
    @endforelse
  </tbody>
</table>
</div>

{{ $rows->links() }}
@endsection
