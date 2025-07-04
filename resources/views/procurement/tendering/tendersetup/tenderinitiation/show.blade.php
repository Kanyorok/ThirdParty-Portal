@extends('layouts.app')
@section('title', 'Tender Item Details')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📄 Tender Item Details – {{$tender->TenderNo}}</h4>


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
                            {{-- <th>Deadline</th>
                            <th>Delivery Timeline</th> --}}
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{$loop->index+1}}</td>
                                <td>{{ $item->item?->ItemName }}</td>
                                <td>{{ $item->category?->Name }}</td>
                                <td>{{$item->QtyToTender}}</td>
                                <td> KES {{ number_format($item->item->price?->ActualPrice ?? 0, 2) }}</td>
                                <td>KES {{ number_format(($item->QtyToTender ?? 0) * ($item->item->price?->ActualPrice ?? 0), 2) }}</td>

                            </tr>
                        @endforeach

                        </tbody>
                        <tfoot class="table-light fw-bold text-end">
                        <tr>
                            <td colspan="5">Total Estimated Cost:</td>
                            <td colspan="3">KES {{ number_format($totalEstimatedCost, 2) }}</td>
                        </tr>
                        </tfoot>

                    </table>
                </div>
            </div>
        </div>


        @if ($tender->TenderType?->name == 'Restricted')
            <!-- Selected Suppliers Section -->
            <div class="card shadow-sm mb-5">
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
                            @foreach ($suppliers as $item)
                                <tr>
                                    <td>{{$loop->index+1}}</td>
                                    <td>{{$item->supplier->SupplierName}}</td>
                                    <td>{{$item->supplier->ContactEmail}}</td>
                                    <td>{{$item->supplier->ContactPhone}}</td>
                                </tr>
                            @endforeach
                            <!-- More suppliers -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        @endif

        <!-- check tender status -->
        @if (!$show)
            <div class="text-end mb-3">
                <!-- Approve Button -->
                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal"
                        data-bs-target="#approveModal">
                    Approve <i class="fa fa-check-circle text-success"></i>
                </button>

                <!-- Reject Button -->
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                        data-bs-target="#rejectModal">
                    Reject <i class="fa fa-times-circle text-danger"></i>
                </button>
                @endif

                <!-- Back Button -->
                <a href="/procurement/initiatetender" class="btn btn-outline-secondary">
                    Back to Tender List
                </a>
            </div>

    </div>



    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('tender.approve') }}">
                @csrf
                @method('POST')
                <input type="hidden" name="tender_id" value="{{ $tender->Id }}">
                <input type="hidden" name="action_type" value="approve">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">Approve Tender <i
                                class="text-success fa fa-check-circle"></i></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p>Are you sure you want to <strong>approve</strong> this tender? <strong>On approval it will
                                also be published</strong></p>
                        <div class="mb-3">
                            <label for="approve_reason" class="form-label">Reason</label>
                            <textarea name="reason" id="approve_reason" class="form-control" rows="3"
                                      required></textarea>
                        </div>
                        @error('reason')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Confirm Approve</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('tender.reject') }}">
                @csrf
                @method('POST')
                <input type="hidden" name="tender_id" value="{{ $tender->Id }}">
                <input type="hidden" name="action_type" value="reject">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Reject Tender</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p>Are you sure you want to <strong>reject</strong> this tender?</p>
                        <div class="mb-3">
                            <label for="reject_reason" class="form-label">Reason</label>
                            <textarea name="reason" id="reject_reason" class="form-control" rows="3"
                                      required></textarea>
                        </div>
                        @error('reason')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Confirm Reject</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


@endsection
