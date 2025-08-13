@extends('layouts.app')
@section('title', 'Process Payment')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">💳 Process Payment</h4>
    <form>
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Voucher</label>
                <select class="form-control">
                    <option>VCH-2025-0001 (KES 100,000.00)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Payment Date</label>
                <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <label>Payment Amount</label>
                <input type="number" class="form-control" value="50000.00">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Bank Account</label>
                <select class="form-control">
                    <option>KCB Main Account</option>
                </select>
            </div>
            <div class="col-md-8">
                <label>Narration</label>
                <input type="text" class="form-control" placeholder="First installment payment">
            </div>
        </div>

        <div class="text-end">
            <button class="btn btn-success">Submit Payment</button>
        </div>
    </form>
</div>
@endsection
