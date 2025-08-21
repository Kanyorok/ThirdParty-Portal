@extends('layouts.app')
@section('title', 'Underwriting Feedback Listing')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection


@section('content')
<div class="container mt-4">
    <h4>Proposals Awaiting Underwriting Feedback</h4>

    <table class="table table-bordered mt-3" id="feedback">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Policy #</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Insurer</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($proposals as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->PolicyNumber }}</td>
                <td>{{ $item->customer->FullName }}</td>
                <td>{{ $item->product->Name }}</td>
                <td>{{ $item->insurer->Name }}</td>
                <td>{{ $item->Status->label() }}</td>
                <td>{{ \Carbon\Carbon::parse($item->CreatedAt)->format('d/m/Y') }}</td>
                <td>
                    <a href="{{ route('bancassurance.policies.feedbackForm', $item->Id) }}"
                        class="btn btn-sm btn-primary">Feedback</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted">No proposals awaiting feedback.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#feedback').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
@endsection
