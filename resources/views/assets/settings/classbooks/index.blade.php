@extends('layouts.app')
@section('title','Class-Book Overrides')

@section('content')
@php
  $classMap = $classes->keyBy('Id');
  $bookMap = $books->keyBy('Id');
@endphp
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted"><i class="far fa-copy me-2"></i> Class-Book Overrides</h6>
      <a href="{{ route('assets.settings.class-books.create') }}" class="btn btn-primary btn-sm">New</a>
    </div>
    <div class="card-body p-3">
      @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle">
          <thead class="table-light">
          <tr><th>#</th><th>Class</th><th>Book</th><th>Method</th><th>Life (m)</th><th>Residual %</th><th>Active</th><th class="text-end">Actions</th></tr>
          </thead>
          <tbody>
          @forelse($rows as $i => $r)
            <tr>
              <td>{{ $rows->firstItem() + $i }}</td>
              <td>{{ $classMap[$r->ClassID]->Name ?? ('#'.$r->ClassID) }}</td>
              <td>{{ $bookMap[$r->BookID]->Name ?? ('#'.$r->BookID) }}</td>
              <td>{{ $r->DepMethod ?? '—' }}</td>
              <td>{{ $r->UsefulLifeMonths ?? '—' }}</td>
              <td>{{ optional($r->ResidualPct, fn($v)=>number_format($v,2)) ?? '—' }}</td>
              <td>{{ $r->IsActive ? 'Yes' : 'No' }}</td>
              <td class="text-end">
                <a href="{{ route('assets.settings.class-books.show',$r->Id) }}" class="btn btn-outline-primary btn-sm">View</a>
                <a href="{{ route('assets.settings.class-books.edit',$r->Id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                <form method="post" action="{{ route('assets.settings.class-books.destroy',$r->Id) }}" class="d-inline" onsubmit="return confirm('Delete this override?')">
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
      {{ $rows->links() }}
    </div>
  </div>
</div>
@endsection
