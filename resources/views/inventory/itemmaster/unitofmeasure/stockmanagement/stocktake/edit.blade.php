@extends('layouts.app')
@section('title', 'Edit Stock Take')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')

    <div class="container mt-5" style="max-width: 1000px;">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('stocktake.update', $stock->Id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Header Section --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">📍 Branch</label>
                    <select name="BranchId" class="form-select" required>
                        <option value="">-- Select Branch --</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->Id }}" {{ $stock->BranchId == $branch->Id ? 'selected' : '' }}>
                                {{ $branch->Name ?? '-'}}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">🏢 Store</label>
                    <select name="StoreId" class="form-select" required>
                        <option value="">-- Select Store --</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->Id }}" {{ $stock->StoreId == $store->Id ? 'selected' : '' }}>
                                {{ $store->StoreName ?? '-'}}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">🧑‍💼 Counted By</label>
                    <select name="CountedBy" class="form-select" required>
                        <option value="">-- Select User --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->Id }}" {{ $stock->CountedBy == $user->Id ? 'selected' : '' }}>
                                {{ $user->Name ?? '-'}}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">📅 Count Date</label>
                    <input type="date" name="CountDate" class="form-control" value="{{ $stock->CountDate }}" required>
                </div>
            </div>

            {{-- Line Items Table --}}
            <div class="card mb-4">
                <div class="card-header">
                    <strong>🧾 Update Line Items</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>System Qty</th>
                                <th>Counted Qty</th>
                                <th>Remarks</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($stock->lines as $index => $line)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $line->item->item->ItemCode ?? 'N/A' }}</td>
                                    <td>{{ $line->item->item->ItemName ?? 'N/A' }}</td>
                                    <td>{{ $line->ActualQuantity ?? '-'}}</td>
                                    <td>
                                        <input type="hidden" name="lines[{{ $index }}][Id]" value="{{ $line->Id }}">
                                        <input type="number" name="lines[{{ $index }}][CountedQuantity]"
                                               class="form-control"
                                               value="{{ $line->CountedQuantity ?? '-'}}" required>
                                    </td>
                                    <td>
                                        <input type="text" name="lines[{{ $index }}][Remarks]" class="form-control"
                                               value="{{ $line->Remarks ?? '-'}}">
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-success">Update</button>
                <a href="{{ route('stocktake.index') }}" class="btn btn-secondary">Cancel</a>
            </div>

        </form>
    </div>

@endsection
