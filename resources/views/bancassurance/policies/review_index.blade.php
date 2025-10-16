@extends('layouts.app')
@section('title', 'Proposal Review List')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection



@section('content')
<div class="container mt-4">
    <table  class="table table-bordered table-hover"  id="review">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Policy Number</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Sum Assured</th>
                <th>Status</th>
                <th>Submitted On</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($proposals as $p)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{ $p->PolicyNumber ?? '-'}}</td>
                <td>{{ $p->customer->thirdParty->ThirdPartyName ?? '-'}}</td>
                <td>{{ $p->product->Name ?? '-'}}</td>
                <td>{{ number_format($p->SumAssured, 2) }}</td>
                <td>
                    <span class="badge bg-{{ $p->Status->badgeColor() }}">
                        {{ $p->Status->label() }}
                    </span>
                </td>
                <td>{{ \Carbon\Carbon::parse($p->IssuedDate)->format('d/m/Y') }}</td>
                <td>
                    <a href="{{ route('bancassurance.policies.review', $p->Id) }}" class="btn btn-sm btn-info">Review</a>
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
            $('#review').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
@endsection
