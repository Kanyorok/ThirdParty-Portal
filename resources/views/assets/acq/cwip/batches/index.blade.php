@extends('layouts.app')
@section('title','Capitalization Batches')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Batches</h6></div>
    <div class="card-body p-3">
      @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
      <div class="table-responsive">
        <table class="table table-sm table-hover">
          <thead class="table-light"><tr><th>#</th><th>Batch No</th><th>Date</th><th>Mode</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            @forelse($rows as $i => $r)
            <tr>
              <td>{{ $rows->firstItem() + $i }}</td>
              <td><a href="{{ route('assets.acq.cap-batches.show',$r->Id) }}">{{ $r->BatchNo }}</a></td>
              <td>{{ $r->BatchDate }}</td>
              <td>{{ $r->Mode }}</td>
              <td>{{ $r->Status }}</td>
              <td class="text-end">
                <form method="post" action="{{ route('assets.acq.cap-batches.destroy',$r->Id) }}" onsubmit="return confirm('Delete batch?')">
                  @csrf @method('DELETE') <button class="btn btn-outline-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
            @empty <tr><td colspan="6" class="text-center text-muted">No batches.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $rows->links() }}
    </div>
  </div>
</div>
@endsection
