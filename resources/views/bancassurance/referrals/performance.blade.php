@extends('layouts.app')
@section('title', 'Referral Performance')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">📊 Staff & Branch Referral Performance</h4>
    <table class="table table-bordered" id="performanceTable">
        <thead>
            <tr>
                <th>Staff</th>
                <th>Branch</th>
                <th>Total Referrals</th>
                <th>Converted</th>
                <th>Pending</th>
            </tr>
        </thead>
        <tbody>
            @foreach($performance as $entry)
                <tr>
                    <td>{{ $entry['StaffName'] }}</td>
                    <td>{{ $entry['BranchName'] }}</td>
                    <td>{{ $entry['Total'] }}</td>
                    <td>{{ $entry['Converted'] }}</td>
                    <td>{{ $entry['Pending'] }}</td>
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
            $('#performanceTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
@endsection
