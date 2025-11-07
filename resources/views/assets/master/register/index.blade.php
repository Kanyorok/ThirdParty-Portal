@extends('layouts.app')
@section('title','Asset Register')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted"><i class="fas fa-boxes me-2"></i> Asset Register</h6>
      <div class="d-flex">
        <form method="get" class="me-2 d-flex align-items-center">
          <input name="q" value="{{ $q }}" class="form-control form-control-sm me-2" placeholder="Search code/name/serial">
          <select name="class" class="form-select form-select-sm me-2">
            <option value="">Class: All</option>
            @foreach($classes as $c)<option value="{{ $c->Id }}" @selected(request('class')==$c->Id)>{{ $c->Name }}</option>@endforeach
          </select>
          <select name="status" class="form-select form-select-sm me-2">
            @foreach(['','Active','Inactive','Disposed'] as $s)
              <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s==''?'Status: All':$s }}</option>
            @endforeach
          </select>
          <button class="btn btn-outline-secondary btn-sm">Filter</button>
        </form>
        <a href="{{ route('assets.master.register.create') }}" class="btn btn-primary btn-sm">New</a>
      </div>
    </div>
    <div class="card-body p-3">
      @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle">
          <thead class="table-light">
            <tr><th>#</th><th>Code</th><th>Name</th><th>Class</th><th>Status</th><th>Location</th><th>Serial</th><th class="text-end">Actions</th></tr>
          </thead>
          <tbody>
          @forelse($rows as $i => $r)
            <tr>
              <td>{{ $rows->firstItem() + $i }}</td>
              <td><a href="{{ route('assets.master.register.show',$r->Id) }}">{{ $r->AssetCode }}</a></td>
              <td>{{ $r->AssetName }}</td>
              <td>{{ $r->ClassID }}</td>
              <td>{{ $r->Status }}</td>
              <td>{{ $r->LocationID }}</td>
              <td>{{ $r->SerialNumber }}</td>
              <td class="text-end">
                <a href="{{ route('assets.master.register.edit',$r->Id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                <form method="post" action="{{ route('assets.master.register.destroy',$r->Id) }}" class="d-inline" onsubmit="return confirm('Delete this asset?')">
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
