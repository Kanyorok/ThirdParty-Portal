@extends('layouts.app')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Cashbook</h1>
        <div>
            <a href="{{ route('cashbook.create.receipt') }}" class="btn btn-success">New Receipt</a>
            <a href="{{ route('cashbook.create.payment') }}" class="btn btn-danger">New Payment</a>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success mt-3">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger mt-3">{{ session('error') }}</div> @endif

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Bank Account</th>
                    <th>Currency</th>
                    <th>Amount</th>
                    <th>Ref</th>
                    <th>Party</th>
                    <th>Status</th>
                    <th style="width:220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $e)
                    <tr>
                        <td>{{ $e->CashbookID }}</td>
                        <td>{{ \Carbon\Carbon::parse($e->DocDate)->format('Y-m-d') }}</td>
                        <td><span class="badge {{ $e->EntryType==='RECEIPT'?'bg-success':'bg-danger' }}">{{ $e->EntryType }}</span></td>
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
                                <span class="badge bg-primary">Posted</span>
                            @elseif($e->Status==='Voided')
                                <span class="badge bg-secondary">Voided</span>
                            @else
                                <span class="badge bg-warning text-dark">Draft</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('cashbook.show', $e->CashbookID) }}" class="btn btn-sm btn-info">View</a>
                            @if($e->Status==='Draft')
                                <a href="{{ route('cashbook.edit', $e->CashbookID) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('cashbook.post', $e->CashbookID) }}" method="POST" class="d-inline">
                                    @csrf <button class="btn btn-sm btn-primary" onclick="return confirm('Post this entry?')">Post</button>
                                </form>
                                <form action="{{ route('cashbook.destroy', $e->CashbookID) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete entry?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @elseif($e->Status==='Posted')
                                <form action="{{ route('cashbook.void', $e->CashbookID) }}" method="POST" class="d-inline" onsubmit="return confirm('Void this entry? This will reverse accounting.')">
                                    @csrf <button class="btn btn-sm btn-outline-danger">Void</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No entries yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $entries->links() }}
</div>
@endsection
