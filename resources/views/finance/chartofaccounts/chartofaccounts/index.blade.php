@extends('layouts.app')
@section('title', 'Chart of Accounts')
@section('content')
    <div class="container mt-4">
        <!-- Add New Account Button -->
        <div class="mb-3">
            <a href="{{ route('chartofaccounts.create') }}" class="btn btn-primary">➕ Add New Account</a>
        </div>
        <div class="card p-2">
{{--            <div class="card-header bg-light text-black">--}}
{{--                Chart Of Accouts Table--}}
{{--            </div>--}}
            <div class="card-body mb-3">
                <p class="text-muted">
                    The Chart of Accounts defines the structure of your financial ledger by categorizing all general ledger (GL) accounts used for tracking assets, liabilities, income, expenses, and equity. Each account plays a key role in accurate financial reporting and compliance.
                </p>
                @if($charts->count())
                    <!-- Accounts Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped align-middle text-center">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>GL Name</th>
                                <th>Account Code</th>
                                <th>GL Type</th>
                                <th>GL Type Group</th>
                                <th>GL Sub-Type</th>
                                {{--                                <th>Parent</th>--}}
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $charts as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->GLName ?? '-'}}</td>
                                    <td>
                                    @foreach($glOrders as $glOrder)
                                            @if($glOrder->SegmentType == 'GLDigits')
                                                @php
                                                    $digits = $item->{$glOrder->SegmentType} ?? 0;
                                                    $formattedId = strlen($item->Id) >= $digits
                                                        ? $item->Id
                                                        : str_pad($item->Id, $digits, '0', STR_PAD_LEFT);
                                                @endphp
                                                {{ $formattedId }} @if (!$loop->last) - @endif
                                            @else
                                            {{ $item->{$glOrder->SegmentType} ?? '-' }} @if (!$loop->last) - @endif
                                          @endif
                                    @endforeach
                                    </td>
                                    <td>{{ $item->GLAccountTypeID ?? '-'}}</td>
                                    <td>{{ $item->typeGroup->Description ?? '-'}}</td>
                                    <td>{{ $item->subAccount->Description ?? '-'}}</td>
{{--                                    <td>{{ optional($item->parent)->GLCode ?? '-' }}</td>--}}
                                    <td>{{ $item->Description ?? '-'}}</td>
                                    <td>
                                        @if($item->IsActive)
                                            <span class="text-success" value="1">✅ Active</span>
                                        @else
                                            <span class="text-danger" value="0">🚫 Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{route('chartofaccounts.edit',$item->Id)}}" class="btn btn-sm btn-warning">Edit</a>
                                        <button type="button"
                                                class="btn btn-sm btn-danger custom-delete-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#customDeleteConfirmModal"
                                                data-name="{{ $item->GLName }}"    {{-- Pass item name --}}
                                                data-route="{{ route('chartofaccounts.destroy', $item->Id) }}"> {{-- Pass delete route --}}
                                            Delete
                                        </button>
                                    </td>
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


    <!-- Import the Custom Delete Modal -->
    @include('components.modals.delete-confirm')
@endsection
