@extends('layouts.app')
@section('title', 'Create RFQ')
@section('content')

<div class="container">
    <h3>Create RFQ</h3>

    <div class="container mt-3">
        <h3>RFQ Details</h3>
        <form method="POST" action="{{ route('rfqs.store') }}">
            @csrf

        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <label>Item Category</label>
                    <select name="ItemCategoryId" class="form-control" required>
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label for="Comments">Comments<span class="text-danger">*</span></label>
                    <textarea name="Comments" class="form-control" required></textarea>
                </div>
                <div class="form-group mb-3">
                    <label for="SubmissionDeadline">Submission Deadline <span class="text-danger">*</span></label>
                    <input type="date" name="SubmissionDeadline" class="form-control" required min="{{ date('Y-m-d') }}">
                </div> 
            </div>
        </div>
        <button class="btn btn-success" type="submit">Save</button>
        </form>
    </div>
</div>
@endsection