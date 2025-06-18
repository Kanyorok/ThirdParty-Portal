@extends('layouts.app')
@section('title', 'Edit Stock Take')
@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    <h1>Edit Item Stock Take</h1>
    <form action="{{ route('stocktake.update', $stock->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
        <div class="col-md-3">
            <label class="form-label">📍 Branch</label>
                <select name="BranchId" class="form-select" required>
                    <option value="">-- Select Branch --</option>
                    @foreach ($items as $item)
                                            <option value="{{ $item->Id }}" {{ $stock->BranchId == $item->Id ? 'selected' : '' }}>
                            {{ $item->Branch }}
                        </option>
                    @endforeach
                </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">🏢 Store</label>
                <select name="StoreId" class="form-select" required>
                    <option value="">-- Select Store --</option>
                    @foreach ($items as $item)
                                          <option value="{{ $item->Id }}" {{ $stock->StoreId == $item->Id ? 'selected' : '' }}>
                            {{ $item->Store }}
                        </option>
                    @endforeach
                </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">🧑‍💼 Counted By</label>
            <input type="text" class="form-control" id="countedBy" placeholder="Enter name"name="CountedBy">
        </div>
        <div class="col-md-3">
            <label class="form-label">📅 Count Date</label>
            <input type="date" class="form-control" id="countedDate" value="2025-05-02"name="CountDate">
        </div>
        </div>

        <button type="submit" class="btn btn-success">Update Stock Take</button>
        <a href="{{ route('stocktake.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection
