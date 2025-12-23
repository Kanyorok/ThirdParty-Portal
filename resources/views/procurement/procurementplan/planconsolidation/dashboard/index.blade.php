@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Consolidated Procurement Needs')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">🧾 Consolidated Procurement Needs - HO Dashboard</h4>
        
        <style>
            /* Ensure table fits at 100% zoom without overlapping */
            .consolidated-table-wrapper { overflow-x: auto; }
            .consolidated-table { font-size: 0.9rem; table-layout: auto; }
            .consolidated-table th,
            .consolidated-table td { white-space: normal; word-break: break-word; vertical-align: middle; padding: .5rem .6rem; }
            .consolidated-table th { font-weight: 600; }
            /* Tighter named columns with ellipsis */
            .truncate { display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: bottom; }
            .col-item   { max-width: 220px; }
            .col-branch { max-width: 150px; }
            .col-dept   { max-width: 160px; }
            .col-raised { max-width: 170px; }
            /* Numeric alignment */
            .text-num { text-align: right; }
        </style>

        <!-- Filters -->
        <form method="GET" action="{{ route('dashboard.index') }}">
            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label">Filter by Status</label>
                    <select class="form-select filter-auto" name="status">
                        <option value="" {{ empty($status) ? 'selected' : '' }}>All Statuses</option>
                        @foreach($statusOptions as $val => $label)
                            <option value="{{ $val }}" {{ (string)$val === (string)$status ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Branch</label>
                    <select class="form-select filter-auto" name="branch">
                        <option value="" {{ empty($branch) ? 'selected' : '' }}>All Branches</option>
                        @foreach ($branches as $id => $name)
                            <option value="{{ $id }}" {{ (string)$id === (string)$branch ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Department</label>
                    <select class="form-select filter-auto" name="department">
                        <option value="" {{ empty($department) ? 'selected' : '' }}>All Departments</option>
                        @foreach ($departments as $id => $name)
                            <option value="{{ $id }}" {{ (string)$id === (string)$department ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Planning Year</label>
                    <select class="form-select filter-auto" name="year">
                        <option value="" {{ empty($year) ? 'selected' : '' }}>All Years</option>
                        @foreach ($years as $y)
                            <option value="{{ $y }}" {{ (string)$y === (string)$year ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </div>
        </form>

        <!-- Consolidation Table -->
        <div class="table-responsive consolidated-table-wrapper">
            <table id="consolidatedneedsTable" class="table table-bordered table-striped align-middle consolidated-table">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Need ID</th>
                        <th>Item</th>
                        <th>Branch</th>
                        <th>Dept</th>
                        <th>Raised</th>
                        <th>Qty</th>
                        <th>Unit Cost</th>
                        <th>Est. Total</th>
                        <th>Needed</th>
                        <th>Status</th>
                        <th class="text-center">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($needs as $index => $need)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $need->NeedID }}</td>
                            <td>
                                <span class="truncate col-item" title="{{ $need->item->ItemName ?? 'N/A' }}">
                                    {{ $need->item->ItemName ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="truncate col-branch" title="{{ $need->branch->Name ?? 'N/A' }}">
                                    {{ $need->branch->Name ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="truncate col-dept" title="{{ $need->department->Name ?? 'N/A' }}">
                                    {{ $need->department->Name ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="truncate col-raised" title="{{ optional($need->creator)->Name ?? ($need->CreatedByName ?? ($need->CreatedBy ?? 'N/A')) }}">
                                    {{ optional($need->creator)->Name ?? ($need->CreatedByName ?? ($need->CreatedBy ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="text-num">{{ $need->RequestedQty }}</td>
                            <td class="text-num">
                                {{ is_numeric($need->EstimatedUnitCost ?? null)
                                    ? number_format($need->EstimatedUnitCost, 2, '.', ',')
                                    : 'N/A' }}
                            </td>
                            <td class="text-num">{{ number_format($need->RequestedQty * $need->EstimatedUnitCost, 2) }}</td>
                            <td>{{ Carbon::parse($need->RequestedDate)->format('d/m/Y') }}</td>
                            @php
                                // Determine status label and map to badge classes
                                $statusLabel = (is_object($need->Status) && method_exists($need->Status, 'label'))
                                    ? $need->Status->label()
                                    : (string)($need->Status ?? '');
                                $statusKey = strtolower(trim($statusLabel));
                                $badgeClass = 'bg-secondary';
                                if ($statusKey === 'approved') {
                                    $badgeClass = 'bg-success';
                                } elseif ($statusKey === 'rejected') {
                                    $badgeClass = 'bg-danger';
                                } elseif ($statusKey === 'pending') {
                                    $badgeClass = 'bg-warning text-dark';
                                }
                            @endphp
                            <td><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                            <td class="text-center">
                                <button
                                    class="btn btn-sm btn-outline-info px-2 view-need-btn"
                                    title="View"
                                    data-id="{{ $need->NeedID }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#needModal">👁️
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center">No records found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

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
                            <p class="text-muted">
                                <i class="spinner-border spinner-border-sm"></i> Loading details...
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Button -->
        <div class="mt-4 d-flex justify-content-end">
            <a href="{{ route('dashboard.export', request()->query()) }}" class="btn btn-outline-secondary">
                🗃 Export to Excel
            </a>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const needDetails = document.getElementById("needDetails");

    // Function to format ISO date string to DD Mon YYYY
    function formatToDDMonYYYY(dateString) {
        if (!dateString) return 'N/A';

        const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        // Handle YYYY-MM-DD (ISO Format)
        if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
            const [year, month, day] = dateString.split("-");
            return `${parseInt(day)} ${monthNames[parseInt(month) - 1]} ${year}`;
        }

        // Handle DD/MM/YYYY
        if (/^\d{2}\/\d{2}\/\d{4}$/.test(dateString)) {
            const [day, month, year] = dateString.split("/");
            return `${parseInt(day)} ${monthNames[parseInt(month) - 1]} ${year}`;
        }

        // Fallback: Try native parsing
        const date = new Date(dateString);
        if (!isNaN(date)) {
            const d = date.getDate();
            const m = monthNames[date.getMonth()];
            const y = date.getFullYear();
            return `${d} ${m} ${y}`;
        }

        return dateString;
    }

    // Event delegation for view buttons
    document.addEventListener('click', function (e) {
        const button = e.target.closest('.view-need-btn');

        if (button) {
            const needId = button.getAttribute('data-id');
            
            console.log('Fetching need details for ID:', needId);
            
            // Reset modal content
            needDetails.innerHTML = '<p class="text-muted"><i class="spinner-border spinner-border-sm"></i> Loading details...</p>';

            // Construct the URL
            const url = `/procurement/dashboard/show/${needId}`;
            console.log('Fetching from URL:', url);

            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            })
            .then(res => {
                console.log('Response status:', res.status);
                if (!res.ok) {
                    throw new Error(`HTTP error! Status: ${res.status} - ${res.statusText}`);
                }
                return res.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.error) {
                    needDetails.innerHTML = `<p class="text-danger">Error: ${data.error}</p>`;
                    return;
                }

                const formattedDate = formatToDDMonYYYY(data.RequestedDate);

                needDetails.innerHTML = `
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Need ID:</strong> ${data.NeedID || 'N/A'}</li>
                        <li class="list-group-item"><strong>Item Name:</strong> ${data.ItemName || 'N/A'}</li>
                        <li class="list-group-item"><strong>Branch:</strong> ${data.BranchName || 'N/A'}</li>
                        <li class="list-group-item"><strong>Department:</strong> ${data.DepartmentName || 'N/A'}</li>
                        <li class="list-group-item"><strong>Created By:</strong> ${data.CreatedBy || 'N/A'}</li>
                        <li class="list-group-item"><strong>Quantity:</strong> ${data.RequestedQty || 0}</li>
                        <li class="list-group-item"><strong>Est. Total Cost:</strong> KES ${data.EstimatedCost || '0.00'}</li>
                        <li class="list-group-item"><strong>Expected Delivery:</strong> ${formattedDate}</li>
                        <li class="list-group-item"><strong>Status:</strong> <span class="badge bg-info">${data.Status || 'Unknown'}</span></li>
                    </ul>
                `;
            })
            .catch(error => {
                console.error('Fetch error:', error);
                needDetails.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Failed to load data</strong><br>
                        ${error.message}<br>
                        <small class="text-muted">Check browser console for more details</small>
                    </div>
                `;
            });
        }
    });

    // Initialize DataTables only if table has data
    @if(!$needs->isEmpty())
        $('#consolidatedneedsTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            autoWidth: false,
            language: {
                emptyTable: ""
            }
        });
        
        // Auto submit on filter change
        $('.filter-auto').on('change', function(){
            this.form.submit();
        });
    @endif
});
</script>
@endpush