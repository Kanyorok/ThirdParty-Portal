@extends('layouts.app')
@section('title', 'Register Intellectual Property')

@section('content')
<div class="card shadow p-1 rounded-4">
    <div class="card-body">
        <p class="text-muted">
            Use this form to register new Intellectual Property (IP) details such as trademarks, patents, or copyrights.
            Fill in all relevant information to ensure proper legal documentation and tracking.
        </p>

        <form method="POST" action="{{ route('legal.intellectual.store') }}">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">IP Type</label>
                    <select class="form-select" name="IPType" id="IPType" required>
                        <option selected disabled value="">-- Select IP Type --</option>
                            @foreach($details as $item)
                                <option value="{{ $item->Value }}">{{ $item->Value }}</option>
                            @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input type="text" name="Title" class="form-control" placeholder="Enter the IP title" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Owner</label>
                    <input type="text" name="Owner" class="form-control" placeholder="Name of the owner or organization"
                           required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Registration Number</label>
                    <input type="text" name="RegistrationNumber" class="form-control"
                           placeholder="Unique registration number" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Registration Date</label>
                    <input type="date" name="RegistrationDate" class="form-control"
                           placeholder="Select registration date" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" name="ExpiryDate" class="form-control" placeholder="Select expiry date" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <textarea name="Remarks" class="form-control" rows="3" placeholder="Additional notes or details"
                          required></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-3">
                <a href="{{ route('legal.intellectual.index') }}" class="btn btn-outline-secondary"><i class="fas fa-long-arrow-alt-left"></i> Back</a>
                <button type="submit" class="btn btn-info" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}"><i class="fas fa-save"></i> Save IP</button>
            </div>
        </form>
    </div>
</div>
@endsection
