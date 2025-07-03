@extends('layouts.app')

@section('title', 'View Transfers')

@section('content')
    {{-- Session Errors --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Custom client‑side error --}}
    <div id="customErrorContainer" style="display:none;">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span id="customErrorMessage"></span>
            <button type="button" class="btn-close" aria-label="Close" onclick="hideCustomError()"></button>
        </div>
    </div>

    <div class="container bg-white shadow-sm rounded p-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Goods Transfer List</h4>
            <a href="{{ route('transactionstransfers.create') }}" class="btn btn-success">➕ New Transfer</a>
        </div>

        <div class="table-responsive">
            <table id="transferTable" class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Transfer ID</th>
                    <th>Date</th>
                    <th>From Branch</th>
                    <th>To Branch</th>
                    <th>Transferred By</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($transfers as $i => $transfer)
                    @php
                        $statusEnum = $transfer->Status instanceof \App\Enums\Inventory\Transfers
                            ? $transfer->Status
                            : (\App\Enums\Inventory\Transfers::tryFrom($transfer->Status) ?? null);

                        $isPending = $statusEnum
                            && $statusEnum->value === \App\Enums\Inventory\Transfers::Pending->value;
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $transfer->TransferID ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($transfer->TransferDate)->format('d/m/Y') }}</td>
                        <td>{{ optional($transfer->fromBranch)->Name ?? '-' }}</td>
                        <td>{{ optional($transfer->toBranch)->Name ?? '-' }}</td>
                        <td>{{$transfer->transferredBy->Name ?? 'N/A'}}</td>
                        <td>
                            @if($statusEnum)
                                <span class="badge bg-{{ $statusEnum->badgeColor() }}">
                                        {{ $statusEnum->label() }}
                                    </span>
                            @else
                                <span class="badge bg-secondary">{{ $transfer->Status ?? '-' }}</span>
                            @endif
                        </td>
                        <td class="d-flex gap-1">
                            {{-- View always allowed --}}
                            <a href="{{ route('transactionstransfers.show', $transfer->Id) }}"
                               class="btn btn-sm btn-primary">View</a>

                            {{-- Edit --}}
                            @if($isPending)
                                <a href="{{ route('transactionstransfers.edit', $transfer->Id) }}"
                                   class="btn btn-sm btn-warning">Edit</a>
                            @else
                                <button type="button" class="btn btn-sm btn-warning"
                                        onclick="return showCustomError('Only pending transfers can be edited.');">
                                    Edit
                                </button>
                            @endif

                            {{-- Delete --}}
                            @if($isPending)
                                <form id="delete-form-{{ $transfer->Id }}"
                                      action="{{ route('transactionstransfers.destroy', $transfer->Id) }}"
                                      method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="return confirmDelete('{{ $transfer->Id }}');">
                                        Delete
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn btn-sm btn-danger"
                                        onclick="return showCustomError('Only pending transfers can be deleted.');">
                                    Delete
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No transfers found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- JavaScript --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            @if(!$transfers->isEmpty())
            $('#transferTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true,
                dom: 'rt<"bottom"ip><"clear">',
                language: {
                    emptyTable: "No transfers available"
                }
            });
            @endif
        });

        function confirmDelete(id) {
            if (confirm('⚠️ Are you sure you want to delete this transfer?')) {
                document.getElementById('delete-form-' + id).submit();
            }
            return false;
        }

        function showCustomError(message) {
            document.getElementById('customErrorMessage').textContent = message;
            document.getElementById('customErrorContainer').style.display = 'block';
            window.scrollTo({top: 0, behavior: 'smooth'});
            return false;
        }

        function hideCustomError() {
            document.getElementById('customErrorContainer').style.display = 'none';
        }
    </script>

@endsection
