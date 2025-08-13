@extends('layouts.app')
@section('title', 'New Insurance Referral')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">📋 New Insurance Referral</h4>

    <form method="POST" action="{{ route('bancassurance.referrals.store') }}">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Client Name</label>
                <input type="text" name="ClientName" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">ID Number</label>
                <input type="text" name="ClientIDNumber" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Phone</label>
                <input type="text" name="ClientPhone" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="ClientEmail" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Insurance Product</label>
                <select name="ProductID" class="form-select" required>
                    <option value="">-- Select Product --</option>
                    <option value="1">Life Insurance</option>
                    <option value="2">Medical Cover</option>
                    <option value="3">Motor Insurance</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Preferred Insurer</label>
                <select name="PreferredInsurerID" class="form-select">
                    <option value="">-- Select Insurer --</option>
                    <option value="1">Jubilee</option>
                    <option value="2">Britam</option>
                    <option value="3">CIC Insurance</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Remarks</label>
                <input type="text" name="Remarks" class="form-control">
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Submit Referral
            </button>
        </div>
    </form>
</div>
@endsection
