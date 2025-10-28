@extends('layouts.app')
@section('content')
    <h4 class="mb-3">Petty Cash Replenishment Wizard</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach</ul>
        </div>
    @endif

    <form id="wizardForm" method="POST" action="{{ route('finance.pettycash.wizard.store') }}">@csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Float</label>
                <select name="FloatID" id="FloatID" class="form-select" required>
                    <option value="">-- select --</option>
                    @foreach($floats as $f)
                        <option value="{{ $f->FloatID }}">{{ $f->Name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Bank Account (Payer)</label>
                <select name="BankAccountID" class="form-select" required>
                    <option value="">-- select --</option>
                    @foreach($bankAccounts as $ba)
                        <option value="{{ $ba->AccountID }}">{{ optional($ba->bank)->BankName }}
                            — {{ $ba->AccountNumber }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Batch Date</label>
                <input type="date" name="BatchDate" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
        </div>

        <hr class="my-4">

        <div id="preview" class="d-none">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Eligible Posted Disbursements</h5>
                <div><strong>Total:</strong> <span id="totalAmount">0.00</span></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>#</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th>Narration</th>
                    </tr>
                    </thead>
                    <tbody id="itemsBody"></tbody>
                </table>
            </div>
            <button class="btn btn-success">Create Replenishment</button>
        </div>

        <div class="mt-3">
            <button type="button" id="btnLoad" class="btn btn-outline-primary">Load Eligible Vouchers</button>
            <a class="btn btn-secondary" href="{{ route('finance.pettycash.index') }}">Back</a>
        </div>
    </form>

    <script>
        const btn = document.getElementById('btnLoad');
        const floatSel = document.getElementById('FloatID');
        const preview = document.getElementById('preview');
        const body = document.getElementById('itemsBody');
        const totalSpan = document.getElementById('totalAmount');
        const selectAll = document.getElementById('selectAll');

        btn.addEventListener('click', async () => {
            const fid = floatSel.value;
            if (!fid) {
                alert('Select a float first.');
                return;
            }
            const url = `{{ route('finance.pettycash.wizard.preview') }}?FloatID=${encodeURIComponent(fid)}`;
            const rsp = await fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
            const data = await rsp.json();

            body.innerHTML = '';
            let total = 0;
            data.items.forEach((it, idx) => {
                total += parseFloat(it.Amount);
                const tr = document.createElement('tr');
                tr.innerHTML = `
      <td><input type="checkbox" class="pick" name="voucher_ids[]" value="${it.VoucherID}" checked></td>
      <td>${it.VoucherID}</td>
      <td>${it.DocDate}</td>
      <td>${Number(it.Amount).toFixed(2)}</td>
      <td>${it.Reference ?? ''}</td>
      <td>${it.Narration ?? ''}</td>
    `;
                body.appendChild(tr);
            });
            totalSpan.textContent = total.toFixed(2);

            preview.classList.toggle('d-none', data.items.length === 0);
            if (data.items.length === 0) alert('No eligible posted disbursements found.');
        });

        selectAll.addEventListener('change', () => {
            document.querySelectorAll('.pick').forEach(cb => cb.checked = selectAll.checked);
        });
    </script>
@endsection
