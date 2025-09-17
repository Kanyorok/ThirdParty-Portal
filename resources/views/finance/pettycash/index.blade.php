@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Petty Cash Vouchers</h4>
  <div class="btn-group">
    <a class="btn btn-outline-primary" href="{{ route('finance.pettycash.disbursement.create') }}">New Disbursement</a>
    <a class="btn btn-outline-success" href="{{ route('finance.pettycash.replenishment.create') }}">Replenishment</a>
    <a class="btn btn-outline-secondary" href="{{ route('finance.pettycash.refund.create') }}">Refund</a>
  </div>
</div>

<form method="GET" class="row g-2 mb-3">
  <div class="col-md-3">
    <label class="form-label">Float</label>
    <select name="FloatID" class="form-select" onchange="this.form.submit()">
      <option value="">(Any)</option>
      @foreach($floats as $f)<option value="{{ $f->FloatID }}" @selected(request('FloatID')==$f->FloatID)>{{ $f->Name }}</option>@endforeach
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Type</label>
    <select name="VoucherType" class="form-select" onchange="this.form.submit()">
      <option value="">(Any)</option>
      @foreach(['DISBURSEMENT','REPLENISHMENT','REFUND','ADJUSTMENT'] as $t)
        <option value="{{ $t }}" @selected(request('VoucherType')==$t)>{{ $t }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Status</label>
    <select name="Status" class="form-select" onchange="this.form.submit()">
      <option value="">(Any)</option>
      @foreach(['Draft','Posted','Voided'] as $s)
        <option value="{{ $s }}" @selected(request('Status')==$s)>{{ $s }}</option>
      @endforeach
    </select>
  </div>
</form>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="table-responsive">

@if(!empty($widgets))
<div class="row g-3 mb-3">
  @foreach($widgets as $w)
    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">{{ $w['Name'] }}</h6>
            @php
              $warn = $w['Balance'] <= $w['ReorderLevel'];
            @endphp
            <span class="badge bg-{{ $warn ? 'warning text-dark' : 'success' }}">
              {{ $warn ? 'Needs Top-up' : 'OK' }}
            </span>
          </div>
          <div class="mt-2">
            <div class="small text-muted">Balance</div>
            <div class="fs-5 fw-semibold">{{ number_format($w['Balance'],2) }}</div>
          </div>
          <div class="mt-2">
            <div class="small text-muted">Pending Reimbursement</div>
            <div>{{ number_format($w['Pending'],2) }}</div>
          </div>
          <div class="mt-3 d-flex gap-2">
            <a class="btn btn-sm btn-outline-primary"
               href="{{ route('finance.pettycash.replenishment.create') }}">New Replenishment</a>
            <a class="btn btn-sm btn-outline-secondary"
               href="{{ route('finance.pettycash.wizard') }}">Wizard</a>
          </div>
        </div>
      </div>
    </div>
  @endforeach
</div>
@endif

<table class="table table-sm table-striped">
  <thead class="table-light"><tr>
    <th>#</th><th>Float</th><th>Type</th><th>Date</th><th>Amount</th><th>Status</th><th></th>
  </tr></thead>
  <tbody>
  @forelse($rows as $r)
    <tr>
      <td>{{ $r->VoucherID }}</td>
      <td>{{ $r->float?->Name }}</td>
      <td>{{ $r->VoucherType }}</td>
      <td>{{ $r->DocDate }}</td>
      <td>{{ number_format($r->Amount,2) }} {{ $r->currency?->Code }}</td>
      <td><span class="badge bg-{{ $r->Status==='Posted'?'success':($r->Status==='Voided'?'secondary':'warning text-dark') }}">{{ $r->Status }}</span></td>
      <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('finance.pettycash.show',$r->VoucherID) }}">Open</a></td>
    </tr>
  @empty
    <tr><td colspan="7" class="text-center text-muted">No vouchers.</td></tr>
  @endforelse
  </tbody>
</table>
</div>
{{ $rows->links() }}
@endsection
