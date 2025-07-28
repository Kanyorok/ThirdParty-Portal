@extends('layouts.app')
@section('title', 'New Customer Profile')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">👤 New Customer Profile & KYC</h4>

    <form method="POST" action="{{ route('bancassurance.customers.store') }}">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="FullName" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">National ID</label>
                <input type="text" name="NationalID" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">KRA PIN</label>
                <input type="text" name="KRA_PIN" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="DateOfBirth" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Gender</label>
                <select name="Gender" class="form-select">
                    <option value="">-- Select --</option>
                    <option value="m">Male</option>
                    <option value="f">Female</option>
                    <option value="o">Other</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Marital Status</label>
                <select name="MaritalStatus" class="form-select">
                    <option value="">-- Select --</option>
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                    <option value="Divorced">Divorced</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Occupation</label>
                <input type="text" name="Occupation" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input type="text" name="Phone" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="Email" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Address</label>
                <input type="text" name="Address" class="form-control">
            </div>
        </div>

        <div class="text-end">
            <button class="btn btn-success" type="submit">
                <i class="fas fa-save"></i> Save Profile
            </button>
        </div>
    </form>
</div>
@endsection
