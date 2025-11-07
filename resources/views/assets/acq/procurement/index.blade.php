@extends('layouts.app')
@section('title','Procurement Links (PO/GRN)')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted">Procurement Links</h6>
      <form method="get" class="d-flex">
        <input name="q" value="{{ $q }}" class="form-control form-control-sm me-2" placeholder="PO/GRN or Supplier">
        <select name="type" class="form-select form-select-sm me-2">
          <option value="">All</option>
          @foreach(['PO','GRN'] as $t)<option value="{{ $t }}" @selected($typ===$t)>{{ $t }}</option>@endforeach
        </select>
        <button class="btn btn-outline-secondary btn-sm">Search</button>
      </form>
    </div>
    <div class="card-body p-3">
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light"><tr><th>#</th><th>Type</th><th>Doc No</th><th>Date</th><th>Supplier</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody>
            @forelse($rows as $i => $r)
            <tr>
              <td>{{ $rows->firstItem() + $i }}</td>
              <td>{{ $r->DocType }}</td>
              <td>{{ $r->DocNo }}</td>
              <td>{{ $r->DocDate }}</td>
              <td>{{ $r->SupplierName }}</td>
              <td>{{ number_format($r->Amount,2) }}</td>
              <td>{{ $r->Status }}</td>
            </tr>
            @empty <tr><td colspan="7" class="text-center text-muted">No results.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $rows->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
