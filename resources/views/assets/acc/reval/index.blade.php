{{-- index --}}
@extends('layouts.app')
@section('title','Revaluations')
@section('content')
<div class="container my-3">
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between">
      <h6 class="mb-0 text-muted">Revaluations</h6>
      <a href="{{ route('assets.acc.reval.create') }}" class="btn btn-primary btn-sm">New</a>
    </div>
    <div class="card-body p-3">
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light"><tr><th>#</th><th>Asset</th><th>Book</th><th>Date</th><th>Old</th><th>New FV</th><th>Δ</th></tr></thead>
          <tbody>
            @forelse($rows as $i => $r)
              <tr>
                <td>{{ $rows->firstItem()+$i }}</td>
                <td>{{ $r->AssetID }}</td><td>{{ $r->BookID }}</td><td>{{ $r->RevalDate }}</td>
                <td>{{ number_format($r->OldNBV,2) }}</td><td>{{ number_format($r->NewFairValue,2) }}</td>
                <td>{{ number_format($r->NewFairValue - $r->OldNBV,2) }}</td>
              </tr>
            @empty <tr><td colspan="7" class="text-center text-muted">No records.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $rows->links() }}
    </div>
  </div>
</div>
@endsection
