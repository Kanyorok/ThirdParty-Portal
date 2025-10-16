@extends('layouts.app')
@section('title', 'Invoice Entry - Accounts Payable')

@section('content')
    <div class="container my-3">
        <!-- Card for Invoice Entries -->
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info">📑 Invoice Entries</h5>
                <a href="{{ route('invoiceentry.create') }}" class="btn btn-info btn-sm p-2">
                    <i class="fas fa-plus me-1"></i> Add Invoice
                </a>
            </div>

            <!-- Filter Section -->
            <div class="card-body border-bottom pb-0">
                <form action="{{ route('invoiceentry.index') }}" method="GET" id="filter-form" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="vendor_name" class="form-label small text-muted">Vendor Name</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-building"></i></span>
                            <input type="text" class="form-control form-control-sm" id="vendor_name" name="vendor_name"
                                value="{{ request('vendor_name') }}" placeholder="Search vendor...">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="invoice_number" class="form-label small text-muted">Invoice Number</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-hashtag"></i></span>
                            <input type="text" class="form-control form-control-sm" id="invoice_number" name="invoice_number"
                                value="{{ request('invoice_number') }}" placeholder="Search invoice...">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="date_from" class="form-label small text-muted">Date From</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control form-control-sm" id="date_from" name="date_from"
                                value="{{ request('date_from') }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="date_to" class="form-label small text-muted">Date To</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control form-control-sm" id="date_to" name="date_to"
                                value="{{ request('date_to') }}">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="approval_status" class="form-label small text-muted">Status</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-check-circle"></i></span>
                            <select class="form-select form-select-sm" id="approval_status" name="approval_status">
                                <option value="all" {{ request('approval_status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                                @foreach($approvalStatuses as $status)
                                    <option value="{{ $status }}" {{ request('approval_status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label for="per_page" class="form-label small text-muted">Per Page</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fas fa-list-ol"></i></span>
                            <select class="form-select form-select-sm" id="per_page" name="per_page">
                                @foreach([15, 25, 50, 100] as $perPageOption)
                                    <option value="{{ $perPageOption }}" {{ request('per_page', 15) == $perPageOption ? 'selected' : '' }}>
                                        {{ $perPageOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12 d-flex justify-content-end mb-3">
                        <button type="submit" class="btn btn-sm btn-primary me-2">
                            <i class="fas fa-filter me-1"></i> Apply Filters
                        </button>
                        <a href="{{ route('invoiceentry.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo me-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="card-body p-3">
                <!-- Invoice Entries Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle table-striped1 text-center"
                           style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>
                                <a href="{{ route('invoiceentry.index', array_merge(request()->query(), ['sort_by' => 'ThirdPartyID', 'sort_direction' => request('sort_direction') == 'asc' && request('sort_by') == 'ThirdPartyID' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Vendor
                                    @if(request('sort_by') == 'ThirdPartyID')
                                        <i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }} ms-1 text-muted"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('invoiceentry.index', array_merge(request()->query(), ['sort_by' => 'InvoiceNumber', 'sort_direction' => request('sort_direction') == 'asc' && request('sort_by') == 'InvoiceNumber' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Invoice Number
                                    @if(request('sort_by') == 'InvoiceNumber')
                                        <i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }} ms-1 text-muted"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('invoiceentry.index', array_merge(request()->query(), ['sort_by' => 'InvoiceDate', 'sort_direction' => request('sort_direction') == 'asc' && request('sort_by') == 'InvoiceDate' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Invoice Date
                                    @if(request('sort_by') == 'InvoiceDate')
                                        <i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }} ms-1 text-muted"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-end">
                                <a href="{{ route('invoiceentry.index', array_merge(request()->query(), ['sort_by' => 'InvoiceAmount', 'sort_direction' => request('sort_direction') == 'asc' && request('sort_by') == 'InvoiceAmount' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Amount
                                    @if(request('sort_by') == 'InvoiceAmount')
                                        <i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }} ms-1 text-muted"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('invoiceentry.index', array_merge(request()->query(), ['sort_by' => 'ApprovalStatus', 'sort_direction' => request('sort_direction') == 'asc' && request('sort_by') == 'ApprovalStatus' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Approval
                                    @if(request('sort_by') == 'ApprovalStatus')
                                        <i class="fas fa-sort-{{ request('sort_direction') == 'asc' ? 'up' : 'down' }} ms-1 text-muted"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($invoices as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ ($item->thirdParty->TradingName ?? optional($item->thirdParty)->ThirdPartyName) ?? '-' }}</td>
                                <td>{{ $item->InvoiceNumber ?? '-' }}</td>
                                <td>{{ $item->InvoiceDate ? \Carbon\Carbon::parse($item->InvoiceDate)->format('d-m-Y') : '-' }}</td>
                                <td class="text-end">{{ $item->InvoiceAmount ? number_format($item->InvoiceAmount, 2) : '-' }}</td>
                                <td>
                                    @php
                                        $statusClass = match($item->ApprovalStatus) {
                                            'posted' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'draft' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ ucfirst($item->ApprovalStatus) ?? 'Pending' }}
                                    </span>
                                </td>
{{--                                <td>{{ $item->Description ?? '-' }}</td>--}}
                                <td class="text-center">
                                    <a href="{{ route('invoiceentry.show', $item->Id) }}"
                                       class="btn btn-sm btn-outline-info me-1"
                                       title="View Invoice">
                                        <i class="fas fa-eye"></i>
                                    </a>
{{--                                    @if(strtolower($item->ApprovalStatus) === 'draft')--}}
{{--                                        <a href="{{ route('invoiceentry.edit', $item->Id) }}"--}}
{{--                                        class="btn btn-sm btn-outline-primary me-1"--}}
{{--                                        title="Edit">--}}
{{--                                            <i class="fas fa-edit"></i>--}}
{{--                                        </a>--}}
{{--                                    @else--}}
{{--                                        <a href="#"--}}
{{--                                        class="btn btn-sm btn-outline-primary me-1 disabled"--}}
{{--                                        title="Edit (disabled)">--}}
{{--                                            <i class="fas fa-edit"></i>--}}
{{--                                        </a>--}}
{{--                                    @endif--}}

                                    @if(strtolower($item->ApprovalStatus) === 'draft')
                                        <button type="button"
                                            class="btn btn-sm btn-danger custom-delete-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#customDeleteConfirmModal"
                                            data-name="{{$item->InvoiceNumber}}"    {{-- Pass item name --}}
                                            data-route="{{ route('invoiceentry.destroy', $item->Id) }}">
                                            <i  class="fas fa-trash-alt"></i>
                                        </button>
                                    @else
                                        <button type="button"
                                            class="btn btn-sm btn-danger custom-delete-btn disabled"
                                            data-bs-toggle="modal"
                                            data-bs-target="#customDeleteConfirmModal"
                                            data-name="{{$item->InvoiceNumber}}"    {{-- Pass item name --}}
                                            data-route="{{ route('invoiceentry.destroy', $item->Id) }}">
                                            <i  class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                    {{-- <form action="{{ route('invoiceentry.destroy', $item->Id) }}" method="POST"
                                          style="display:inline;"
                                          onsubmit="return confirm('Are you sure you want to delete this invoice?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form> --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-0">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>

                                            <i>No invoice entries found.</i>
                                        </p>
                                        <a href="{{ route('invoiceentry.create') }}" class="btn btn-info px-4 py-2">
                                            <i class="fas fa-plus-circle me-2"></i> Add Invoice
                                        </a>
                                    </div>
                                </td>
                            </tr>

                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Add pagination if available --}}
                @if(method_exists($invoices, 'links'))
                    <div class="mt-3">
                        {{ $invoices->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@include('components.modals.delete-confirm')
@endsection

@section('styles')
    <style>
        /* Hover effect for rows */
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        /* Compact buttons */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Table padding */
        .table-sm th, .table-sm td {
            padding: 0.5rem;
        }

        /* Card styling */
        .card {
            border: none;
            border-radius: 0.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .table-responsive {
                font-size: 0.875rem;
            }
            .btn-sm {
                padding: 0.2rem 0.4rem;
            }
        }
    </style>
@endsection
