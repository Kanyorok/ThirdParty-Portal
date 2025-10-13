{{-- resources/views/assets/acc/impair/index.blade.php --}}
@extends('layouts.app')
@section('title','Impairments')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted">Impairment Tests</h6>
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
        <a href="{{ route('assets.acc.impair.create') }}" class="btn btn-primary btn-sm">New</a>
      </div>
    </div>

    <div class="card-body p-3">
      @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light">
            <tr>
              <th>#</th><th>Asset</th><th>Book</th><th>Test Date</th>
              <th>Old NBV</th><th>Recoverable</th><th>Loss</th><th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $i => $r)
              <tr>
                <td>{{ $rows->firstItem()+$i }}</td>
                <td>{{ $r->AssetID }}</td>
                <td>{{ $r->BookID }}</td>
                <td>{{ $r->TestDate }}</td>
                <td>{{ number_format($r->OldNBV,2) }}</td>
                <td>{{ number_format($r->RecoverableAmount,2) }}</td>
                <td>{{ number_format(max(0, $r->OldNBV - $r->RecoverableAmount),2) }}</td>
                <td>{{ $r->Remarks }}</td>
              </tr>
            @empty
              <tr><td colspan="8" class="text-center text-muted">No impairment tests.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $rows->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
