@extends('layouts.app')
@section('title','Service Providers')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted"><i class="fas fa-handshake me-2"></i> Service Providers</h6>
      <div class="d-flex">
        <form method="get" class="me-2 d-flex align-items-center">
          <input name="q" value="{{ $q }}" class="form-control form-control-sm me-2" placeholder="Search">
          <select name="category" class="form-select form-select-sm">
            <option value="">All</option>
            @foreach($categories as $c)<option value="{{ $c }}" @selected(request('category')===$c)>{{ $c }}</option>@endforeach
          </select>
        </form>
        <a href="{{ route('assets.settings.service-providers.create') }}" class="btn btn-primary btn-sm">New</a>
      </div>
    </div>
    <div class="card-body p-3">
      @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle">
          <thead class="table-light"><tr><th>#</th><th>Name</th><th>Category</th><th>Email</th><th>Phone</th><th>Active</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
          @forelse($rows as $i => $r)
            <tr>
              <td>{{ $rows->firstItem() + $i }}</td>
              <td><a href="{{ route('assets.settings.service-providers.show',$r->Id) }}">{{ $r->Name }}</a></td>
              <td>{{ $r->Category }}</td>
              <td>{{ $r->ContactEmail }}</td>
              <td>{{ $r->ContactPhone }}</td>
              <td>{{ $r->IsActive ? 'Yes' : 'No' }}</td>
              <td class="text-end">
                <a href="{{ route('assets.settings.service-providers.edit',$r->Id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                <form method="post" action="{{ route('assets.settings.service-providers.destroy',$r->Id) }}" class="d-inline" onsubmit="return confirm('Delete this provider?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-outline-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted">No records.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $rows->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
