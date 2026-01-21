@extends('layouts.app')

@section('title', 'Commission Rules')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    /* 🧩 Table Styling */
    #commissionrules thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        text-align: center;
    }

    .table td,
    .table th {
        vertical-align: middle !important;
    }

    table.dataTable tbody tr:hover {
        background-color: #f9fbfd;
    }

    /* 🔍 DataTables Inputs */
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 20px;
        padding: 4px 12px;
        border: 1px solid #ced4da;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 20px;
        padding: 3px 10px;
        border: 1px solid #ced4da;
    }

    /* 🎨 Badges & Buttons */
    .badge {
        font-size: 0.85rem;
    }

    .btn-group .btn {
        margin-right: 4px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    /* 🔹 Icon Sizes */
    i.bi {
        font-size: 0.9rem;
        /* general icons */
    }

    td i.bi {
        font-size: 0.75rem;
        /* table action icons */
        vertical-align: middle;
    }

    .btn-sm i.bi {
        margin-top: -1px;
        /* align icon in compact buttons */
    }
</style>
@endsection

@section('content')
<div class="container mt-4">

    {{-- ✅ Header / Add Button --}}
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('commissions.rules.create') }}"
            class="btn btn-sm btn-primary rounded-pill shadow-sm">
            <i class="bi bi-plus-circle me-1 fs-6"></i> Add Commission Rule
        </a>
    </div>

    <p class="text-muted small mb-3">
        <i class="bi bi-list-check me-2 text-primary"></i>
        The list below consists of all commission rules.
    </p>

    {{-- ✅ Commission Rules Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="commissionrules" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Rule Name</th>
                            <th>Product</th>
                            <th>Policy Type</th>
                            <th>Commission Rate (%)</th>
                            <th>Fixed Amount</th>
                            <th>Applies To</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rules as $rule)
                        <tr>
                            <td class="text-center">{{ $loop->iteration ?? '-' }}</td>
                            <td>{{ $rule->RuleName ?? '-' }}</td>
                            <td>{{ $rule->product->Name ?? '-' }}</td>
                            <td>{{ $rule->policytypes->Description ?? '-' }}</td>
                            <td class="text-end">{{ $rule->CommissionRate ?? '-' }}</td>
                            <td class="text-end">{{ $rule->currency->SymbolNative ?? 'cu' }} {{ $rule->FixedAmount ?? '-' }}</td>
                            <td>{{ $rule->appliesto->Description ?? '-' }}</td>
                            <td class="text-center">
                                @if($rule->IsActive)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('commissions.rules.edit', $rule->Id) }}"
                                        class="btn btn-outline-warning btn-sm rounded-pill px-2 py-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('commissions.rules.destroy', $rule->Id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2 py-1"
                                            onclick="return confirm('Are you sure you want to delete this rule?');" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#commissionrules').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search commission rules..."
            },
            columnDefs: [{
                    orderable: false,
                    targets: [8]
                } // Disable sorting on Actions column
            ]
        });
    });
</script>
@endsection