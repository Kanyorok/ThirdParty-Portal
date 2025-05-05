@extends('layouts.app')
@section('title', 'Fuel Management')
@section('content')
<div class="container mt-5">
    <h2>Fuel Entry Form</h2>
    <form>
        <div class="form-group">
            <label for="vehicle">Vehicle</label>
            <input type="text" class="form-control" id="vehicle" value="Toyota Hilux - KDA 123A">
        </div>

        <div class="form-group">
            <label for="driver">Driver</label>
            <input type="text" class="form-control" id="driver" value="John Doe">
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="fuelDate">Fuel Date</label>
                <input type="date" class="form-control" id="fuelDate" value="2025-05-05">
            </div>
            <div class="form-group col-md-4">
                <label for="fuelType">Fuel Type</label>
                <select class="form-control" id="fuelType">
                    <option selected>Diesel</option>
                    <option>Petrol</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="fuelStation">Fuel Station</label>
                <input type="text" class="form-control" id="fuelStation" value="Total - Westlands">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="quantity">Quantity (Litres)</label>
                <input type="number" class="form-control" id="quantity" value="40">
            </div>
            <div class="form-group col-md-4">
                <label for="unitPrice">Price per Litre</label>
                <input type="number" class="form-control" id="unitPrice" value="185">
            </div>
            <div class="form-group col-md-4">
                <label for="totalCost">Total Cost (Ksh)</label>
                <input type="number" class="form-control" id="totalCost" value="7400">
            </div>
        </div>

        <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea class="form-control" id="remarks" rows="2">Fuelled before trip to Mombasa</textarea>
        </div>

        <button type="submit" class="btn btn-success">Save Fuel Entry</button>
    </form>
</div>

@endsection