@extends('layouts.app')

@section('content')
    <h4 class="mb-3">New Issued Cheque</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Fix the following:</strong>
            <ul class="mb-0">@foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('finance.cheques.issued.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Cheque Book <span class="text-danger">*</span></label>
                <select name="ChequeBookID" id="ChequeBookID" class="form-select" required>
                    <option value="">-- select --</option>
                    @foreach($books as $b)
                        <option value="{{ $b->ChequeBookID }}"
                                data-prefix="{{ $b->Prefix }}" data-suffix="{{ $b->Suffix }}"
                                data-next="{{ $b->NextLeafNumber }}" data-start="{{ $b->StartNumber }}"
                                data-end="{{ $b->EndNumber }}"
                            @selected(old('ChequeBookID')==$b->ChequeBookID)>
                            {{ optional($b->bankAccount->bank)->BankName }} — {{ $b->bankAccount?->AccountNumber }}
                            ({{ $b->BookName ?? 'Book '.$b->ChequeBookID }})
                        </option>
                    @endforeach
                </select>
                <small class="text-muted d-block">Bank account is derived from the selected cheque book.</small>
            </div>

            <div class="col-md-4">
                <label class="form-label">Cheque Number <span class="text-danger">*</span></label>
                <input name="ChequeNumber" id="ChequeNumber" class="form-control" value="{{ old('ChequeNumber') }}"
                       required>
                <small id="numHelp" class="text-muted"></small>
            </div>

            <div class="col-md-2">
                <label class="form-label">Cheque Date</label>
                <input type="date" name="ChequeDate" class="form-control"
                       value="{{ old('ChequeDate', now()->toDateString()) }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">Due Date</label>
                <input type="date" name="DueDate" class="form-control" value="{{ old('DueDate') }}">
            </div>

            <div class="col-md-2">
                <label class="form-label d-block">Post-Dated?</label>
                <input type="checkbox" name="IsPostDated" value="1" @checked(old('IsPostDated'))>
            </div>

            <div class="col-md-3">
                <label class="form-label">Currency <span class="text-danger">*</span></label>
                <select name="CurrencyID" class="form-select" required>
                    <option value="">-- select --</option>
                    @foreach($currencies as $c)
                        <option value="{{ $c->Id }}" @selected(old('CurrencyID')==$c->Id)>{{ $c->Code }}
                            — {{ $c->Name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Amount <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="Amount" class="form-control" value="{{ old('Amount') }}"
                       required>
            </div>

            <div class="col-md-2">
                <label class="form-label">Party Type</label>
                <select name="PartyType" class="form-select">
                    <option value="">—</option>
                    @foreach(['CUSTOMER','VENDOR','OTHER'] as $p)
                        <option value="{{ $p }}" @selected(old('PartyType')===$p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Party ID</label>
                <input type="number" name="PartyID" class="form-control" value="{{ old('PartyID') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Party Name</label>
                <input name="PartyName" class="form-control" value="{{ old('PartyName') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Reference</label>
                <input name="Reference" class="form-control" value="{{ old('Reference') }}">
            </div>
            <div class="col-md-9">
                <label class="form-label">Narration</label>
                <input name="Narration" class="form-control" value="{{ old('Narration') }}">
            </div>
        </div>

        <hr class="my-4">
        <div class="d-flex gap-2">
            <button class="btn btn-success">Save</button>
            <a href="{{ route('finance.cheques.index', ['dir'=>'ISSUED']) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <script>
        (function () {
            const sel = document.getElementById('ChequeBookID');
            const inp = document.getElementById('ChequeNumber');
            const help = document.getElementById('numHelp');

            function suggest() {
                const o = sel?.selectedOptions[0];
                if (!o) return;
                const pre = o.dataset.prefix || '', suf = o.dataset.suffix || '';
                const next = o.dataset.next || '';
                const start = o.dataset.start || '', end = o.dataset.end || '';
                if (next) {
                    if (!inp.value) inp.value = (pre ? pre : '') + next + (suf ? suf : '');
                }
                help.textContent = 'Range: ' + (pre ? pre : '') + start + (suf ? suf : '') + ' ... ' + (pre ? pre : '') + end + (suf ? suf : '');
            }

            sel?.addEventListener('change', suggest);
            suggest();
        })();
    </script>
@endsection
