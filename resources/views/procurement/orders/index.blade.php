@extends('layouts.app')
@section('title', 'Purchase Orders')
@section('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<style>
    .select2-container {
        width: 100% !important;
    }
</style>
@endsection
@section('content')

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <table id="ordersTable"
                    class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                    <thead>

                        <tr>
                            <th>#</th>
                            <th>Order No</th>
                            <th>Order Date</th>
                            <th>RFQ No</th>
                            <th>Priority</th>
                            <th>Branch</th>
                            <th>Order Amount</th>
                            <th>Order Lines</th>
                            <th>Created By</th>
                            <th>Created On</th>
                            <th>Action</th>
                        </tr>

                    </thead>
                    <tbody>
                        @forelse($details as $item)
                          <tr>
                              <td>{{ $loop->iteration }}</td>
                              <td>{{ $item->OrderNo }}</td>
                              <td>{{ \Carbon\Carbon::parse($item->OrderDate)->format('d-m-Y') }}</td>
                              <td>{{ $item->ExtOrdNum }}</td>
                              <td>{{ $item->Priority }}</td>
                              <td>{{ $item->BranchID }}</td>
                              <td>{{ number_format($item->UnitPrice, 2) }}</td>
                              <td>{{ $item->ordercount }}</td>
                              <td>{{ $item->CreatedBy }}</td>
                              <td>{{ \Carbon\Carbon::parse($item->CreatedOn)->format('d-m-Y H:i') }}</td>
                              <td> <a href="{{ route('purchaseOrder.show', $item->Id) }}" class="btn btn-info btn-sm">View</a>
                                  <a href="{{ route('purchaseOrder.approval', $item->Id) }}" class="btn btn-success btn-sm">Approve</a>
                              </td>
                          </tr>
                        @empty
                        <tr>
                            <td colspan="15" class="text-center">No orders found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/datatables.js') }}"></script>
@endsection