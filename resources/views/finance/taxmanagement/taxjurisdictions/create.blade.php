@extends('layouts.app')
@section('title', 'Tax Jurisdiction Setup')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">🌍 Add Tax Jurisdiction</h4>

        <form method="POST" action="#">
            @csrf

            <div class="mb-3">
                <label class="form-label">Jurisdiction Name</label>
                <input type="text" name="JurisdictionName" class="form-control" placeholder="e.g., Kenya, Uganda">
            </div>

            <div class="mb-3">
                <label class="form-label">Currency</label>
                <select name="Currency" class="form-select">
                    <option value="KES">KES</option>
                    <option value="UGX">UGX</option>
                    <option value="TZS">TZS</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Tax Authority</label>
                <input type="text" name="TaxAuthority" class="form-control" placeholder="e.g., KRA, URA">
            </div>

            <button type="submit" class="btn btn-success">Save Jurisdiction</button>
        </form>
    </div>
@endsection
