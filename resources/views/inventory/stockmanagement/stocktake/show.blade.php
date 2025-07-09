@extends('layouts.app')
@section('title', 'Stock Take Details')
@section('content')

    <div class="container mt-5" style="max-width: 1000px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary">📦 Stock Take Details</h3>
            <a href="{{ route('stocktake.index') }}" class="btn btn-outline-secondary btn-sm">⬅ Back to List</a>
        </div>

        {{-- Header Info --}}
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-light">
                <h5 class="mb-0 text-dark">📝 Header Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>📍 Branch:</strong> <br>
                        <span class="text-muted">{{ $stock->branch->Name ?? '-' }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>🏢 Store:</strong> <br>
                        <span class="text-muted">{{ $stock->store->StoreName ?? '-' }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>🧑‍💼 Counted By:</strong> <br>
                        <span class="text-muted">{{ $stock->countedby->Name ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>📅 Count Date:</strong> <br>
                        <span class="text-muted">{{ \Carbon\Carbon::parse($stock->CountDate)->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Line Items --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light">
                <h5 class="mb-0 text-dark">🧾 Counted Items</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>System Qty</th>
                            <th>Counted Qty</th>
                            <th>Variance</th>
                            <th>Remarks</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($stock->lines as $index => $line)
                            <tr class="text-center">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $line->item->item->ItemCode ?? 'N/A' }}</td>
                                <td>{{ $line->item->item->ItemName ?? 'N/A' }}</td>
                                <td>{{ $line->ActualQuantity }}</td>
                                <td>{{ $line->CountedQuantity }}</td>
                                <td>
                                    @php $variance = $line->CountedQuantity - $line->ActualQuantity; @endphp
                                    <span
                                        class="badge {{ $variance === 0 ? 'bg-success' : ($variance > 0 ? 'bg-primary' : 'bg-danger') }}">
                    {{ $variance }}
                  </span>
                                </td>
                                <td class="text-start">{{ $line->Remarks }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No stock take lines found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
