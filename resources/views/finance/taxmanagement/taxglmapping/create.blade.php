@extends('layouts.app')
@section('title', 'Add Tax GL Mapping')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">🧾 Add Tax GL Mapping</h4>

    <form method="POST" action="#">
        @csrf
        <div class="mb-3">
            <label class="form-label">Tax Rule</label>
            <select name="TaxRuleID" class="form-select">
                <option value="1">VAT 16%</option>
                <option value="2">WHT 5%</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Tax Type</label>
            <select name="TaxType" class="form-select">
                <option value="input">Input Tax</option>
                <option value="output">Output Tax</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">GL Account</label>
            <select name="GLAccountID" class="form-select">
                <option value="2100">2100 - VAT Payable</option>
                <option value="3100">3100 - VAT Receivable</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <input type="text" name="Description" class="form-control" placeholder="e.g., Output VAT mapping">
        </div>

        <button type="submit" class="btn btn-success">Save Mapping</button>
        <a href="/finance/taxglmapping" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
