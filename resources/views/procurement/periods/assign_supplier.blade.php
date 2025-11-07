@extends('layouts.app')
@section('title', 'Assign Suppliers to Procurement Period')

@section('content')
<div class="container">
    <h4>Assign Suppliers to Procurement Period: {{ $period->StartDate }} - {{ $period->EndDate }}</h4>

    <form method="POST" action="{{ route('procurement-periods.assign-suppliers', $period->Id) }}">
        @csrf

        <div class="form-group mb-3">
            <label>Select Suppliers</label>
            <select name="SupplierIds[]" class="form-control" multiple required>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->Id }}" {{ $period->Suppliers->contains($supplier->Id) ? 'selected' : '' }}>
                        {{ $supplier->SupplierName }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Assign</button>
    </form>
</div>
@endsection
