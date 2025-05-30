@extends('layouts.app')
@section('title', 'Add Unit of Measure (UOM)')
@section('content')
    <div class="container mt-4">
        <h4>Add Unit of Measure (UOM)</h4>
        <form>
            <div class="mb-3">
                <label for="uomCode" class="form-label">UOM Code</label>
                <input type="text" class="form-control" id="uomCode" placeholder="e.g., PCS">
            </div>
            <div class="mb-3">
                <label for="uomName" class="form-label">UOM Name</label>
                <input type="text" class="form-control" id="uomName" placeholder="e.g., Pieces">
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="isBaseUnit" checked>
                <label class="form-check-label" for="isBaseUnit">Base Unit?</label>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="uomActive" checked>
                <label class="form-check-label" for="uomActive">Active</label>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>

@endsection
