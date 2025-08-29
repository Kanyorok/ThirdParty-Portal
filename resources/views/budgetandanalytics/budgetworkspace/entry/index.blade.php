@extends('layouts.app')
@section('title', '📋 Budget Projections Overview')

@section('content')
    <div class="card p-3">
        <p class="muted">
            The table below displays forecasted financial values (projections) linked to products from the CBS.
            Each projection is based on specific drivers and rate types (e.g., interest or growth rates) and helps
            estimate
            expected income or expenses over a given period.
        </p>
        {{-- <h5>📋 Budget Projections</h5> --}}

        <div class="mb-3 d-flex justify-content-between">
            <a href="{{ route('budgetprojections.create') }}" class="btn btn-success">➕ Add Projection</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Budget</th>
                {{-- <th>Currency</th> --}}
                <th>Products</th>
                <th>No of Accounts</th>
                {{-- <th>Projected Value</th> --}}
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($data as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item['Name'] }}</td>
                    <td>{{ $item['Products'] }}</td>
                    <td>{{ $item['Accounts'] }}</td>
                    <td>

                        <a href="{{route('budgetprojections.show',$item['Id'])}}"
                           class="btn btn-sm btn-outline-info">View</a>

                        <button type="button"
                                class="btn btn-sm btn-danger custom-delete-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#customDeleteConfirmModal"
                                data-name="{{ $item['Name'] }} ALL Projections" {{-- Pass item name --}}
                                data-route="{{ route('budgetprojections.destroy', $item['Id']) }}"> {{-- Pass delete route --}}
                            Delete
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </div>


    @include('components.modals.delete-confirm')
@endsection
