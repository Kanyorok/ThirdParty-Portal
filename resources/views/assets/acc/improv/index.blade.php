{{-- resources/views/assets/acc/improv/index.blade.php --}}
@extends('layouts.app')
@section('title','Improvements')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted">Asset Improvements (CapEx vs Expense)</h6>
      <div class="d-flex">
        <form method="get" class="me-2 d-flex">
          <select name="book" class="form-select form-select-sm me-2">
            <option value="">All Books</option>
            @foreach($books as $b)
              <option value="{{ $b->Id }}" @selected(request('book')==$b->Id)>{{ $b->Name }}</option>
            @endforeach
          </select>
          <button class="btn btn-outline-secondary btn-sm">Filter</button>
        </form>
        <a href="{{ route('assets.acc.improv.create') }}" class="btn btn-primary btn-sm">New</a>
      </div>
    </div>

    <div class="card-body p-3">
      @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light">
            <tr>
              <th>#</th><th>Asset</th><th>Book</th><th>Doc #</th><th>Date</th><th>Description</th>
              <th class="text-end">Amount</th><th>Treatment</th><th>Posted</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $i => $r)
              <tr>
                <td>{{ $rows->firstItem()+$i }}</td>
                <td>{{ $r->AssetID }}</td>
                <td>{{ $r->BookID }}</td>
                <td>{{ $r->DocNo ?? '—' }}</td>
                <td>{{ $r->DocDate }}</td>
                <td>{{ $r->Description }}</td>
                <td class="text-end">{{ number_format($r->Amount,2) }}</td>
                <td>{{ $r->Treatment }}</td>
                <td>{{ $r->Posted ? 'Yes':'No' }}</td>
              </tr>
            @empty
              <tr><td colspan="9" class="text-center text-muted">No improvements recorded.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $rows->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
