@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Packages — {{ $medical_fund->FundName }}</h4>
    <div class="d-flex gap-2">
      <a href="{{ route('bancassurance.medicalfunds.show', ['medical_fund' => $medical_fund->ID]) }}" class="btn btn-outline-secondary">Back to Fund</a>
      <a href="{{ route('bancassurance.medicalfunds.packages.create', ['medical_fund' => $medical_fund->ID]) }}" class="btn btn-primary">New Package</a>
    </div>
  </div>
  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

  <div class="card">
    <div class="card-body p-0">
      @if($packages->count())
      <div class="table-responsive">
        <table class="table table-striped mb-0 align-middle">
          <thead><tr>
            <th>#</th><th>Name</th><th>Coverage</th><th class="text-end">Premium</th><th>Compulsory</th><th class="text-end">Actions</th>
          </tr></thead>
          <tbody>
          @foreach($packages as $i => $p)
            <tr>
              <td>{{ $packages->firstItem() + $i }}</td>
              <td class="fw-semibold">{{ $p->Name }}</td>
              <td>{{ $p->CoverageDescription ?? '—' }}</td>
              <td class="text-end">{{ number_format((float)$p->Premium,2) }}</td>
              <td>{!! $p->IsCompulsory ? '<span class="badge bg-warning text-dark">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
              <td class="text-end">
                <div class="btn-group">
                   <a href="{{ route('bancassurance.packages.show', $p->ID) }}" class="btn btn-sm btn-outline-secondary">View</a>
                  <a href="{{ route('bancassurance.packages.edit', $p->ID) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                  <form action="{{ route('bancassurance.packages.destroy', $p->ID) }}" method="POST" onsubmit="return confirm('Archive this package?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Archive</button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      @else
        <div class="p-4 text-center text-muted">No packages yet.</div>
      @endif
    </div>
    @if($packages->hasPages())
      <div class="card-footer">{{ $packages->links() }}</div>
    @endif
  </div>
</div>
@endsection
