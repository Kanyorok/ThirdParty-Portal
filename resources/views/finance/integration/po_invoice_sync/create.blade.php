@extends('layouts.app')
@section('title', 'Create PO to Invoice Sync Rule')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">🛠️ Create Sync Rule</h4>

        <form method="POST" action="#">
            <div class="mb-3">
                <label for="po_type" class="form-label">PO Type</label>
                <select name="po_type" id="po_type" class="form-select" required>
                    <option value="">-- Select PO Type --</option>
                    <option value="goods">Goods Purchase</option>
                    <option value="services">Service PO</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="gl_account" class="form-label">Default GL Account</label>
                <select name="gl_account" id="gl_account" class="form-select" required>
                    <option value="">-- Select GL --</option>
                    <option value="5000">5000 - Procurement Payable</option>
                    <option value="5010">5010 - Services Payable</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Auto Sync Enabled?</label>
                <select name="auto_sync" class="form-select" required>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">💾 Save Rule</button>
            <a href="{{ route('po-invoice-sync.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
