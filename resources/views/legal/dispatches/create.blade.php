@extends('layouts.app')
@section('title', 'Add Dispatch Entry')
@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">📤 New Dispatch Entry</h4>
        <form method="POST" action="{{ route('legal.documents.dispatches.store', $document->ID) }}">
            @csrf
            <div class="mb-3">
                <label>Dispatch Date</label>
                <input type="datetime-local" name="DispatchDate" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Dispatched To</label>
                <input type="text" name="DispatchedTo" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Dispatch Method</label>
                <select name="DispatchMethod" class="form-control">
                    <option>Email</option>
                    <option>Courier</option>
                    <option>Hand Delivery</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Status</label>
                <select name="Status" class="form-control">
                    <option>Pending</option>
                    <option>Delivered</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Remarks</label>
                <textarea name="Remarks" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Save Dispatch</button>
        </form>
    </div>
@endsection
