@extends('layouts.app')
@section('title', 'Approval Configuration')
@section('content')
<div class="container">
    <h4>🔧 Approval Configuration</h4>

    <form method="POST" action="{{ route('approval-settings.store') }}">
        @csrf
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Document Type</label>
                <select name="DocType" class="form-control" required>
                    <option value="purchase_requisition">Purchase Requisition</option>
                    <option value="purchase_order">Purchase Order</option>
                </select>
            </div>

            <div class="col-md-6">
                <label>Approval Type</label>
                <select name="ApprovalType" class="form-control" required>
                    <option value="ANY">Any</option>
                    <option value="ALL">All</option>
                    <option value="MAJ">Majority</option>
                    <option value="AMT">Amount Based</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">💾 Save Approval Group</button>
    </form>

</div>
@endsection
