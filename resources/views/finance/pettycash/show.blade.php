@extends('layouts.app')

@section('content')
    @php
        $requiresApproval = (bool)($row->float->RequireApproval ?? false);
        $approvalLimit    = (float)($row->float->ApprovalLimit ?? 0);
        $exceedsLimit     = (float)$row->Amount > $approvalLimit;
        $mustApprove      = $requiresApproval && $exceedsLimit;
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">
                Petty Cash Voucher #{{ $row->VoucherID }} — {{ $row->VoucherType }}
            </h4>
            <div class="d-flex gap-2">
      <span
          class="badge bg-{{ $row->Status==='Posted' ? 'success' : ($row->Status==='Voided' ? 'secondary' : 'warning text-dark') }}">
        {{ $row->Status }}
      </span>
                <span class="badge bg-light text-dark border">
        Approval: {{ $row->ApprovalStatus ?? 'N/A' }}
      </span>
                @if($requiresApproval)
                    <span class="badge bg-info text-dark">Approval limit {{ number_format($approvalLimit,2) }}</span>
                    @if($exceedsLimit)
                        <span class="badge bg-danger">Exceeds limit</span>
                    @endif
                @endif
                @if($row->ReplenishmentBatchID)
                    <span class="badge bg-primary">Batch #{{ $row->ReplenishmentBatchID }}</span>
                @endif
            </div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('finance.pettycash.index') }}">Back</a>
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
                <div class="col-md-3"><strong>Float:</strong> {{ $row->float?->Name }}</div>
                <div class="col-md-3"><strong>Date:</strong> {{ $row->DocDate }}</div>
                <div class="col-md-3">
                    <strong>Amount:</strong> {{ number_format($row->Amount,2) }} {{ $row->currency?->Code }}</div>
                <div class="col-md-3">
                    <strong>Rate:</strong> {{ $row->ExchangeRate ? number_format($row->ExchangeRate,6) : '1' }}
                </div>

                @if($row->BankAccountID)
                    <div class="col-md-3">
                        <strong>Bank Account:</strong> ID {{ $row->BankAccountID }}
                    </div>
                @endif

                <div class="col-md-3">
                    <strong>Approval Status:</strong> {{ $row->ApprovalStatus ?? 'N/A' }}
                </div>

                <div class="col-md-3">
                    <strong>Reference:</strong> {{ $row->Reference ?? '-' }}
                </div>
                <div class="col-md-3">
                    <strong>Cashbook:</strong>
                    @if($row->CashbookID)
                        @if(\Illuminate\Support\Facades\Route::has('cashbook.show'))
                            <a href="{{ route('cashbook.show', $row->CashbookID) }}">#{{ $row->CashbookID }}</a>
                        @else
                            #{{ $row->CashbookID }}
                        @endif
                    @else
                        -
                    @endif
                </div>

                <div class="col-md-12"><strong>Narration:</strong> {{ $row->Narration ?? '-' }}</div>

                {{-- Timestamps (if present) --}}
                @if($row->SubmittedOn || $row->ApprovedOn || $row->PostedOn || $row->VoidedOn)
                    <div class="col-md-12">
                        <hr class="my-2">
                    </div>
                    @if($row->SubmittedOn)
                        <div class="col-md-3"><small class="text-muted">Submitted:</small> {{ $row->SubmittedOn }}</div>
                    @endif
                    @if($row->ApprovedOn)
                        <div class="col-md-3"><small class="text-muted">Approved:</small> {{ $row->ApprovedOn }}</div>
                    @endif
                    @if($row->PostedOn)
                        <div class="col-md-3"><small class="text-muted">Posted:</small> {{ $row->PostedOn }}</div>
                    @endif
                    @if($row->VoidedOn)
                        <div class="col-md-3"><small class="text-muted">Voided:</small> {{ $row->VoidedOn }}</div>
                    @endif
                @endif

                @if($row->ReplenishmentBatchID)
                    <div class="col-md-12">
                        <small class="text-muted">Replenishment Batch:</small>
                        {{-- Link only if you add a batch.show route later --}}
                        #{{ $row->ReplenishmentBatchID }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(in_array($row->VoucherType,['DISBURSEMENT','ADJUSTMENT']))
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="mb-2">Lines</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>GL</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($row->lines as $i=>$ln)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $ln->GLAccountID ?? '-' }}</td>
                                <td>{{ $ln->Description }}</td>
                                <td class="text-end">{{ number_format($ln->Amount,2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No lines.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <h5>Actions</h5>

            {{-- DRAFT actions --}}
            @if($row->Status==='Draft')
                {{-- Submit for approval (only if required & exceeds limit & not already pending/approved) --}}
                @if($mustApprove && !in_array($row->ApprovalStatus,['Pending','Approved']))
                    @if(Route::has('finance.pettycash.submit'))
                        <form method="POST" action="{{ route('finance.pettycash.submit',$row->VoucherID) }}"
                              class="d-inline">@csrf
                            <button class="btn btn-outline-secondary">Submit for Approval</button>
                        </form>
                    @endif
                @endif

                {{-- Approver controls (show if pending). Wrap in @can if you use policies --}}
                @if($row->ApprovalStatus==='Pending')
                    @if(Route::has('finance.pettycash.approve'))
                        <form method="POST" action="{{ route('finance.pettycash.approve',$row->VoucherID) }}"
                              class="d-inline">@csrf
                            <button class="btn btn-success">Approve</button>
                        </form>
                    @endif
                    @if(Route::has('finance.pettycash.reject'))
                        <form method="POST" action="{{ route('finance.pettycash.reject',$row->VoucherID) }}"
                              class="d-inline">@csrf
                            <button class="btn btn-outline-danger">Reject</button>
                        </form>
                    @endif
                @endif

                {{-- Post (hide if mustApprove and not Approved) --}}
                @if(!$mustApprove || $row->ApprovalStatus==='Approved' || $row->ApprovalStatus==='N/A')
                    <form method="POST" action="{{ route('finance.pettycash.post',$row->VoucherID) }}"
                          class="d-inline">@csrf
                        <button class="btn btn-primary">Post</button>
                    </form>
                @endif

                {{-- Delete --}}
                <form method="POST" action="{{ route('finance.pettycash.destroy',$row->VoucherID) }}" class="d-inline"
                      onsubmit="return confirm('Delete this voucher?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger">Delete</button>
                </form>
            @endif

            {{-- POSTED actions --}}
            @if($row->Status==='Posted')
                <form method="POST" action="{{ route('finance.pettycash.void',$row->VoucherID) }}" class="d-inline"
                      onsubmit="return confirm('Void this voucher?');">@csrf
                    <button class="btn btn-warning">Void</button>
                </form>
            @endif

        </div>
    </div>
@endsection
