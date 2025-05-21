@extends('layouts.app')
@section('title', 'Consolidated Procurement Needs')
@section('content')

<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🧾 Consolidated Procurement Needs - HO Dashboard</h4>

    <!-- Filters -->
    <form method="GET" action="{{ route('dashboard.index') }}">
        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label">Filter by Branch</label>
                <select class="form-select" name="branch">
                    <option value="">All Branches</option>
                    @foreach ($branches as $id => $name)
                        <option value="{{ $id }}" {{ $id == $branch ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Filter by Department</label>
                <select class="form-select" name="department">
                    <option value="">All Departments</option>
                    @foreach ($departments as $id => $name)
                        <option value="{{ $id }}" {{ $id == $department ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Planning Year</label>
                <select class="form-select" name="year">
                    <option value="All Years">All Years</option>
                      @foreach ($years as $y)
                          <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                      @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
            </div>
        </div>
    </form>

    <!-- Consolidation Table -->
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Branch</th>
                <th>Department</th>
                <th>Qty</th>
                <th>Est. Cost</th>
                <th>Required By</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        @stack('scripts')
        <tbody>
            @forelse ($needs as $index => $need)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $need->item->ItemName ?? 'N/A' }}</td>
                    <td>{{ $need->branch->Name ?? 'N/A' }}</td>
                    <td>{{ $need->department->Name ?? 'N/A' }}</td>
                    <td>{{ $need->RequestedQty }}</td>
                    <td>{{ number_format($need->RequestedQty * $need->EstimatedUnitCost, 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($need->CreatedOn)->format('Y-m-d') }}</td>
                    <td><span class="badge bg-info">{{ $need->Status ?? 'Pending' }}</span></td>
                    <td>
                        <button 
                            class="btn btn-sm btn-outline-info view-need-btn" 
                            data-id="{{ $need->id }}"
                            data-bs-toggle="modal" 
                            data-bs-target="#needModal">
                            View
                        </button>

                        <button class="btn btn-sm btn-outline-primary">Include in Plan</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No records found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <!-- Modal -->
      <div class="modal fade" id="needModal" tabindex="-1" aria-labelledby="needModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content rounded-4 shadow">
            <div class="modal-header">
              <h5 class="modal-title" id="needModalLabel">Procurement Need Details</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div id="needDetails">
                <p class="text-muted"><i class="spinner-border spinner-border-sm"></i> Loading details...</p>
              </div>
            </div>
          </div>
        </div>
      </div>


    <div class="mt-4 d-flex justify-content-end">
        <button class="btn btn-outline-secondary">🗃 Export to Excel</button>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("needModal");
    const needDetails = document.getElementById("needDetails");

    document.querySelectorAll('.view-need-btn').forEach(button => {
        button.addEventListener('click', function () {
            const needId = this.getAttribute('data-id');
            console.log('Fetching details for needId:', needId); // Debug: Log the ID
            needDetails.innerHTML = '<p class="text-muted"><i class="spinner-border spinner-border-sm"></i> Loading details...</p>';

            fetch(`/dashboard/show/${needId}`)
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP error! Status: ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    console.log('Response data:', data); // Debug: Log the response
                    needDetails.innerHTML = `
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Item Name:</strong> ${data.ItemName}</li>
                            <li class="list-group-item"><strong>Branch:</strong> ${data.BranchName}</li>
                            <li class="list-group-item"><strong>Department:</strong> ${data.DepartmentName}</li>
                            <li class="list-group-item"><strong>Quantity:</strong> ${data.RequestedQty}</li>
                            <li class="list-group-item"><strong>Est. Cost:</strong> ${data.EstimatedCost}</li>
                            <li class="list-group-item"><strong>Required By:</strong> ${data.CreatedOn}</li>
                            <li class="list-group-item"><strong>Status:</strong> <span class="badge bg-info">${data.Status}</span></li>
                        </ul>
                    `;
                })
                .catch(error => {
                    console.error('Error fetching data:', error); // Debug: Log errors
                    needDetails.innerHTML = '<p class="text-danger">Failed to load data. Please try again.</p>';
                });
        });
    });
});
</script>
@endpush
@endsection