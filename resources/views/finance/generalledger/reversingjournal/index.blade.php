@extends('layouts.app')
@section('title', 'Reversing Journals')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">🔁 Reversing Journals</h4>

        <div class="mb-3 text-end">
            <a href="{{ route('reversingjournal.create') }}" class="btn btn-danger">➕ New Reversal</a>
        </div>

        <table class="table table-striped table-bordered">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Original Ref</th>
                <th>Reversal Date</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Reversed By</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>1</td>
                <td>JV20240510</td>
                <td>2025-06-01</td>
                <td>Accrual reversal</td>
                <td><span class="badge bg-success">Completed</span></td>
                <td>John Mwangi</td>
            </tr>
            <tr>
                <td>2</td>
                <td>JV20240528</td>
                <td>2025-06-20</td>
                <td>Error correction</td>
                <td><span class="badge bg-warning text-dark">Pending</span></td>
                <td>Jane Wanjiku</td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
