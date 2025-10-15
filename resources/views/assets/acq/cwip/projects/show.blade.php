@extends('layouts.app')
@section('title','CWIP • '.$row->ProjectCode)

@section('content')
<div class="container my-3">
  <div class="card shadow-sm mb-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted">Project</h6>
      <div>
        <a href="{{ route('assets.acq.cwip-projects.edit',$row->Id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
        <a href="{{ route('assets.acq.cwip-projects.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
      </div>
    </div>
    <div class="card-body p-3">
      <div class="row g-3">
        <div class="col-md-3"><strong>Code</strong><div>{{ $row->ProjectCode }}</div></div>
        <div class="col-md-5"><strong>Name</strong><div>{{ $row->ProjectName }}</div></div>
        <div class="col-md-2"><strong>Status</strong><div>{{ $row->Status }}</div></div>
        <div class="col-md-2"><strong>Budget</strong><div>{{ number_format($row->CapexBudget,2) }}</div></div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted">CWIP Lines</h6>
      <a href="{{ route('assets.acq.cwip-projects.lines.create',$row->Id) }}" class="btn btn-primary btn-sm">Add Line</a>
    </div>
    <div class="card-body p-3">
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light"><tr><th>#</th><th>Ref</th><th>Description</th><th>Qty</th><th>Unit</th><th>Total</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            @forelse($lines as $i => $l)
            <tr>
              <td>{{ $lines->firstItem() + $i }}</td>
              <td>{{ $l->RefType }} {{ $l->RefID ?? '' }}</td>
              <td>{{ $l->Description }}</td>
              <td>{{ $l->Quantity }}</td>
              <td>{{ number_format($l->UnitCost,2) }}</td>
              <td>{{ number_format($l->TotalCost,2) }}</td>
              <td>{{ $l->Status }}</td>
              <td class="text-end">
                <a href="{{ route('assets.acq.cwip-projects.lines.edit',[$row->Id,$l->Id]) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                <form method="post" action="{{ route('assets.acq.cwip-projects.lines.destroy',[$row->Id,$l->Id]) }}" class="d-inline" onsubmit="return confirm('Delete line?')">
                  @csrf @method('DELETE') <button class="btn btn-outline-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
            @empty <tr><td colspan="8" class="text-center text-muted">No lines.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $lines->links() }}
    </div>
  </div>
</div>
@endsection
