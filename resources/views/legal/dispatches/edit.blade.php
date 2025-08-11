@extends('layouts.app')
@section('title', 'Edit Dispatch Entry')
@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">✏️ Edit Dispatch Entry</h4>
    <form method="POST" action="{{ route('legal.documents.dispatches.update', [$document->ID, $dispatch->ID]) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Dispatch Date</label>
            <input type="datetime-local" name="DispatchDate" class="form-control"
                   value="{{ \Carbon\Carbon::parse($dispatch->DispatchDate)->format('Y-m-d\\TH:i') }}" required>
        </div>

        <div class="mb-3">
            <label>Dispatched To</label>
            <input type="text" name="DispatchedTo" class="form-control" value="{{ $dispatch->DispatchedTo }}" required>
        </div>

        <div class="mb-3">
            <label>Dispatch Method</label>
            <select name="DispatchMethod" class="form-control">
                <option {{ $dispatch->DispatchMethod == 'Email' ? 'selected' : '' }}>Email</option>
                <option {{ $dispatch->DispatchMethod == 'Courier' ? 'selected' : '' }}>Courier</option>
                <option {{ $dispatch->DispatchMethod == 'Hand Delivery' ? 'selected' : '' }}>Hand Delivery</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="Status" class="form-control">
                <option {{ $dispatch->Status == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option {{ $dispatch->Status == 'Delivered' ? 'selected' : '' }}>Delivered</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Remarks</label>
            <textarea name="Remarks" class="form-control">{{ $dispatch->Remarks }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Dispatch</button>
    </form>
</div>
@endsection
