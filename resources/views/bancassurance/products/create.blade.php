@extends('layouts.app')
@section('title', 'Add Insurance Product')

@section('content')
    <div class="container mt-4">
        <h4>➕ Add Insurance Product</h4>

        <form method="POST" action="{{ route('bancassurance.products.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Insurance Provider</label>
                <select name="InsuranceProviderID" class="form-select" required>
                    <option value="">--Select a status--</option>
                    @foreach ($providers as $provider)
                        <option value="{{ $provider->Id }}">
                            {{ $provider->InsuranceProviderNO }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label"> Name</label>
                <input type="text" name="Name" class="form-control" required maxlength="150">
            </div>

            <div class="mb-3">
                <label class="form-label">Type </label>
                <input type="text" name="Type" class="form-control" required maxlength="150">
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="Description" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="primaryCheck">
                <label class="form-check-label" for="primaryCheck">IsActive </label>
            </div>

            <button type="submit" class="btn btn-primary">💾 Save Product</button>
        </form>
    </div>
@endsection
