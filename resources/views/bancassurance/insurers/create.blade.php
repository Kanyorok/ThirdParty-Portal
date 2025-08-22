@extends('layouts.app')
@section('title', 'Register Insurance Provider')
@section('content')
    <div class="container mt-4">
        <h4>🏢 Register Insurance Provider</h4>

        <form method="POST" action="{{ route('bancassurance.insurers.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input name="Name" type="text" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Country</label>
                <input name="Country" type="text" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Contact Person</label>
                <input name="ContactPerson" type="text" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input name="Email" type="email" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input name="Phone" type="text" class="form-control">
            </div>

            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="primaryCheck">
                <label class="form-check-label" for="primaryCheck">IsActive </label>
            </div>

            <button type="submit" class="btn btn-primary">Save Provider</button>
        </form>
    </div>
@endsection

