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

  @php
    $isPaginator = isset($details) && method_exists($details, 'links');
    if ($isPaginator) {
      $detailsSorted = $details; // already paginated and ordered server-side
    } else {
      $detailsCollection = isset($details) ? collect($details) : collect();
      if ($detailsCollection->isNotEmpty()) {
        $first = $detailsCollection->first();
        if (is_array($first) ? array_key_exists('CreatedOn', $first) : isset($first->CreatedOn)) {
          $detailsSorted = $detailsCollection->sortByDesc(fn($d) => is_array($d) ? ($d['CreatedOn'] ?? null) : ($d->CreatedOn ?? null))->values();
        } else {
          $detailsSorted = $detailsCollection->sortByDesc(fn($d) => is_array($d) ? ($d['Id'] ?? null) : ($d->Id ?? null))->values();
        }
      } else {
        $detailsSorted = $detailsCollection;
      }
    }
  @endphp

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
                <th>Order Amount</th>
                <th>Order Lines</th>
                <th>Created By</th>
                <th>Created On</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($detailsSorted as $item)
                <tr>
                  <td>{{ $isPaginator ? ($details->firstItem() + $loop->index) : $loop->iteration }}</td>
                  <td>{{ $item->OrderNo }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->OrderDate)->format('d/m/Y') }}</td>
                  <td>{{ $item->ExtOrdNum }}</td>
                  <td>{{ $item->Priority }}</td>
                  @php
                    $totalIncl = null;
                    try {
                      $totalIncl = $item->OrdTotIncl ?? null;
                      if ($totalIncl === null) {
                        $excl = $item->OrdTotExcl ?? null;
                        $tax  = $item->OrdTotTax  ?? null;
                        if ($excl !== null && $tax !== null) {
                          $totalIncl = (float)$excl + (float)$tax;
                        }
                      }
                    } catch (\Throwable $e) { $totalIncl = null; }
                  @endphp
                  <td>{{ number_format(($totalIncl ?? 0), 2) }}</td>
                  <td>{{ $item->ordercount }}</td>
                  <td>{{ $item->CreatedBy }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->CreatedOn)->format('d/m/Y H:i') }}</td>
                    <td>
                      @php
                          // Use DocStatus field directly from database (trim to remove any whitespace)
                          $docStatus = trim($item->DocStatus ?? 'P');
                          
                          // Map status codes to display text and badge classes (matching ApprovalEnum)
                          $statusMap = [
                              'A' => ['text' => 'Approved', 'class' => 'badge bg-success'],
                              'R' => ['text' => 'Rejected', 'class' => 'badge bg-danger'],
                              'S' => ['text' => 'Submitted', 'class' => 'badge bg-warning'],
                              'P' => ['text' => 'Pending', 'class' => 'badge bg-info'],
                              'Ca' => ['text' => 'Cancelled', 'class' => 'badge bg-secondary'],
                              'Co' => ['text' => 'Completed', 'class' => 'badge bg-primary'],
                              '' => ['text' => 'Pending', 'class' => 'badge bg-info'],
                          ];
                          
                          $status = $statusMap[$docStatus] ?? ['text' => "Unknown ($docStatus)", 'class' => 'badge bg-secondary'];
                          $statusText = $status['text'];
                          $badgeClass = $status['class'];
                          $approved = ($docStatus === 'A');
                          $rejected = ($docStatus === 'R');
                      @endphp
                      <span class="{{ $badgeClass }}">{{ $statusText }}</span>
                    </td>
                    <td>
                      <a href="#" class="btn btn-info btn-sm view-order" data-id="{{ $item->Id }}">View</a>
                      @if($approved)
                        <span class="btn btn-success btn-sm disabled" aria-disabled="true" title="This PO is fully approved">Approve</span>
                      @elseif($rejected)
                        <span class="btn btn-outline-secondary btn-sm disabled" aria-disabled="true" title="This PO was rejected">Approve</span>
                      @else
                        <a href="{{ route('purchaseOrder.approval', $item->Id) }}" class="btn btn-success btn-sm">Approve</a>
                        <button type="button" class="btn btn-outline-danger btn-sm reject-order" data-id="{{ $item->Id }}" data-total="{{ $totalIncl ?? 0 }}">Reject</button>
                      @endif
                    </td>
                </tr>
              @empty
                <tr>
                  <td colspan="15" class="text-center">No orders found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
          @if($isPaginator)
            <div class="d-flex justify-content-between align-items-center mt-2">
              <div>
                Showing {{ $details->firstItem() ?? 0 }} to {{ $details->lastItem() ?? 0 }} of {{ $details->total() }} results
              </div>
              <div>
                {{ $details->withQueryString()->links('pagination::bootstrap-5') }}
              </div>
            </div>
          @endif
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

  <!-- Reject Modal -->
  <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="rejectModalLabel">Reject Purchase Order</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form id="rejectForm" action="" method="POST">
                  @csrf
                  <div class="modal-body">
                      <input type="hidden" name="document_type" value="purchase_order">
                      <input type="hidden" name="order_total" id="rejectOrderTotal" value="0">
                      <input type="hidden" name="action" value="reject">
                      <div class="mb-3">
                          <label for="rejection_reason" class="form-label">Reason for rejection</label>
                          <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" placeholder="Provide a brief reason" required></textarea>
                      </div>
                      <div class="alert alert-warning">
                          This will send the P.O back to the previous workflow step.
                      </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-danger">
                          <i class="fas fa-times"></i> Confirm Reject
                      </button>
                  </div>
              </form>
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

      // Handler for Reject button
      $(document).on('click', '.reject-order', function(e) {
          e.preventDefault();
          var id = $(this).data('id');
          var total = $(this).data('total');
          
          // Construct action URL: Replace placeholder with ID
          // Assuming route is like /procurement/purchaseOrder/{id}/approve
          // adaptable if route('purchaseOrder.approve', ':id') pattern works
          var url = "{{ route('purchaseOrder.approve', ':id') }}".replace(':id', id);
          
          $('#rejectForm').attr('action', url);
          $('#rejectOrderTotal').val(total);
          
          // Clear previous reason
          $('#rejection_reason').val('');
          
          $('#rejectModal').modal('show');
      });
    });

    // Re-run when partial content is loaded via fragment navigation
    document.addEventListener('partial:loaded', function(e) {
      initOrdersPage();
    });
  </script>
@endpush
