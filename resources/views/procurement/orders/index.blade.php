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

  <div class="row mb-3">
    <div class="col-12 d-flex justify-content-end">
      <a href="{{ route('purchaseOrder.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Add New LPO
      </a>
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      <div class="card mb-3">
        <div class="card-body">
          <table id="ordersTable" class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
            <thead>
              <tr>
                <th>#</th>
                <th>Order No</th>
                <th>Order Date</th>
                  <th>LPO No</th>
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
                    <td>{{ \Carbon\Carbon::parse($item->OrderDate)->format('d/m/Y') }}</td>
                  <td>{{ $item->ExtOrdNum }}</td>
                  <td>{{ $item->Priority }}</td>
                  <td>{{ $item->BranchID }}</td>
                  <td>{{ number_format($item->UnitPrice, 2) }}</td>
                  <td>{{ $item->ordercount }}</td>
                  <td>{{ $item->CreatedBy }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->CreatedOn)->format('d/m/Y H:i') }}</td>
                    <td><a href="#" class="btn btn-info btn-sm view-order" data-id="{{ $item->Id }}">View</a>
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

  <!-- Modal for Show Order -->
  <div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true"
       data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="orderModalLabel">Purchase Order Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="orderModalBody">
          <!-- Order details will be loaded here -->
        </div>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
  <script>
    function initOrdersPage() {
      // Use off/on to avoid duplicate handlers when partials reload
        $(document).off('click', '.view-order').on('click', '.view-order', function (e) {
            e.preventDefault();
        var orderId = $(this).data('id');
        var url = "{{ url('procurement/purchaseOrder') }}" + "/" + orderId;
            $('#orderModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            var modalEl = document.getElementById('orderModal');
            var modal = window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(modalEl) : null;
            if (modal) {
                modal.show();
            } else {
                $('#orderModal').modal('show');
            }
            $.ajax({
                url: url,
                method: 'GET',
                headers: {'X-Partial': '1'},
                timeout: 20000
            }).done(function (data) {
          $('#orderModalBody').html(data);
            }).fail(function (xhr, status, err) {
                console.error('Load order failed', status, err, xhr && xhr.responseText);
          $('#orderModalBody').html('<div class="alert alert-danger">Failed to load order details.</div>');
        });
      });
    }

    // Initial attach on full page load
    $(function() {
      initOrdersPage();
    });

    // Re-run when partial content is loaded via fragment navigation
    document.addEventListener('partial:loaded', function(e) {
      initOrdersPage();
    });
  </script>
@endpush
