@extends('layouts.app')
@section('title', 'Proposal Review List')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-2 px-3">
            <h5 class="mb-0">
                <i class="bi bi-list-check me-2"></i> Proposal Review List
            </h5>
        </div>

        <div class="card-body">
            <table class="table table-striped table-bordered align-middle" id="review">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Policy Number</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Sum Assured</th>
                        <th>Status</th>
                        <th>Submitted On</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($proposals as $p)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->PolicyNumber ?? '-' }}</td>
                            <td>{{ $p->customer->thirdParty->ThirdPartyName ?? '-' }}</td>
                            <td>{{ $p->product->Name ?? '-' }}</td>
                            <td>{{ number_format($p->SumAssured, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $p->Status->badgeColor() }}">
                                    {{ $p->Status->label() }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($p->IssuedDate)->format('d M Y') }}</td>                           <td class="text-center">
                                <a href="{{ route('bancassurance.policies.review', $p->Id) }}"
                                   class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye me-1"></i> Review
                                </a>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#review').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
