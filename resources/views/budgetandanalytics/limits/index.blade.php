@extends('layouts.app')
@section('title','Ledger Limits')
@section('content')
<div class="card mt-4">

      <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-info mb-0"></h4>
		<a href="{{ route('budgetandanalytics.limits.create') }}" class="btn btn-primary mb-3">➕ Create Limit</a>
    </div>
  <div class="card-body">
    <table class="table table-bordered">
      <thead class="table-light">
        <tr>
          <th>#</th><th>Budget Line</th><th>Ledger</th><th>Type</th><th>Amount</th><th>Valid From</th><th>Valid To</th>
        </tr>
      </thead>
      <tbody>
        @foreach($limits as $l)
        <tr>
          <td>{{ $l->id }}</td>
          <td>{{ $l->budgetLine->LineName }}</td>
          <td>{{ $l->LedgerID }}</td>
          <td>{{ $l->LimitType }}</td>
          <td>{{ number_format($l->LimitAmount,2) }}</td>
          <td>{{ $l->EffectiveFrom }}</td>
          <td>{{ $l->EffectiveTo ?? '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
