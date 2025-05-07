@extends('layouts.app')
@section('title', 'Vehicle Registry')
@section('content')


<div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Create Vehicle Entry</h4>
            </div>
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label for="regNumber" class="form-label">Registration Number</label>
                        <input type="text" class="form-control" id="regNumber" placeholder="KDA 123A">
                    </div>
                    <div class="mb-3">
                        <label for="ownerName" class="form-label">Owner Name</label>
                        <input type="text" class="form-control" id="ownerName" placeholder="John Doe">
                    </div>
                    <div class="mb-3">
                        <label for="make" class="form-label">Vehicle Make</label>
                        <input type="text" class="form-control" id="make" placeholder="Toyota">
                    </div>
                    <div class="mb-3">
                        <label for="model" class="form-label">Vehicle Model</label>
                        <input type="text" class="form-control" id="model" placeholder="Corolla">
                    </div>
                    <div class="mb-3">
                        <label for="year" class="form-label">Year of Manufacture</label>
                        <input type="number" class="form-control" id="year" placeholder="2021">
                    </div>
                    <button type="submit" class="btn btn-success">Save Vehicle</button>
                </form>
            </div>
        </div>
    </div>

@endsection