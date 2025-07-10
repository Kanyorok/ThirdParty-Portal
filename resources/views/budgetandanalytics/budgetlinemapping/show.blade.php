@extends('layouts.app')
@section('title', 'Budget Line Product Types')
@section('content')
    <div class="container mt-4">
        <div class="card p-4">
            <div class="card-header bg-dark text-white">
                Product Types for: {{ $budgetLineName }}
            </div>
            <div class="card-body">
                <p class="text-muted">
                    This budget line is mapped to the following product types.
                    If this budget line is projection-driven, it will have associated product types.
                </p>
                {{-- Display product types --}}
                <div class="mb-0table-responsive">
                    <table class="table table-bordered table-hover text-nowrap text-center">

                        @if(($products ?? collect())->count())
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product Type Name</th>
                                <th>Code</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td style="max-width:200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $product->products->Name }}</td>
                                    <td style="max-width:200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $product->products->ProductCode }}</td>
                                    <td>
                                        <form action="{{route('budgetlinemapping.destroy', $product->Id)}}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="type" value="lineProduct">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete this activity?')">
                                                🗑️
                                            </button>
                                            {{-- <button type="button"
                                                    class="btn btn-sm btn-danger custom-delete-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#customDeleteConfirmModal"
                                                    data-name="{{ $product->products->Name }}"    {{-- Pass item name --}}
                                            {{-- data-route="{{route('budgetlinemapping.destroy', $product->Id)}}"> Pass delete route --}}
                                            {{-- Delete --}}
                                            {{-- </button> --}}
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        @else
                            <p>No product types mapped to this budget line.</p>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('components.modals.delete-confirm')
@endsection
