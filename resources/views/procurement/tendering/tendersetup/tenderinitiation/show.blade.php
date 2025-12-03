@extends('layouts.app')
@section('title', 'Tender Item Details')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📄 Tender Item Details – {{$tender->TenderNo}}</h4>
        <div class="alert alert-info" role="alert" style="background:#eef6ff;border:1px solid #cfe2ff;color:#084298;">
            <i class="fa fa-info-circle me-2"></i>
            <span title="Open: all suppliers can bid. Restricted: only invited based on selected item category. Use 'Add to Grid' to add items.">
                <strong>Guidance:</strong> Tender Initiation supports two types: Open (all suppliers can bid) and Restricted (only invited suppliers based on the selected item category). Add items to the tender by clicking Add to Grid.
            </span>
        </div>

        <!-- Tender Summary Info -->
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tender Title</label>
                        <input type="text" class="form-control" value="{{$tender->Title}}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tender Type</label>
                        <input type="text" class="form-control" value="{{$tender->TenderType?->name}} Tender" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Initiated By</label>
                        <input type="text" class="form-control" value="{{auth()->user()->Name}}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">📦 Items in this Tender</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Item Description</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Estimated Unit Cost</th>
                                <th>Total Estimated Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{ $item->item?->ItemName ?? 'N/A' }}</td>
                                    <td>{{ $item->category?->Name ?? 'N/A' }}</td>
                                    <td>{{$item->QtyToTender}}</td>
                                    <td>KES {{ number_format($item->item->price?->ActualPrice ?? 0, 2) }}</td>
                                    <td>KES {{ number_format(($item->QtyToTender ?? 0) * ($item->item->price?->ActualPrice ?? 0), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No items found</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold text-end">
                            <tr>
                                <td colspan="5">Total Estimated Cost:</td>
                                <td>KES {{ number_format($totalEstimatedCost, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        @if ($tender->TenderType?->name == 'Restricted')
            <!-- Selected Suppliers Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">🏷️ Selected Suppliers (Restricted Tender)</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Supplier Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($suppliers as $item)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$item->supplier->thirdParty->ThirdPartyName ?? 'N/A'}}</td>
                                        <td>{{$item->supplier->thirdParty->Email ?? 'N/A'}}</td>
                                        <td>{{$item->supplier->thirdParty->Phone ?? 'N/A'}}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No suppliers selected</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Attached Documents --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">📎 Attached Documents</h5>
                <div class="p-3 border rounded bg-light">
                    @forelse($tender->documents as $document)
                        <div class="mb-2">
                            {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                        </div>
                    @empty
                        <p class="text-muted mb-0">No documents attached.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="text-end mb-3">
            @if (!$show)
                <!-- Approve Button -->
                @canApprove('tender')
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="fa fa-check-circle"></i> Approve
                    </button>
                @endcanApprove

                <!-- Reject Button -->
                @canApprove('tender')
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fa fa-times-circle"></i> Reject
                    </button>
                @endcanApprove
            @endif

            <!-- Back Button -->
            <a href="{{ route('initiatetender.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to Tender List
            </a>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('tender.approve') }}">
                @csrf
                <input type="hidden" name="tender_id" value="{{ $tender->Id }}">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">
                            <i class="fa fa-check-circle text-success"></i> Approve Tender
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p>Are you sure you want to <strong>approve</strong> this tender?</p>
                        <p class="text-warning"><strong>Note:</strong> On approval, the tender will be published.</p>
                        <div class="mb-3">
                            <label for="approve_reason" class="form-label">Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" id="approve_reason" class="form-control" rows="3" required></textarea>
                        </div>
                        @error('reason')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        @canApprove('tender')
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check"></i> Confirm Approve
                            </button>
                        @endcanApprove
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('tender.reject') }}">
                @csrf
                <input type="hidden" name="tender_id" value="{{ $tender->Id }}">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">
                            <i class="fa fa-times-circle text-danger"></i> Reject Tender
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p>Are you sure you want to <strong>reject</strong> this tender?</p>
                        <div class="mb-3">
                            <label for="reject_reason" class="form-label">Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" id="reject_reason" class="form-control" rows="3" required></textarea>
                        </div>
                        @error('reason')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        @canApprove('tender')
                            <button type="submit" class="btn btn-danger">
                                <i class="fa fa-times"></i> Confirm Reject
                            </button>
                        @endcanApprove
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection