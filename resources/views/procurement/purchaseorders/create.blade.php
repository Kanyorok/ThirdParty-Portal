@extends('layouts.app')
@section('title', 'Create LPO')
@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-dark text-white">📝 New Local Purchase Order</div>
        <div class="card-body">
            <form>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">LPO Number</label>
                        <input type="text" class="form-control" value="LPO/2025/110">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" value="2025-07-05">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Supplier</label>
                    <select class="form-select">
                        <option selected>OfficePro Suppliers Ltd</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Linked Contract</label>
                    <select class="form-select">
                        <option selected>CONTRACT/PROC/2025/009 - Office Furniture</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Items</label>
                    <textarea class="form-control" rows="3">Office Desks, Executive Chairs</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Delivery Terms</label>
                    <textarea class="form-control" rows="2">Delivery within 14 days to HQ Stores</textarea>
                </div>

                <div class="text-end">
                    <button class="btn btn-primary">💾 Save LPO</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
