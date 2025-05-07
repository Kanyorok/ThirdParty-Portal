@extends('layouts.app')
@section('title', 'Trip Management')
@section('content')

<div class="container mt-5">
    <h2>New Trip Entry</h2>
    <form>
        <div class="form-group">
            <label for="tripID">Trip ID</label>
            <input type="text" class="form-control" id="tripID" placeholder="Enter Trip ID" value="TRIP004">
        </div>

        <div class="form-group">
            <label for="vehicle">Vehicle</label>
            <input type="text" class="form-control" id="vehicle" placeholder="Vehicle Name and Plate" value="Mazda BT50 - KDB 567X">
        </div>

        <div class="form-group">
            <label for="driver">Driver</label>
            <input type="text" class="form-control" id="driver" placeholder="Driver's Full Name" value="Lucy Wanjiru">
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="origin">Origin</label>
                <input type="text" class="form-control" id="origin" value="Nairobi">
            </div>
            <div class="form-group col-md-6">
                <label for="destination">Destination</label>
                <input type="text" class="form-control" id="destination" value="Nakuru">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="departure">Departure Date</label>
                <input type="date" class="form-control" id="departure" value="2025-05-05">
            </div>
            <div class="form-group col-md-6">
                <label for="arrival">Arrival Date</label>
                <input type="date" class="form-control" id="arrival" value="2025-05-06">
            </div>
        </div>

        <div class="form-group">
            <label for="status">Trip Status</label>
            <select class="form-control" id="status">
                <option selected>Planned</option>
                <option>In Progress</option>
                <option>Completed</option>
                <option>Cancelled</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Save Trip</button>
    </form>
</div>
@endsection