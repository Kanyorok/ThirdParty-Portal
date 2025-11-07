@extends('layouts.app')
@section('title','Asset Locations')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted"><i class="fas fa-map-marker-alt me-2"></i> Asset Locations</h6>
      <div class="d-flex">
        <form method="get" class="me-2"><input name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Search"></form>
        <a href="{{ route('assets.settings.locations.create') }}" class="btn btn-primary btn-sm">New</a>
      </div>
    </div>
    <div class="card-body p-3">
      @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle">
          <thead class="table-light">
            <tr><th>#</th><th>Code</th><th>Site</th><th>Building</th><th>Floor</th><th>Room</th><th>Active</th><th class="text-end">Actions</th></tr>
          </thead>
          <tbody>
          @forelse($rows as $i => $r)
            <tr>
              <td>{{ $rows->firstItem() + $i }}</td>
              <td><a href="{{ route('assets.settings.locations.show',$r->Id) }}">{{ $r->Code }}</a></td>
              <td>{{ $r->Site }}</td>
              <td>{{ $r->Building }}</td>
              <td>{{ $r->Floor }}</td>
              <td>{{ $r->Room }}</td>
              <td>{{ $r->IsActive ? 'Yes' : 'No' }}</td>
              <td class="text-end">
                <a href="{{ route('assets.settings.locations.edit',$r->Id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                <form method="post" action="{{ route('assets.settings.locations.destroy',$r->Id) }}" class="d-inline" onsubmit="return confirm('Delete this location?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-outline-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted">No records.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $rows->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
