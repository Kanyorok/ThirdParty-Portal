@extends('layouts.app')
@section('title', 'Add Clause')

@section('content')
<div class="card p-1 shadow rounded-4 mb-0">

    <div class="card-body px-4 px-4 mb-0">
        <p class="text-muted">Provide the clause details below to add it to the legal clauses registry.</p>
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <form action="{{ route('legal.clauses.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input type="text" name="Title" class="form-control" placeholder="e.g. Confidentiality Clause" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Clause Type</label>
                    <select class="form-select" name="ClauseType" id="ClauseType" required>
                        <option selected disabled value="">-- Select Clause Type --</option>
                            @foreach( $details as $item)
                                <option value="{{ $item->Value}}">{{ $item->Value}}</option>
                            @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Clause Content</label>
                <textarea name="Content" rows="4" class="form-control" required></textarea>
            </div>

            {{-- <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="IsStandard" id="IsStandard" checked>
                <label class="form-check-label" for="IsStandard">Mark as Standard Clause</label>
            </div> --}}
            <div class="d-flex justify-content-end gap-2 text-end mb-3">
                <a href="{{ route('legal.clauses.index') }}" class="btn btn-outline-secondary"><i class="fas fa-long-arrow-alt-left"></i> Back</a>
                <button class="btn btn-info" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText=' Saving...'; this.form.submit();}"><i class="fas fa-save"></i> Save Clause</button>
            </div>
        </form>
</div>
@endsection
