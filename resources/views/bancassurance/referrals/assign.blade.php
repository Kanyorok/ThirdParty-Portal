@extends('layouts.app')
@section('title', 'Assign Referrals')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">
    <table class="table table-bordered" id="assignTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Client Name</th>
                <th>Product</th>
                <th>Referral Date</th>
                <th>Status</th>
                <th>Assign To</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($referrals as $referral)
            <tr>
                <form method="POST" action="{{ route('bancassurance.referrals.assign', $referral->Id) }}">
                    @csrf
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $referral->ClientName }}</td>
                    <td>{{ $referral->insuranceProduct->Description }}</td>
                    <td>{{ \Carbon\Carbon::parse($referral->ReferralDate)->format('d/m/Y') }}</td>
                    <td><span class="badge bg-secondary">{{ $referral->Status->Label() }}</span></td>
                    <td>
                        <select name="AssignedTo" class="form-select" required>
                            <option value="">-- Select Officer --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->Id }}">{{ $emp->FirstName }} {{ $emp->LastName }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <button type="submit" class="btn btn-sm btn-success">Assign</button>
                    </td>
                </form>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @section('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#assignTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    lengthChange: true
                });
            });
        </script>
    @endsection
@endsection
