@extends('layouts.app')
@section('title', 'My Insurance Referrals')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📋 My Insurance Referrals</h4>
  <div class="mb-2 d-flex justify-content-between">
  <a href="{{ route('referrals.create') }}"  class="btn btn-success">➕ New Referral</a>
  </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Client Name</th>
                <th>Phone</th>
                <th>Product</th>
                <th>Suggested Cover</th>
                <th>Status</th>
                <th>Submitted On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            {{-- Sample Static Rows --}}
            <tr>
                <td>1</td>
                <td>Jane Njeri</td>
                <td>0722000000</td>
                <td>Loan</td>
                <td>Credit Life</td>
                <td><span class="badge bg-warning text-dark">Pending</span></td>
                <td>2025-07-08</td>
                <td>
                    <a href="#" class="btn btn-sm btn-info">View</a>
                    <a href="#" class="btn btn-sm btn-danger">Cancel</a>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>Michael Otieno</td>
                <td>0711000000</td>
                <td>Mortgage</td>
                <td>Property Cover</td>
                <td><span class="badge bg-success">Converted</span></td>
                <td>2025-06-20</td>
                <td>
                    <a href="#" class="btn btn-sm btn-info">View</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
