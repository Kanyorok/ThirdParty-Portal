@extends('layouts.app')
@section('title','CWIP Projects')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted">CWIP Projects</h6>
      <div class="d-flex">
        <form method="get" class="me-2"><input name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Search"></form>
        <a href="{{ route('assets.acq.cwip-projects.create') }}" class="btn btn-primary btn-sm">New</a>
      </div>
    </div>
    <div class="card-body p-3">
      @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light"><tr><th>#</th><th>Code</th><th>Name</th><th>Status</th><th>Budget</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            @forelse($rows as $i => $r)
            <tr>
              <td>{{ $rows->firstItem() + $i }}</td>
              <td><a href="{{ route('assets.acq.cwip-projects.show',$r->Id) }}">{{ $r->ProjectCode }}</a></td>
              <td>{{ $r->ProjectName }}</td>
              <td>{{ $r->Status }}</td>
              <td>{{ number_format($r->CapexBudget,2) }}</td>
              <td class="text-end">
                <a href="{{ route('assets.acq.cwip-projects.edit',$r->Id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                <form method="post" action="{{ route('assets.acq.cwip-projects.destroy',$r->Id) }}" class="d-inline" onsubmit="return confirm('Delete project?')">
                  @csrf @method('DELETE') <button class="btn btn-outline-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
            @empty <tr><td colspan="6" class="text-center text-muted">No projects.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $rows->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
