@extends('layouts.app')
@section('title', 'Assign Referrals')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">🧭 Assign Referrals</h4>

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
            @foreach($referrals as $i => $referral)
            <tr>
                <form method="POST" action="{{ route('bancassurance.referrals.assign', $referral->Id) }}">
                    @csrf
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $referral->ClientName }}</td>
                    <td>{{ $referral->ProductName }}</td>
                    <td>{{ \Carbon\Carbon::parse($referral->ReferralDate)->format('d M Y') }}</td>
                    <td><span class="badge bg-secondary">{{ $referral->Status }}</span></td>
                    <td>
                        <select name="AssignedTo" class="form-select" required>
                            <option value="">-- Select Officer --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->Id }}">{{ $emp->FirstName }} {{ $emp->LastName }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <button type="submit" class="btn btn-sm btn-success">
                            Assign
                        </button>
                    </td>
                </form>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
