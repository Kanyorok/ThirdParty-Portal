@extends('layouts.app')
@section('title', 'Payroll Index')
@section('content')

<div class="container mt-5 text-center">
    <h2 class="mb-4">Welcome to Payroll Management</h2>

    <a href="{{ route('generatepayslip.create') }}" class="btn btn-success btn-lg">
        Download Payslip
    </a>
</div>

@endsection
