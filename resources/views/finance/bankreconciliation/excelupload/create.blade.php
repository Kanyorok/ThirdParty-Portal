@extends('layouts.app')
@section('title', 'Upload Bank Statement')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">📤 Upload Bank Statement</h4>

        <form method="POST" action="#" enctype="multipart/form-data">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="bank_account" class="form-label">Bank Account</label>
                    <select name="bank_account" id="bank_account" class="form-select" required>
                        <option selected disabled>-- Select Account --</option>
                        <option value="1">001 - Equity Bank - KES</option>
                        <option value="2">002 - KCB Corporate - USD</option>
                        <option value="3">003 - Stanbic - EUR</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="statement_file" class="form-label">Statement File</label>
                <input type="file" name="statement_file" id="statement_file" class="form-control" accept=".csv,.txt"
                       required>
                <div class="form-text">Accepted formats: CSV, TXT (e.g., MT940, BAI2)</div>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Notes (optional)</label>
                <textarea name="notes" id="notes" class="form-control" rows="3"
                          placeholder="E.g. Upload for June 2025"></textarea>
            </div>

            <button type="submit" class="btn btn-success">Upload</button>
            <a href="{{ route('reconuploads.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
