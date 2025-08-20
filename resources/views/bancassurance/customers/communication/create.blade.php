@extends('layouts.app')
@section('title', 'Log Communication')

@section('content')
<div class="container mt-4">
    <form method="POST" action="{{ route('bancassurance.customers.communication.store',) }}">
        @csrf
        <div class="col-md-4">
            <label class="form-label">CustomerID <span class="text-danger">*</span></label>
            <select name="CustomerID" class="form-select" required>
            <option value="">-- Select CustomerID --</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->Id }}">{{ $customer->FullName }}</option>
                @endforeach
            </select>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Contact Date<span class="text-danger">*</span></label>
                <input type="datetime-local" name="ContactDate" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Contact Type<span class="text-danger">*</span></label>
                <select name="ContactType" class="form-select" required>
                 <option value="">--Select Contact Type--</option>
                    @foreach ($contacttypes as $contacttype)
                    <option value="{{ $contacttype->ID }}">
                    {{ $contacttype->Description }}
                 </option>
                  @endforeach
            </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Handled By<span class="text-danger">*</span></label>
                <select name="HandledBy" class="form-select">
                <option value="">-- Select Handled By --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->Id }}">{{ $emp->FirstName }} {{ $emp->LastName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Summary<span class="text-danger">*</span></label>
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
