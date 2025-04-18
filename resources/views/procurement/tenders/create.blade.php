@extends('layouts.app')
@section('title','Create Tender')
@section('content')
<div class="container">
    <h3>Create New Tender</h3>

    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Whoops!</strong> Please fix the following issues:<br>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('tendering-process.store') }}">
        @csrf

        <div class="form-group mb-3">
            <label class="form-label" for="Title">Title <span class="text-danger">*</span></label>
            <input type="text" name="Title" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label for="Description">Tender Requirements <span class="text-danger">*</span></label>
            <textarea name="Description" class="form-control"></textarea>
        </div>

        <div class="form-group mb-3">
            <label for="ProcurementModeId">Procurement Mode <span class="text-danger">*</span></label>
            <select name="ProcurementModeId" class="form-control" required>
                <option value="">Select Mode</option>
                @foreach($procurementModes as $mode)
                <option value="{{ $mode->id }}">{{ $mode->Name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="EstimatedValue">Estimated Value <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="EstimatedValue" class="form-control">
        </div>

        <div class="form-group mb-3">
            <label for="Currency">Currency <span class="text-danger">*</span></label>
            <select name="Currency" id="Currency" class="form-control" required>
                <option value="">-- Select Currency --</option>
                @foreach($currencies as $currency => $name)
                    <option value="{{ $currency }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="StartDate">Start Date <span class="text-danger">*</span></label>
            <input type="date" name="StartDate" class="form-control" required min="{{ date('Y-m-d') }}">
        </div>

        <button type="submit" class="btn btn-primary mt-3">Create Tender</button>
    </form>
</div>
@endsection