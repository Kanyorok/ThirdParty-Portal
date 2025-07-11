@extends('layouts.app')
@section('title', 'Referral Tracker')

@section('content')
<div class="container mt-4">

  <div class="mb-2 d-flex justify-content-between">
  <a href="{{ route('bancassurance.referrals.create') }}"  class="btn btn-success">➕ New Refferal</a>
  </div>  
    
<h4 class="mb-3">📊 Referral Tracker</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped" id="referralTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Client Name</th>
                <th>Product</th>
                <th>Insurer</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th>Referral Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($referrals as $i => $referral)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $referral->ClientName }}</td>
                <td>{{ $referral->ProductName ?? '-' }}</td>
                <td>{{ $referral->InsurerName ?? '-' }}</td>
                <td>
                    @if($referral->Status == 'Converted')
                        <span class="badge bg-success">Converted</span>
                    @elseif($referral->Status == 'Assigned')
                        <span class="badge bg-info">Assigned</span>
                    @else
                        <span class="badge bg-secondary">{{ $referral->Status }}</span>
                    @endif
                </td>
                <td>{{ $referral->AssignedToName ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($referral->ReferralDate)->format('d M Y') }}</td>
                <td>
                    <a href="#" class="btn btn-sm btn-outline-primary disabled">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
