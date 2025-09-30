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

            <div class="card-body p-3">
                <!-- Invoice Entries Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle table-striped1 text-center"
                           style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Vendor</th>
                            <th>Invoice Number</th>
                            <th>Invoice Date</th>
                            <th class="text-end">Amount</th>
                            <th scope="col">Approval</th>
{{--                            <th>Description</th>--}}
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($invoices as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ ($item->thirdParty->TradingName ?? $item->thirdParty->ThirdPartyName) ?? '-' }}</td>
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
