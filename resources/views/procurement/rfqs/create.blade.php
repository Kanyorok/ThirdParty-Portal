@extends('layouts.app')
@section('title', 'Create RFQ')
@section('content')

<div class="container">
    <h3>Create RFQ</h3>

    <form method="POST" action="{{ route('rfqs.store') }}">
        @csrf

        <div class="mb-3">
            <label>Tender</label>
            <select name="TenderId" class="form-control" required {{ $tenders->isEmpty() ? 'disabled' : '' }}>
                <option value="">-- Select Tender --</option>
                @foreach($tenders as $tender)
                    <option value="{{ $tender->Id }}">{{ $tender->TenderNumber }} - {{ $tender->Title }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Item Category</label>
            <select name="ItemCategoryId" class="form-control" required {{ $tenders->isEmpty() ? 'disabled' : '' }}>
                <option value="">-- Select Category --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->Name }}</option>
                @endforeach
            </select>
        </div>

        @if($tenders->isEmpty())
            <p class="text-danger">All tenders have already been added to RFQs.</p>
        @endif

        <button class="btn btn-primary" {{ $tenders->isEmpty() ? 'disabled' : '' }}>Send RFQ</button>
    </form>
</div>
@endsection