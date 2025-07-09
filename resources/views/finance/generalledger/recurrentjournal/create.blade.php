@extends('layouts.app')
@section('title', 'Recurring Journal Setup')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">🔁 Recurring Journal Setup</h4>

    <form method="POST" action="#">
        @csrf
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Reference Name</label>
                <input type="text" name="ReferenceName" class="form-control" placeholder="e.g., Monthly Rent Accrual">
            </div>
            <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="date" name="StartDate" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Frequency</label>
                <select name="Frequency" class="form-select">
                    <option value="Monthly">Monthly</option>
                    <option value="Quarterly">Quarterly</option>
                    <option value="Annually">Annually</option>
                </select>
            </div>
        </div>

        <h5>Recurring Lines</h5>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>GL Account</th>
                    <th>DR/CR</th>
                    <th>Amount</th>
                    <th>Narration</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < 2; $i++)
                <tr>
                    <td>
                        <select name="GLAccount[]" class="form-select">
                            <option value="">Select</option>
                            <option value="1000">1000 - Cash & Bank</option>
                            <option value="4000">4000 - Salary Expenses</option>
                        </select>
                    </td>
                    <td>
                        <select name="DRCR[]" class="form-select">
                            <option value="DR">DR</option>
                            <option value="CR">CR</option>
                        </select>
                    </td>
                    <td><input type="number" name="Amount[]" class="form-control" step="0.01"></td>
                    <td><input type="text" name="Narration[]" class="form-control"></td>
                </tr>
                @endfor
            </tbody>
        </table>

        <button class="btn btn-primary">Save Recurring Journal</button>
        <a href="/finance/recurrentjournal" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
