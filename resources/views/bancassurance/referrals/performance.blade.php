@extends('layouts.app')
@section('title', 'Referral Performance')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">📊 Staff & Branch Referral Performance</h4>

    <table class="table table-bordered table-striped" id="performanceTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Staff</th>
                <th>Branch</th>
                <th>Total Referrals</th>
                <th>Converted</th>
                <th>Pending</th>
                <th>Conversion Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($performance as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row->StaffName }}</td>
                <td>{{ $row->BranchName }}</td>
                <td>{{ $row->Total }}</td>
                <td>{{ $row->Converted }}</td>
                <td>{{ $row->Pending }}</td>
                <td>
                    {{ number_format(($row->Converted / max($row->Total, 1)) * 100, 1) }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
