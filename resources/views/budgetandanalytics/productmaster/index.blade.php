@extends('layouts.app')
@section('title', 'Product Master')
@section('content')
  <div class="card p-3">
    <h5>📦 Product Master (CBS Synced)</h5>
    <p class="text-muted">
      Below is a list of Products synced from Core Banking System (CBS).
    </p>
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Product Code</th>
            <th>Product Name</th>
            <th>Product Type</th>
            <th>GL Code (CBS)</th>
            {{-- <th>Is Budgeted</th> --}}
            <th>Status</th>
            {{-- <th>Actions</th>  --}}
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $item)
            <tr>
              <td>{{ $loop->iteration }}.</td>
              <td class="text-break">{{ $item['Code'] }}</td>
              <td class="text-break">{{ $item['Name'] }}</td>
              <td class="text-break">{{ $item['Type'] }}</td>
              <td class="text-break">{{ $item['GLCode'] }}</td>
              {{-- <td><span class="badge bg-success">Yes</span></td> --}}
              <td><span class="badge bg-primary">Active</span></td>
              <td>
                {{-- <button class="btn btn-sm btn-outline-info">👁 View</button> --}}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    function initBudgetProductMaster() {
      // page-specific initialisation (DataTables or plugins can be initialized here)
      // Currently no dynamic widgets; function kept for partial reloads.
    }

    document.addEventListener('partial:loaded', function(e) {
      // re-run inits when the content is replaced via partial navigation
      initBudgetProductMaster();
    });

    // initial run on full load
    document.addEventListener('DOMContentLoaded', initBudgetProductMaster);
  </script>
@endpush
