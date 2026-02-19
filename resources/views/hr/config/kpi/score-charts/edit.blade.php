@extends('layouts.app')

@section('title', 'Edit Score Chart')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Score Chart Row</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.config.kpi.scorecharts.index') }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('hr.config.kpi.scorecharts.update', $chart->Id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Rating Scale</label>
                        <select name="RatingScaleID" class="form-select">
                            <option value="">All Scales</option>
                            @foreach($scales as $scale)
                                <option value="{{ $scale->Id }}" @selected(old('RatingScaleID', $chart->RatingScaleID) == $scale->Id)>
                                    {{ $scale->Name }} ({{ $scale->MinScore }} - {{ $scale->MaxScore }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Min % *</label>
                        <input type="number" step="0.01" name="MinPercent" class="form-control" value="{{ old('MinPercent', $chart->MinPercent) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Max %</label>
                        <input type="number" step="0.01" name="MaxPercent" class="form-control" value="{{ old('MaxPercent', $chart->MaxPercent) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rating *</label>
                        <input type="number" step="0.01" name="RatingValue" class="form-control" value="{{ old('RatingValue', $chart->RatingValue) }}" required>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Label *</label>
                        <input type="text" name="RatingLabel" class="form-control" value="{{ old('RatingLabel', $chart->RatingLabel) }}" required>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsActive" value="1" id="IsActive" @checked(old('IsActive', $chart->IsActive))>
                            <label class="form-check-label" for="IsActive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
