@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Add Tender Type</h4>
    <form>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="tenderTypeName" class="form-label">Tender Type</label>
                <input type="text" class="form-control" id="tenderTypeName" placeholder="e.g., Open, Restricted, RFP">
            </div>
            <div class="col-md-6 mb-3">
                <label for="typeCode" class="form-label">Type Code</label>
                <input type="text" class="form-control" id="typeCode" placeholder="e.g., TYP-001">
            </div>
        </div>

        <div class="mb-3">
            <label for="typeDescription" class="form-label">Description</label>
            <textarea class="form-control" id="typeDescription" rows="3" placeholder="Describe how this tender type works..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save Type</button>
        <button type="reset" class="btn btn-secondary">Cancel</button>
    </form>
</div>
@endsection