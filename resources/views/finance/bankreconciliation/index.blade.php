@extends('layouts.app')
@section('title', 'Bank Reconciliation')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Bank Reconciliation</h1>
            <a href="{{ route('bankreconciliation.create') }}" class="btn btn-success mb-3">New Reconciliation</a>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Bank Account</th>
                        <th>Statement Balance</th>
                        <th>GL Balance</th>
                        <th>Recon Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Equity Bank - Main Account</td>
                        <td>250,000.00</td>
                        <td>248,750.00</td>
                        <td>15/04/2025</td>
                        <td><span class="badge bg-warning">Pending</span></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-primary">Edit</a>
                            <form action="#" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this reconciliation?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Co-op Bank - Operations</td>
                        <td>1,000,000.00</td>
                        <td>999,500.00</td>
                        <td>01/04/2025</td>
                        <td><span class="badge bg-success">Reconciled</span></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-primary">Edit</a>
                            <form action="#" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this reconciliation?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>
    </div>
</div>
@endsection
