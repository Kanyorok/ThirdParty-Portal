@extends('layouts.app')
@section('title','Asset Books')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted"><i class="fas fa-book me-2"></i> Asset Books</h6>
      <div class="d-flex">
        <form method="get" class="me-2">
          <input name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Search">
        </form>
        <a href="{{ route('assets.settings.books.create') }}" class="btn btn-primary btn-sm">New</a>
      </div>
    </div>
    <div class="card-body p-3">
      @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
      @endif
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle">
          <thead class="table-light">
            <tr><th>#</th><th>Code</th><th>Name</th><th>Active</th><th class="text-end">Actions</th></tr>
          </thead>
          <tbody>
          @forelse($books as $i => $b)
            <tr>
              <td>{{ $books->firstItem() + $i }}</td>
              <td>{{ $b->Code }}</td>
              <td>{{ $b->Name }}</td>
              <td>{{ $b->IsActive ? 'Yes' : 'No' }}</td>
              <td class="text-end">
                <a href="{{ route('assets.settings.books.edit',$b->Id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                <form method="post" action="{{ route('assets.settings.books.destroy',$b->Id) }}" class="d-inline" onsubmit="return confirm('Delete this book?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-outline-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted">No records.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $books->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
