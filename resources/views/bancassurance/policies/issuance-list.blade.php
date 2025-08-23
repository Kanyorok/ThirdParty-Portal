@extends('layouts.app')
@section('title', 'Policies Ready for Issuance')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection


@section('content')
<div class="container mt-4">
    <table class="table table-bordered mt-3" id="issuance">
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
            @forelse($policies as $policy)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $policy->PolicyNumber ?? '—' }}</td>
                <td>{{ $policy->customer->FullName }}</td>
                <td>{{ $policy->product->Name }}</td>
                <td>{{ $policy->insurer->Name }}</td>
                <td>{{ $policy->Status->label() }}</td>
                <td>{{ \Carbon\Carbon::parse($policy->CreatedAt)->format('d/m/Y') }}</td>
                <td>
                    <form action="{{ route('bancassurance.policies.storeIssuance', $policy->Id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <button class="btn btn-sm btn-success">Issue Now</button>
                    </form>
                </td>
            </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>
@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#issuance').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
@endsection
