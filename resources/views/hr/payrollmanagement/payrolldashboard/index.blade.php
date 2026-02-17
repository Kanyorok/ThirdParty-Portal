@extends('layouts.app')
@section('title', 'Payroll Dashboard')
@section('content')

<div class="container text-center mt-5">
    <h1 class="mb-4">Payroll Management System</h1>
    <p class="lead">Access employee payroll, deductions, and payslip records.</p>

    <!-- The only link to the payroll dashboard -->
    <a href="{{route('payrolldashboard.create')}}" class="btn btn-primary btn-lg mt-3">Open Payroll Dashboard</a>
</div>


@endsection