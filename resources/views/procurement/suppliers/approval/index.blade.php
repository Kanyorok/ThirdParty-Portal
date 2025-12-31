@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Supplier Approval')
@section('content')
<div class="card p-4 shadow rounded-4">
    <style>
        .approval-table-wrapper {
            overflow-x: auto;
        }

        .approval-table {
            font-size: 0.9rem;
        }

        .approval-table th,
        .approval-table td {
            white-space: normal;
            word-break: break-word;
            vertical-align: middle;
            padding: .5rem .6rem;
        }

        .approval-table th {
            font-weight: 600;
        }
    </style>
    <h4 class="mb-4">✅ Supplier Approval Queue</h4>

    <div class="approval-table-wrapper table-responsive">
        <table id="supplier-approval" class="table table-hover table-bordered approval-table">
            <thead class="table-light">
                <tr>
                    <th>Supplier ID</th>
                    <th>Company Name</th>
                    <th>Tax PIN</th>
                    <th>Business Type</th>
                    <th>Status</th>
                    <th>Submitted On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $index => $supplier)
                <tr>
                    <td>{{ $supplier->SupplierID }}</td>
                    <td>{{ $supplier->party->ThirdPartyName ?? 'N/A' }}</td>
                    <td>{{ $supplier->party->TaxPIN ?? 'N/A' }}</td>
                    <td>
                        @if($supplier->party->types->isNotEmpty())
                        {{ $supplier->party->types->pluck('Name')->join(', ') }}
                        @else
                        N/A
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-info text-dark">{{ $supplier->ApprovalStatus->name ?? 'Submitted' }}</span>
                    </td>
                    <td>{{ Carbon::parse($supplier->CreatedOn)->format('d/m/Y') }}</td>
                    <td>
                        <a class="btn btn-sm btn-primary" href="{{ route('suppliers-approval.show', $supplier->SupplierID) }}">View to Approve</a>
                    </td>
                </tr>
                @empty
                {{-- DataTables handles empty state --}}
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#supplier-approval').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            autoWidth: false,
            language: {
                emptyTable: 'No pending supplier approvals found.'
            }
        });
    });
</script>
@endsection