@extends('layouts.app')
@section('title', 'Policy Endorsement')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">✏️ Endorse Policy</h4>

    <form method="POST" action="#">
        @csrf

        <!-- Select Active Policy -->
        <div class="mb-3">
            <label class="form-label">Select Policy</label>
            <select name="PolicyID" class="form-select" required>
                <option value="">-- Choose Policy --</option>
                <option value="1">POL-202507001 – Jane Njeri</option>
                <option value="2">POL-202507002 – John Mwangi</option>
            </select>
        </div>

        <!-- Endorsement Type -->
        <div class="mb-3">
            <label class="form-label">Type of Endorsement</label>
            <select name="Type" class="form-select" required>
                <option value="Sum Assured Update">Sum Assured Update</option>
                <option value="Premium Adjustment">Premium Adjustment</option>
                <option value="Beneficiary Update">Beneficiary Update</option>
                <option value="Term Extension">Term Extension</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <!-- Affected Fields -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Old Sum Assured</label>
                <input type="number" name="OldSumAssured" class="form-control" readonly value="500000">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">New Sum Assured</label>
                <input type="number" name="NewSumAssured" class="form-control">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Old Premium</label>
                <input type="number" name="OldPremium" class="form-control" readonly value="5000">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">New Premium</label>
                <input type="number" name="NewPremium" class="form-control">
            </div>
        </div>

        <!-- Notes -->
        <div class="mb-3">
            <label class="form-label">Justification / Notes</label>
            <textarea name="Reason" class="form-control" rows="3" required></textarea>
        </div>

        <button type="submit" class="btn btn-success">Submit Endorsement</button>
    </form>
</div>
@endsection
