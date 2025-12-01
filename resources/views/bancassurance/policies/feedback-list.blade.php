@extends('layouts.app')
@section('title', 'Underwriting Feedback Listing')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-2 px-3">
            <h5 class="mb-0">
                <i class="bi bi-chat-dots me-2"></i> Proposals Awaiting Underwriting Feedback
            </h5>
        </div>

        <div class="card-body">
            <table class="table table-striped table-bordered align-middle" id="feedback">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Policy #</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Insurer</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proposals as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->PolicyNumber ?? '-' }}</td>
                        <td>{{ $item->customer->thirdParty->ThirdPartyName ?? '-' }}</td>
                        <td>{{ $item->product->Name ?? '-' }}</td>
                        <td>{{ $item->insurer->Name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $item->Status->badgeColor() ?? 'secondary' }}">
                                {{ $item->Status->label() ?? '-' }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($item->CreatedAt)->format('d M Y') ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('bancassurance.policies.feedbackForm', $item->Id) }}"
                                class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil-square me-1"></i> Feedback
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
    $(document).ready(function() {
        $('#feedback').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection