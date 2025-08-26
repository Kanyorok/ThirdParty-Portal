@extends('layouts.app')
@section('title', 'Policy Renewal')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">🔄 Renew Policy</h4>

        <form method="POST" action="#">
            @csrf

            <!-- Select Expiring/Expired Policy -->
            <div class="mb-3">
                <label class="form-label">Select Policy</label>
                <select name="PolicyID" class="form-select" required>
                    <option value="">-- Select Policy --</option>
                    <option value="1">POL-202407001 – Jane Njeri</option>
                    <option value="2">POL-202407002 – Michael Otieno</option>
                </select>
            </div>

            <!-- Renewal Period -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">New Start Date</label>
                    <input type="date" name="NewStartDate" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">New End Date</label>
                    <input type="date" name="NewEndDate" class="form-control" required>
                </div>
            </div>

            <!-- Sum Assured / Premium (Optional Edits) -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sum Assured</label>
                    <input type="number" name="NewSumAssured" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Premium</label>
                    <input type="number" name="NewPremium" class="form-control">
                </div>
            </div>

            <!-- Renewal Notes -->
            <div class="mb-3">
                <label class="form-label">Renewal Comments</label>
                <textarea name="Notes" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-success">Submit Renewal</button>
        </form>
    </div>
@endsection
