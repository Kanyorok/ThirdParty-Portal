@extends('layouts.app')
@section('title','Depreciation Runs')
@section('content')
<div class="container my-3">
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted">Depreciation Runs</h6>
      <div>
        <a href="{{ route('assets.acc.depruns.create') }}" class="btn btn-primary btn-sm">New Run</a>
      </div>
    </div>
    <div class="card-body p-3">
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light"><tr><th>#</th><th>Book</th><th>Period</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            @forelse($rows as $i => $r)
            <tr>
              <td>{{ $rows->firstItem()+$i }}</td>
              <td>{{ $r->BookID }}</td>
              <td>{{ $r->PeriodStart }} → {{ $r->PeriodEnd }}</td>
              <td>{{ $r->Status }}</td>
              <td class="text-end">
                <a href="{{ route('assets.acc.depruns.show',$r->Id) }}" class="btn btn-outline-secondary btn-sm">Open</a>
              </td>
            </tr>
            @empty <tr><td colspan="5" class="text-center text-muted">No runs.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $rows->links() }}
    </div>
  </div>
</div>
@endsection
