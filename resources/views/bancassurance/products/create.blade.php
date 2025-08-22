@extends('layouts.app')
@section('title', 'Add Insurance Product')

@section('content')
<div class="container mt-4">
    <form method="POST" action="{{ route('bancassurance.products.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Insurance Provider <span class="text-danger">*</span></label>
            <select name="InsuranceProviderID" class="form-select" required>
            <option value="">--Select a status--</option>
              @foreach ($providers as $provider)
                <option value="{{ $provider->Id }}">
                  {{ $provider->Name }}
                </option>
              @endforeach
            </select>
          </div>   
          
      <div class="mb-3">
            <label class="form-label"> Name <span class="text-danger">*</span></label>
            <input type="text" name="Name" class="form-control" required maxlength="150">
        </div>

          <div class="mb-3">
            <label class="form-label">Type <span class="text-danger">*</span></label>
            <input type="text" name="Type" class="form-control" required maxlength="150">
        </div>

        <div class="mb-3">
            <label class="form-label">Description <span class="text-danger">*</span></label>
            <textarea name="Description" class="form-control" rows="3"></textarea>
        </div>

            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="primaryCheck">
                <label class="form-check-label" for="primaryCheck">IsActive </label>
            </div>

        <button type="submit" class="btn btn-primary">Save Product</button>
    </form>
</div>
@endsection
