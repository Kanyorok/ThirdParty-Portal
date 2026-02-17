@extends('layouts.app')
@section('title', 'Payroll Settings')
@section('content')

<div class="container mt-5">
        <h1 class="mb-4">Payroll Management System</h1>
        <div class="list-group">
            <a href="{{route('payrollsettings.create')}}" class="list-group-item list-group-item-action">Payroll Settings</a>
        </div>
    </div>

@endsection