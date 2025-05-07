@extends('layouts.app')
@section('title', 'Licensing')
@section('content')
<div class="container mt-5">
    <h2>Add Insurance & Licensing Record</h2>

    <form>
        <div class="form-group">
            <label for="vehicle">Vehicle</label>
            <input type="text" class="form-control" id="vehicle" value="Toyota Hilux - KDA 123A">
        </div>

        <div class="form-group">
            <label for="insuranceProvider">Insurance Provider</label>
            <input type="text" class="form-control" id="insuranceProvider" value="Jubilee Insurance">
        </div>

        <div class="form-group">
            <label for="policyNumber">Policy Number</label>
            <input type="text" class="form-control" id="policyNumber" value="INS-2025001">
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="insuranceExpiry">Insurance Expiry Date</label>
                <input type="date" class="form-control" id="insuranceExpiry" value="2025-12-15">
            </div>
            <div class="form-group col-md-6">
                <label for="licenseExpiry">License Expiry Date</label>
                <input type="date" class="form-control" id="licenseExpiry" value="2025-08-30">
            </div>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control" id="status">
                <option selected>Active</option>
                <option>Expiring Soon</option>
                <option>Expired</option>
            </select>
        </div>

        <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea class="form-control" id="remarks" rows="3">Renew before August</textarea>
        </div>

        <button type="submit" class="btn btn-success">Save Record</button>
        <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>
    </form>
</div>
@endsection
