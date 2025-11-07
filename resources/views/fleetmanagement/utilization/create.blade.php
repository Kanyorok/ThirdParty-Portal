@extends('layouts.app')
@section('title', 'Fleet Utilization & Costing')
@section('content')

<div class="container mt-5">
    <h2>Fuel Entry Form</h2>
    <form action="submit_fuel.php" method="post">
        <div class="form-group mb-3">
            <label for="vehicle">Vehicle</label>
            <input type="text" class="form-control" id="vehicle" name="vehicle" value="Toyota Hilux - KDA 123A">
        </div>

        <div class="form-group mb-3">
            <label for="driver">Driver</label>
            <input type="text" class="form-control" id="driver" name="driver" value="John Doe">
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="fuelDate">Fuel Date</label>
                <input type="date" class="form-control" id="fuelDate" name="fuel_date" value="2025-05-05">
            </div>
            <div class="col-md-4">
                <label for="fuelType">Fuel Type</label>
                <select class="form-control" id="fuelType" name="fuel_type">
                    <option selected>Diesel</option>
                    <option>Petrol</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="fuelStation">Fuel Station</label>
                <input type="text" class="form-control" id="fuelStation" name="fuel_station" value="Total - Westlands">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="quantity">Quantity (Litres)</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="40">
            </div>
            <div class="col-md-4">
                <label for="unitPrice">Price per Litre</label>
                <input type="number" class="form-control" id="unitPrice" name="unit_price" value="185">
            </div>
            <div class="col-md-4">
                <label for="totalCost">Total Cost (Ksh)</label>
                <input type="number" class="form-control" id="totalCost" name="total_cost" value="7400">
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="remarks">Remarks</label>
            <textarea class="form-control" id="remarks" name="remarks" rows="2">Fuelled before trip to Mombasa</textarea>
        </div>

        <button type="submit" class="btn btn-success">Save Fuel Entry</button>
    </form>
</div>
 @endesection