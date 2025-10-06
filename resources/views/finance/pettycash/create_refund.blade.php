@extends('layouts.app')

@section('content')
<h4 class="mb-3">Petty Cash Refund</h4>

@if ($errors->any())
  <div class="alert alert-danger">
    <strong>Fix the following:</strong>
    <ul class="mb-0">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('finance.pettycash.store') }}">
  @csrf
  <input type="hidden" name="VoucherType" value="REFUND">

  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Float <span class="text-danger">*</span></label>
      <select name="FloatID" class="form-select" required>
        <option value="">-- select --</option>
        @foreach($floats as $f)
          <option value="{{ $f->FloatID }}" @selected(old('FloatID')==$f->FloatID)>{{ $f->Name }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label">Doc Date <span class="text-danger">*</span></label>
      <input type="date" name="DocDate" class="form-control" value="{{ old('DocDate', now()->toDateString()) }}" required>
    </div>

    <div class="col-md-3">
      <label class="form-label">Currency <span class="text-danger">*</span></label>
      <select name="CurrencyID" class="form-select" required>
        @foreach($currencies as $c)
          <option value="{{ $c->Id }}" @selected(old('CurrencyID')==$c->Id)>{{ $c->Code }} — {{ $c->Name }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-md-2">
      <label class="form-label">Rate</label>
      <input type="number" step="0.000001" name="ExchangeRate" class="form-control" value="{{ old('ExchangeRate', 1) }}">
    </div>

    <div class="col-md-6">
      <label class="form-label">Bank Account (Receiver) <span class="text-danger">*</span></label>
      <select name="BankAccountID" class="form-select" required>
        <option value="">-- select --</option>
        @foreach($bankAccounts as $ba)
          <option value="{{ $ba->AccountID }}" @selected(old('BankAccountID')==$ba->AccountID)>
            {{ optional($ba->bank)->BankName }} — {{ $ba->AccountNumber }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label">Amount <span class="text-danger">*</span></label>
      <input type="number" step="0.01" name="Amount" class="form-control" value="{{ old('Amount') }}" required>
    </div>

    <div class="col-md-3">
      <label class="form-label">Reference</label>
      <input name="Reference" class="form-control" value="{{ old('Reference') }}">
    </div>

    <div class="col-md-12">
      <label class="form-label">Narration</label>
      <input name="Narration" class="form-control" value="{{ old('Narration') }}" placeholder="Reason for refund / details">
    </div>
  </div>

  <hr class="my-4">
  <div class="d-flex gap-2">
    <button class="btn btn-success">Save</button>
    <a href="{{ route('finance.pettycash.index') }}" class="btn btn-secondary">Cancel</a>
  </div>
</form>
@endsection
