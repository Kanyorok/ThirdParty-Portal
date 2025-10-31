@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Cheque #{{ $row->ChequeID }} — {{ $row->Direction }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.cheques.index',['dir'=>$row->Direction]) }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3"><strong>Status:</strong> <span
                        class="badge bg-{{ $row->Status==='Cleared'?'success':($row->Status==='Bounced'?'danger':($row->Status==='Deposited'?'info':($row->Status==='OnHand'?'warning text-dark':($row->Status==='Issued'?'secondary':'dark')))) }}">{{ $row->Status }}</span>
                </div>
                <div class="col-md-3"><strong>Cheque No:</strong> {{ $row->ChequeNumber }}</div>
                <div class="col-md-3"><strong>Cheque
                        Date:</strong> {{ $row->ChequeDate ? \Illuminate\Support\Carbon::parse($row->ChequeDate)->format('Y-m-d') : '-' }}
                </div>
                <div class="col-md-3"><strong>Due
                        Date:</strong> {{ $row->DueDate ? \Illuminate\Support\Carbon::parse($row->DueDate)->format('Y-m-d') : '-' }}
                </div>
                <div class="col-md-3">
                    <strong>Amount:</strong> {{ number_format($row->Amount,2) }} {{ $row->currency?->Code }}</div>
                <div class="col-md-3"><strong>Post-Dated:</strong> {{ $row->IsPostDated ? 'Yes':'No' }}</div>
                <div class="col-md-6"><strong>Bank:</strong> {{ optional($row->bankAccount?->bank)->BankName ?? '-' }}
                    — {{ $row->bankAccount?->AccountNumber ?? '-' }}</div>
                <div class="col-md-4">
                    <strong>Party:</strong> {{ $row->PartyType ?? '-' }} {{ $row->PartyID ? '#'.$row->PartyID : '' }}
                </div>
                <div class="col-md-8"><strong>Party Name:</strong> {{ $row->PartyName ?? '-' }}</div>
                <div class="col-md-4"><strong>Reference:</strong> {{ $row->Reference ?? '-' }}</div>
                <div class="col-md-8"><strong>Narration:</strong> {{ $row->Narration ?? '-' }}</div>

                @if($row->Direction==='RECEIVED')
                    <div class="col-md-4"><strong>Received:</strong> {{ $row->ReceivedDate ?? '-' }}</div>
                    <div class="col-md-4"><strong>Deposited:</strong> {{ $row->DepositDate ?? '-' }}</div>
                @endif
                <div class="col-md-4"><strong>Cleared:</strong> {{ $row->ClearDate ?? '-' }}</div>
                <div class="col-md-4"><strong>Bounced:</strong> {{ $row->BounceDate ?? '-' }}</div>
            </div>
        </div>
    </div>

    {{-- ACTIONS --}}
    <div class="card">
        <div class="card-body">
            <h5 class="mb-3">Actions</h5>

            @if($row->Direction==='RECEIVED' && in_array($row->Status,['OnHand']))
                {{-- Deposit Form --}}
                <form method="POST" action="{{ route('finance.cheques.deposit',$row->ChequeID) }}"
                      class="row g-2 align-items-end border rounded p-3 mb-3">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">Deposit To Bank Account <span class="text-danger">*</span></label>
                        <select name="BankAccountID" class="form-select" required>
                            <option value="">-- select --</option>
                            @foreach(\App\Models\Finance\BankAccount::with('bank')->orderBy('AccountNumber')->get() as $ba)
                                <option value="{{ $ba->AccountID }}">{{ optional($ba->bank)->BankName }}
                                    — {{ $ba->AccountNumber }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Deposit Date <span class="text-danger">*</span></label>
                        <input type="date" name="DocDate" class="form-control" value="{{ now()->toDateString() }}"
                               required>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary mt-4">Deposit & Post</button>
                    </div>
                </form>
            @endif

            {{-- Clear Form (Issued or Received) --}}
            @if( ($row->Direction==='ISSUED' && in_array($row->Status,['Issued'])) ||
                 ($row->Direction==='RECEIVED' && in_array($row->Status,['Deposited'])) )
                <form method="POST" action="{{ route('finance.cheques.clear',$row->ChequeID) }}"
                      class="row g-2 align-items-end border rounded p-3 mb-3">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label">Clear Date <span class="text-danger">*</span></label>
                        <input type="date" name="DocDate" class="form-control" value="{{ now()->toDateString() }}"
                               required>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success mt-4">Mark as Cleared</button>
                    </div>
                </form>
            @endif

            {{-- Bounce Form --}}
            @if( ($row->Direction==='RECEIVED' && in_array($row->Status,['Deposited'])) ||
                 ($row->Direction==='ISSUED' && in_array($row->Status,['Issued'])) )
                <form method="POST" action="{{ route('finance.cheques.bounce',$row->ChequeID) }}"
                      class="row g-2 align-items-end border rounded p-3 mb-3">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label">Bounce Date <span class="text-danger">*</span></label>
                        <input type="date" name="DocDate" class="form-control" value="{{ now()->toDateString() }}"
                               required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Reason</label>
                        <input name="Reason" class="form-control"
                               placeholder="e.g., Refer to Drawer, Insufficient funds">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-warning mt-4">Mark as Bounced</button>
                    </div>
                </form>
            @endif

            {{-- Cancel Form --}}
            @if(in_array($row->Status,['Draft','Issued','OnHand']))
                <form method="POST" action="{{ route('finance.cheques.cancel',$row->ChequeID) }}"
                      onsubmit="return confirm('Cancel this cheque?');">
                    @csrf
                    <button class="btn btn-outline-danger">Cancel Cheque</button>
                </form>
            @endif
        </div>
    </div>
@endsection
