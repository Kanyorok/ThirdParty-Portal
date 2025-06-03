
@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📜 Tender Bid Opening Ceremony</h4>

    <!-- Tender Details -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="tenderNo" class="form-label fw-bold">Tender No:</label>
                    <select class="form-select" id="tenderNo">
                        <option selected disabled>Search from List</option>
                        @foreach ($tenders as $item)
                            <option value="{{ $item->TenderNo }}">{{ $item->TenderNo }} | {{ $item->Title }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- <div class="col-md-4">
                    <label for="tenderName" class="form-label fw-bold">Tender Name:</label>
                    <input type="text" class="form-control" id="tenderName" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Bidding Closed:</label>
                    <input type="text" class="form-control" value="2025-05-09 17:00" readonly>
                </div> --}}
            </div>
        </div>
    </div>

    <!-- Submissions Table -->
@if ($data)
<div class="table-responsive mb-4">
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
            <tr>
                <th>Supplier Name</th>
                <th>Submission Mode</th>
                <th>Submitted On</th>
                <th>Received By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($submissions as $item)
                <tr>
                    <td>{{$item->SupplierName}}</td>
                    <td><span class="badge bg-info">Manual</span></td>
                    <td>{{ \Carbon\Carbon::parse($item->ReceivedAt)->format('d M Y, h:i A') }}</td>
                    <td>{{ $item->modifiedByUser->Name ?? 'N/A' }}</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-outline-info">View</a>
                        
                    </td>
                </tr>
                
            @endforeach
        </tbody>
    </table>
</div>
@else
    
@endif

    <!-- Bulk Action -->
    {{-- <div class="mb-4 text-end">
        <button class="btn btn-success">
            Send Decryption Request to All Suppliers
        </button>
    </div> --}}
</div>


<script>
    document.getElementById('tenderNo').addEventListener('change', function () {
        const tenderId = this.value;
        if (tenderId) {
            window.location.href = `/procurement/tenderopening/${tenderId}`;
        }
    });
</script>
@endsection
