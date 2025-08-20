@extends('layouts.app')
@section('title', 'Policy Master List')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📘 Policy Master Register</h4>
  <div class="mb-2 d-flex justify-content-between">
  <a href="{{ route('policy-proposals.create') }}"  class="btn btn-success">➕ New Policy Proposal</a>
  </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Policy No</th>
                <th>Client Name</th>
                <th>Product</th>
                <th>Sum Assured</th>
                <th>Premium</th>
                <th>Status</th>
                <th>Effective</th>
                <th>Expiry</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            {{-- Sample static rows --}}
            <tr>
                <td>1</td>
                <td>POL-202507001</td>
                <td>Jane Njeri</td>
                <td>Credit Life</td>
                <td>KES 500,000</td>
                <td>KES 5,000</td>
                <td><span class="badge bg-success">Active</span></td>
                <td>2025-07-10</td>
                <td>2026-07-10</td>
                <td>
                    <a href="#" class="btn btn-sm btn-info">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Endorse</a>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>PROP-202507002</td>
                <td>Michael Otieno</td>
                <td>Fire Cover</td>
                <td>KES 2,000,000</td>
                <td>KES 25,000</td>
                <td><span class="badge bg-secondary">Draft</span></td>
                <td>–</td>
                <td>–</td>
                <td>
                    <a href="#" class="btn btn-sm btn-primary">Edit Proposal</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
