@extends('layouts.app')
@section('title', 'Create New Inventory')
@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <body class="bg-light">

    <div class="container mt-5">
        <div class="card shadow rounded-4">
            <div class="card-header text-dark rounded-top-4" style="background-color: #add8e6;">
                <h4 class="mb-0">Create Store</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('stores.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="StoreName" class="form-label">Store Name</label>
                            <input type="text" name="StoreName" class="form-control" id="StoreName"
                                   value="{{ old('StoreName') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="BranchID" class="form-label">Branch</label>
                            <select name="BranchID" id="BranchID" class="form-select" required>
                                <option value="">-- Select Branch --</option>
                                @foreach($branches as $branch)
                                    <option
                                        value="{{ $branch->Id }}" {{ old('BranchID') == $branch->Id ? 'selected' : '' }}>
                                        {{ $branch->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input type="hidden" name="Status" value="0">
                                <input class="form-check-input" type="checkbox" name="Status" value="1"
                                       id="Status" {{ old('Status', 1) ? 'checked' : '' }}>
                                <label class="form-check-label" for="Status">Is Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Create Store</button>
                        <a href="{{ route('stores.index') }}" class="btn btn-danger px-4 ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
