@extends('layouts.app')
@section('title', 'Patent Details')
@section('content')
    <div class="container mt-5" style="max-width: 700px;">
        <h3 class="mb-4">Patent Details</h3>
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">PropertyID</dt>
                    <dd class="col-sm-8">{{ $unit->PropertyID ?? '-' }}</dd>

                    <dt class="col-sm-4">Block</dt>
                    <dd class="col-sm-8">{{ $unit->BlockID ?? '-' }}</dd>

                    <dt class="col-sm-4">Floor</dt>
                    <dd class="col-sm-8">{{ $unit->FloorID ??'-' }}</dd>

                    <dt class="col-sm-4">Unit Code</dt>
                    <dd class="col-sm-8">{{ $unit->UnitCode?? '-' }}</dd>

                    <dt class="col-sm-4">Size</dt>
                    <dd class="col-sm-8">{{ $unit->UnitSize ?? '-' }}</dd>

                    <dt class="col-sm-4">current status</dt>
                    <dd class="col-sm-8">{{ $unit->CurrentStatus ?? '-' }}</dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="#" class="btn btn-primary">Edit</a>
                <a href="#" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
