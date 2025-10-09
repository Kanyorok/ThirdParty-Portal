@extends('layouts.app')
@section('content')
<h4 class="mb-3">Petty Cash Disbursement</h4>
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<form method="POST" action="{{ route('finance.pettycash.store') }}">@csrf
<input type="hidden" name="VoucherType" value="DISBURSEMENT">

<div class="row g-3">
  <div class="col-md-4">
    <label class="form-label">Float</label>
    <select name="FloatID" class="form-select" required>
      <option value="">-- select --</option>
      @foreach($floats as $f)<option value="{{ $f->FloatID }}">{{ $f->Name }}</option>@endforeach
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Doc Date</label>
    <input type="date" name="DocDate" class="form-control" value="{{ now()->toDateString() }}" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Currency</label>
    <select name="CurrencyID" class="form-select" required>
      @foreach($currencies as $c)<option value="{{ $c->Id }}">{{ $c->Code }} — {{ $c->Name }}</option>@endforeach
    </select>
  </div>
  <div class="col-md-2">
    <label class="form-label">Rate</label>
    <input type="number" step="0.000001" name="ExchangeRate" class="form-control" value="1">
  </div>

  <div class="col-md-12"><hr><strong>Lines</strong></div>
  <div id="lines">
    <div class="row g-2 mb-2 line">
      <div class="col-md-5"><input name="lines[0][Description]" class="form-control" placeholder="Description"></div>
      <div class="col-md-3"><input name="lines[0][GLAccountID]" class="form-control" placeholder="Expense GL ID"></div>
      <div class="col-md-2"><input type="number" step="0.01" name="lines[0][Amount]" class="form-control" placeholder="Amount"></div>
      <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.line').remove()">Remove</button></div>
    </div>
  </div>
  <div class="col-md-12"><button type="button" class="btn btn-outline-secondary" onclick="addLine()">+ Add Line</button></div>

  <div class="col-md-4">
    <label class="form-label">Total Amount</label>
    <input type="number" step="0.01" name="Amount" class="form-control" required>
  </div>
  <div class="col-md-4"><label class="form-label">Reference</label><input name="Reference" class="form-control"></div>
  <div class="col-md-4"><label class="form-label">Narration</label><input name="Narration" class="form-control"></div>
</div>

<hr class="my-4">
<button class="btn btn-success">Save</button>
<a class="btn btn-secondary" href="{{ route('finance.pettycash.index') }}">Cancel</a>
</form>

<script>
let idx=1;
function addLine(){
  const c = document.getElementById('lines');
  const row = document.createElement('div');
  row.className = 'row g-2 mb-2 line';
  row.innerHTML = `
    <div class="col-md-5"><input name="lines[${idx}][Description]" class="form-control" placeholder="Description"></div>
    <div class="col-md-3"><input name="lines[${idx}][GLAccountID]" class="form-control" placeholder="Expense GL ID"></div>
    <div class="col-md-2"><input type="number" step="0.01" name="lines[${idx}][Amount]" class="form-control" placeholder="Amount"></div>
    <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.line').remove()">Remove</button></div>
  `;
  c.appendChild(row); idx++;
}
</script>
@endsection
