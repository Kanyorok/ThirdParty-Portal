@extends('layouts.app')
@section('title', 'Chart of Accounts')
@section('content')
    <div class="container mt-4">
        <div class="card p-4">
            <div class="card-header bg-dark text-white">
                Chart Of Accouts Table
            </div>
            <div class="card-body mb-3">
                <p class="text-muted">
                    The Chart of Accounts defines the structure of your financial ledger by categorizing all general ledger (GL) accounts used for tracking assets, liabilities, income, expenses, and equity. Each account plays a key role in accurate financial reporting and compliance.
                </p>
                <!-- Add New Account Button -->
                <div class="mb-3">
                    <a href="{{ route('chartofaccounts.create') }}" class="btn btn-primary">➕ Add New Account</a>
                </div>

                @if($charts->count())
                    <!-- Accounts Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped align-middle text-center">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Account Code</th>
                                <th>Account Name</th>
                                <th>Account Type</th>
                                <th>Account Type Group</th>
                                <th>Parent</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $charts as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->GLCode ?? '-'}}</td>
                                    <td>{{ $item->GLName ?? '-'}}</td>
                                    <td>{{ $item->GLAccountTypeID ?? '-'}}</td>
                                    <td>{{ $item->GLTypeGroupID ?? '-'}}</td>
                                    <td>{{ optional($item->parent)->GLCode ?? '-' }}</td>
                                    <td>{{ $item->Description ?? '-'}}</td>
                                    <td>
                                        @if($item->IsActive)
                                            <span class="text-success" value="1">✅ Active</span>
                                        @else
                                            <span class="text-danger" value="0">🚫 Inactive</span>
                                        @endif
                                    </td>
                                    <td><a href="#" class="btn btn-sm btn-warning">Edit</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        No General Ledger Accounts created
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
