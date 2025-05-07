@extends('layouts.app')
@section('title', 'Fuel Management')
@section('content')

<div class="container mt-5">
    <h2>Service Tracking - New Entry</h2>
    <form>
        <div class="form-group">
            <label for="vehicle">Vehicle</label>
            <input type="text" class="form-control" id="vehicle" value="Toyota Hilux - KDA 123A">
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="serviceDate">Service Date</label>
                <input type="date" class="form-control" id="serviceDate" value="2025-05-05">
            </div>
            <div class="form-group col-md-6">
                <label for="nextService">Next Service Due</label>
                <input type="date" class="form-control" id="nextService" value="2025-08-05">
            </div>
        </div>

        <div class="form-group">
            <label for="serviceType">Service Type</label>
            <select class="form-control" id="serviceType">
                <option selected>Routine Maintenance</option>
                <option>Engine Repair</option>
                <option>Brake Service</option>
                <option>Tyre Replacement</option>
                <option>Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="provider">Service Provider</label>
            <input type="text" class="form-control" id="provider" value="AutoX Garage - Nairobi">
        </div>

        <div class="form-group">
            <label for="cost">Cost (Ksh)</label>
            <input type="number" class="form-control" id="cost" value="12500">
        </div>

        <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea class="form-control" id="remarks" rows="2">Changed oil, filters, and brake pads</textarea>
        </div>

        <button type="submit" class="btn btn-success">Save Service Entry</button>
    </form>
</div>
@endsection