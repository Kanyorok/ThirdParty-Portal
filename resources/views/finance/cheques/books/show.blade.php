@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4>Cheque Book #{{ $row->ChequeBookID }} — {{ $row->BookName ?? 'Book' }}</h4>
            <div class="text-muted">
                Bank: {{ optional($row->bankAccount->bank)->BankName }} — {{ $row->bankAccount?->AccountNumber }}
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.chequebooks.index') }}" class="btn btn-outline-secondary">Back</a>
            <a href="{{ route('finance.chequebooks.edit',$row->ChequeBookID) }}"
               class="btn btn-outline-primary">Edit</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-2"><strong>Range:</strong> {{ $row->StartNumber }} – {{ $row->EndNumber }}</div>
        <div class="col-md-2"><strong>Next Leaf:</strong> {{ $row->NextLeafNumber }}</div>
        <div class="col-md-2"><strong>Issued/Total:</strong> {{ $row->LeavesIssued }}/{{ $row->LeavesTotal }}</div>
        <div class="col-md-2"><strong>Active:</strong> {{ $row->IsActive ? 'Yes':'No' }}</div>
        <div class="col-md-4"><strong>Prefix/Suffix:</strong> {{ $row->Prefix }} / {{ $row->Suffix }}</div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-2">
                <div class="col-sm-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status" onchange="this.form.submit()">
                        @php $f = request('status'); @endphp
                        <option value="">(Any)</option>
                        @foreach(['Unused','Reserved','Issued','Cleared','Bounced','Cancelled','Spoiled'] as $s)
                            <option value="{{ $s }}" @selected($f===$s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3">
                    <label class="form-label">Contains Number</label>
                    <input name="q" class="form-control" value="{{ request('q') }}" placeholder="Cheque No or Leaf #">
                </div>
                <div class="col-sm-auto align-self-end">
                    <button class="btn btn-secondary">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Leaf #</th>
                        <th>Cheque No.</th>
                        <th>Status</th>
                        <th>ChequeID</th>
                        <th>Used On</th>
                        <th>Cleared On</th>
                        <th>Cancelled On</th>
                        <th>Notes</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $leavesQuery = \App\Models\Finance\ChequeLeaf::where('ChequeBookID',$row->ChequeBookID)->orderBy('LeafNumber');
                        if(request('status')) $leavesQuery->where('Status', request('status'));
                        if(request('q')) {
                          $q = request('q');
                          $leavesQuery->where(function($qq) use ($q){
                            $qq->where('ChequeNumber','like',"%$q%")->orWhere('LeafNumber',$q);
                          });
                        }
                        $leaves = $leavesQuery->paginate(50);
                    @endphp

                    @forelse($leaves as $leaf)
                        <tr>
                            <td>{{ $leaf->LeafNumber }}</td>
                            <td>{{ $leaf->ChequeNumber }}</td>
                            <td><span
                                    class="badge bg-{{ $leaf->Status==='Unused'?'secondary':($leaf->Status==='Issued'?'warning text-dark':($leaf->Status==='Cleared'?'success':($leaf->Status==='Bounced'?'danger':($leaf->Status==='Cancelled'?'dark':'info')))) }}">{{ $leaf->Status }}</span>
                            </td>
                            <td>{{ $leaf->ChequeID ?? '-' }}</td>
                            <td>{{ $leaf->UsedOn ?? '-' }}</td>
                            <td>{{ $leaf->ClearedOn ?? '-' }}</td>
                            <td>{{ $leaf->CancelledOn ?? '-' }}</td>
                            <td>{{ $leaf->Notes ?? '-' }}</td>
                            <td class="text-end">
                                @if($leaf->Status==='Unused')
                                    <form
                                        action="{{ route('finance.chequebooks.leaves.spoil',[$row->ChequeBookID,$leaf->LeafID]) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Mark this unused leaf as spoiled?');">Spoil
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No leaves.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $leaves->appends(request()->query())->links() }}
        </div>
    </div>
@endsection
