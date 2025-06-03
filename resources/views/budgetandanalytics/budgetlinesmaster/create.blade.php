@extends('layouts.app')
@section('title', 'Add Budget Item')
@section('content')
    <div class="card p-3">
        <h5 class="mb-3">➕ Add Budget Item</h5>
        <form>
            <div class="row mb-2">
                <div class="col-md-6">
                    <label>Budget Item Code</label>
                    <input type="text" class="form-control" placeholder="e.g., BGT-001"/>
                </div>
                <div class="col-md-6">
                    <label>Budget Item Name</label>
                    <input type="text" class="form-control" placeholder="e.g., Loan Interest Income"/>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <label>Category</label>
                    <select class="form-control">
                        <option>Revenue</option>
                        <option>Expense</option>
                        <option>Capital</option>
                        <option>Interest</option>
                        <option>Commission</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Linked CBS Product?</label>
                    <select class="form-control">
                        <option>No</option>
                        <option>Yes</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Status</label>
                    <select class="form-control">
                        <option>Active</option>
                        <option>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-6">
                    <label>Mapped GL Code</label>
                    <input type="text" class="form-control" placeholder="Optional - for CBS"/>
                </div>
                <div class="col-md-6">
                    <label>Remarks</label>
                    <input type="text" class="form-control"/>
                </div>
            </div>

            <div class="text-end">
                <button class="btn btn-primary">💾 Save Budget Item</button>
            </div>
        </form>
    </div>

@endsection
