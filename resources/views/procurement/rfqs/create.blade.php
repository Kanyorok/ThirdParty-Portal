@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Create RFQ</h3>

    <form method="POST" action="{{ route('rfqs.store') }}">
        @csrf

        <div class="mb-3">
            <label>Tender</label>
            <select name="TenderId" class="form-control" required>
                <option value="">-- Select Tender --</option>
                @foreach($tenders as $tender)
                    <option value="{{ $tender->Id }}">{{ $tender->TenderNumber }} - {{ $tender->TenderTitle }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Item Category</label>
            <select name="ItemCategoryId" class="form-control" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->Name }}</option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary">Send RFQ</button>
    </form>
</div>
@endsection
