@extends('layouts.app')
@section('title', 'Cashbook')
@section('content')
    <div class="container my-3">
        <div id="toastArea" class="position-fixed top-0 end-0 p-3" style="z-index:1080;">
            @if(session('success'))
                <div class="toast align-items-center text-bg-success border-0" role="alert" id="cashbookToast">
                    <div class="d-flex">
                        <div class="toast-body">{{ session('success') }}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            @elseif(session('error'))
                <div class="toast align-items-center text-bg-danger border-0" role="alert" id="cashbookToast">
                    <div class="d-flex">
                        <div class="toast-body">{{ session('error') }}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            @endif
        </div>

        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">💵 Cashbook</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('cashbook.create.receipt') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i> New Receipt
                    </a>
                    <a href="{{ route('cashbook.create.payment') }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-plus me-1"></i> New Payment
                    </a>
                </div>
            </div>

            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle"
                           style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                        <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Date</th>
                            <th scope="col">Type</th>
                            <th scope="col">Bank Account</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Reference</th>
                            <th scope="col">Party</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($entries as $index => $e)
                            <tr>
                                <td>{{ $entries->firstItem() + $index }}</td>
                                <td>{{ \Carbon\Carbon::parse($e->DocDate)->format('d M Y') }}</td>
                                <td>
                                    <span class="badge {{ $e->EntryType==='RECEIPT'?'bg-success':'bg-danger' }}">
                                        {{ $e->EntryType }}
                                    </span>
                                </td>
                                <td>
                                    {{ optional($e->bankAccount->bank)->BankName ?? '—' }}
                                    — {{ $e->bankAccount->AccountNumber ?? '—' }}
                                </td>
                                <td>{{ optional($e->currency)->Code ?? '—' }}</td>
                                <td>{{ number_format($e->Amount,2) }}</td>
                                <td>{{ $e->Reference ?? '—' }}</td>
                                <td>{{ $e->PartyName ?? '—' }}</td>
                                <td>
                                    @if($e->Status==='Posted')
                                        <span class="badge bg-success">Posted</span>
                                    @elseif($e->Status==='Voided')
                                        <span class="badge bg-secondary">Voided</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Draft</span>
                                    @endif
                                </td>
                                <td class="text-center text-nowrap">
                                    <a href="{{ route('cashbook.show', $e->CashbookID) }}" class="btn btn-sm btn-outline-info me-1" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($e->Status==='Draft')
                                        <a href="{{ route('cashbook.edit', $e->CashbookID) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('cashbook.post', $e->CashbookID) }}" method="POST"
                                              class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" title="Post"
                                                    onclick="return confirm('Post this entry?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('cashbook.destroy', $e->CashbookID) }}" method="POST"
                                              class="d-inline" onsubmit="return confirm('Delete entry?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @elseif($e->Status==='Posted')
                                        <form action="{{ route('cashbook.void', $e->CashbookID) }}" method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Void this entry? This will reverse accounting.')">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" title="Void">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="p-0">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            <i>No cashbook entries have been added yet.</i>
                                        </p>
                                        <a href="{{ route('cashbook.create.payment') }}" class="btn btn-danger px-4 py-2">
                                            <i class="fas fa-plus-circle me-2"></i> Add Cashbook Payment
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted small">
                        Showing {{ $entries->firstItem() ?? 0 }} to {{ $entries->lastItem() ?? 0 }} of {{ $entries->total() }} entries
                    </div>
                    <div>
                        {{ $entries->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toastEl = document.getElementById('cashbookToast');
            if (toastEl && window.bootstrap) {
                const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
                toast.show();
            }
        });
    </script>
@endsection
