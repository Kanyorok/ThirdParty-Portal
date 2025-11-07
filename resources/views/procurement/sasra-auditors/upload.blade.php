@extends('layouts.app')

@section('title', 'Upload SASRA Auditor List')

@section('content')
<div class="container">
    <h3>Upload SASRA Auditor List</h3>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Please fix the following issues:<br>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('sasra-auditors.import') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group mb-3">
            <label for="file">Choose Excel or CSV file <span class="text-danger">*</span></label>
            <input type="file" name="file" class="form-control" accept=".csv, .xlsx" required>
        </div>

        <button type="submit" class="btn btn-primary">Upload SASRA List</button>
    </form>
</div>
@endsection
