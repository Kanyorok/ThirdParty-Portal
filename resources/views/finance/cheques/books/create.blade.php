@extends('layouts.app')

@section('content')
    <h4 class="mb-3">New Cheque Book</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Fix the following:</strong>
            <ul class="mb-0">@foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('finance.chequebooks.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Bank Account <span class="text-danger">*</span></label>
                <select name="BankAccountID" id="BankAccountID" class="form-select" required>
                    <option value="">-- select --</option>
                    @foreach($bankAccounts as $ba)
                        <option value="{{ $ba->AccountID }}" @selected(old('BankAccountID', $firstId)==$ba->AccountID)>
                            {{ optional($ba->bank)->BankName }} — {{ $ba->AccountNumber }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Book Size <span class="text-danger">*</span></label>
                <select name="BookSize" id="BookSize" class="form-select" required>
                    @foreach([30,50,100] as $size)
                        <option value="{{ $size }}" @selected(old('BookSize',50)==$size)>{{ $size }} pages</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-5">
                <label class="form-label">Book Name</label>
                <input name="BookName" class="form-control" value="{{ old('BookName') }}"
                       placeholder="e.g., Main Ops Book 2025-Q3">
            </div>

            <div class="col-md-2">
                <label class="form-label">Prefix</label>
                <input name="Prefix" id="Prefix" class="form-control" value="{{ old('Prefix') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Suffix</label>
                <input name="Suffix" id="Suffix" class="form-control" value="{{ old('Suffix') }}">
            </div>

            {{-- Auto-computed by AJAX; still shown to user for transparency --}}
            <div class="col-md-2">
                <label class="form-label">Start Number</label>
                <input name="StartNumber" id="StartNumber" class="form-control" value="{{ old('StartNumber') }}"
                       readonly>
            </div>
            <div class="col-md-2">
                <label class="form-label">End Number</label>
                <input name="EndNumber" id="EndNumber" class="form-control" value="{{ old('EndNumber') }}" readonly>
            </div>
            <div class="col-md-2">
                <label class="form-label">Next Leaf</label>
                <input id="NextLeafNumber" class="form-control" value="{{ old('NextLeafNumber') }}" readonly>
            </div>
        </div>

        <small class="text-muted d-block mt-2" id="rangeHelp">Select a bank account & size to see the proposed
            range.</small>

        <hr class="my-4">
        <div class="d-flex gap-2">
            <button class="btn btn-success">Save</button>
            <a href="{{ route('finance.chequebooks.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <script>
        (function () {
            const bankSel = document.getElementById('BankAccountID');
            const sizeSel = document.getElementById('BookSize');
            const startInp = document.getElementById('StartNumber');
            const endInp = document.getElementById('EndNumber');
            const nextInp = document.getElementById('NextLeafNumber');
            const help = document.getElementById('rangeHelp');

            async function refreshRange() {
                const bankId = bankSel.value;
                const size = sizeSel.value || 50;
                if (!bankId) {
                    startInp.value = '';
                    endInp.value = '';
                    nextInp.value = '';
                    help.textContent = 'Select a bank account & size to see the proposed range.';
                    return;
                }
                try {
                    const url = `{{ route('finance.chequebooks.next-range', ['bankAccountId' => 'BANK_ID_PLACEHOLDER']) }}?size=${encodeURIComponent(size)}`.replace('BANK_ID_PLACEHOLDER', bankId);
                    const rsp = await fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
                    const data = await rsp.json();
                    startInp.value = data.start;
                    endInp.value = data.end;
                    nextInp.value = data.next_leaf;
                    help.textContent = `Proposed range: ${data.start} – ${data.end}`;
                } catch (e) {
                    help.textContent = 'Could not compute range. Check network or routes.';
                }
            }

            bankSel?.addEventListener('change', refreshRange);
            sizeSel?.addEventListener('change', refreshRange);

            // Initial preview if form preselected a bank
            if (bankSel?.value) refreshRange();
        })();
    </script>
@endsection
