@extends('layouts.app')
@section('title','📋 Budget Reallocations')
@section('content')

<div class="card mt-4">
  <div class="card-header d-flex justify-content-between align-items-center bg-secondary text-white">
    <a href="{{ route('budgetandanalytics.reallocation.create') }}" class="btn btn-light btn-sm">➕ New Reallocation</a>
  </div>

  <div class="card-body">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Budget</th>
          <th>Branch</th>
          <th>Department</th>
          <th>From Line</th>
          <th>To Line</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($reallocations as $r)
        <tr>
          <td>{{ $r->id }}</td>
          <td>{{ $r->budget->Name ?? '—' }}</td>
          <td>{{ $r->branch->Name ?? '—' }}</td>
          <td>{{ $r->department->Name ?? '—' }}</td>
          <td>{{ $r->fromLine->LineName ?? '—' }}</td>
          <td>{{ $r->toLine->LineName ?? '—' }}</td>
          <td>{{ number_format($r->Amount,2) }}</td>
          <td><span class="badge bg-warning text-dark">{{ $r->Status }}</span></td>
          <td>
            <a href="{{ route('budgetandanalytics.reallocation.review',$r->id) }}" class="btn btn-sm btn-primary">Review</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="9" class="text-center text-muted">No reallocation requests found</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
