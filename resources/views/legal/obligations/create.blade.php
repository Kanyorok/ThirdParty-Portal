@extends('layouts.app')
@section('title', 'New Legal Obligation')

@section('content')
<div class="card p-0 shadow rounded-4 border-0">

    <div class="card-body">
        <p class="text-muted">Record a new legal obligation, including its details, deadlines, and responsible parties, to ensure proper monitoring and compliance.</p>
         @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        <form action="{{ route('legal.obligations.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="Title" class="form-label">Title</label>
                    <input type="text" name="Title" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label for="SourceType" class="form-label">Source Type</label>
                    <select name="SourceType" class="form-select">
                        <option selected disabled value="">-- Select Source Type --</option>
                            @foreach($details as $item)
                                <option value="{{$item->Value}}">{{$item->Value}}</option>
                            @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="DueDate" class="form-label">Due Date</label>
                <input type="date" name="DueDate" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="Description" class="form-label">Description</label>
                <textarea name="Description" class="form-control" rows="3"></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-3">
                <a href="{{ route('legal.obligations.index') }}" class="btn btn-outline-secondary"><i class="fas fa-long-arrow-alt-left"></i> Back</a>
                <button type="submit" class="btn btn-info" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}"><i class="fas fa-save"></i> Save Obligation</button>
            </div>
        </form>
    </div>
</div>
@endsection
