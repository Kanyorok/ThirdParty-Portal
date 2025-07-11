@extends('layouts.app')
@section('title', 'Log Communication')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">📞 Log Communication for: {{ $customer->FullName }}</h4>

    <form method="POST" action="{{ route('bancassurance.customers.communication.store', $customer->Id) }}">
        @csrf

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Contact Date</label>
                <input type="datetime-local" name="ContactDate" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Contact Type</label>
                <select name="ContactType" class="form-select" required>
                    <option value="Call">📞 Call</option>
                    <option value="Email">📧 Email</option>
                    <option value="SMS">📲 SMS</option>
                    <option value="Visit">🏢 Visit</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Handled By</label>
                <select name="HandledBy" class="form-select">
                    @foreach($employees as $emp)
                        <option value="{{ $emp->Id }}">{{ $emp->FirstName }} {{ $emp->LastName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Summary</label>
            <input type="text" name="Summary" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Detailed Notes</label>
            <textarea name="Notes" class="form-control" rows="4" placeholder="Additional remarks..."></textarea>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">
                💾 Save Log
            </button>
        </div>
    </form>
</div>
@endsection
