@extends('layouts.app')
@section('title', 'Add GL Mapping')

@section('content')
    <div class="card shadow rounded-4 border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-info">
                <i class="fas fa-link me-2"></i> Add GL Posting Mapping
            </h5>
        </div>

        <div class="card-body px-4 py-4">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('glpostingmap.store') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="ModuleID" class="form-label">📦 Module</label>
                        <select name="ModuleID" id="ModuleID" class="form-select" required>
                            <option value="">-- Select Module --</option>
                            @foreach($modules as $module)
                                <option value="{{ $module->ModuleID }}">{{ $module->Name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="TransactionType" class="form-label">🔄 Transaction Type</label>
                        <select name="TransactionType" id="TransactionType" class="form-select" required disabled>
                            <option selected disabled value="">-- Select Transaction Type --</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="DebitGLAccountID" class="form-label">➖ Debit GL Account</label>
                        <select class="form-select" name="DebitGLAccountID" id="DebitGLAccountID" required>
                            <option selected disabled value="">-- Select Debit Account --</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="CreditGLAccountID" class="form-label">➕ Credit GL Account</label>
                        <select class="form-select" name="CreditGLAccountID" id="CreditGLAccountID" required>
                            <option selected disabled value="">-- Select Credit Account --</option>
                        </select>
                    </div>
                </div>

                {{-- Add more mapping fields here if needed --}}

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('glpostingmap.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-info" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}">
                        <i class="fas fa-save"></i> Save Mapping
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const transactionSelect = document.getElementById('TransactionType');
        const moduleSelect = document.getElementById('ModuleID');

        moduleSelect.addEventListener('change', function() {
            const selectedModule = this.value;
            transactionSelect.disabled = !selectedModule;

            transactionSelect.innerHTML = '<option selected disabled value="">Loading...</option>';

            if (selectedModule) {
                fetch(`/finance/finance/transactions/${selectedModule}`)
                    .then(response => response.json())
                    .then(data => {
                        transactionSelect.innerHTML = '<option selected disabled value="">-- Select Transaction Type --</option>';
                        data.forEach(mod => {
                            transactionSelect.innerHTML += `<option value="${mod.Id}">${mod.transactions.Name}</option>`;
                        });
                        transactionSelect.disabled = false;
                    });
            }
        });

        const debitGL = document.getElementById('DebitGLAccountID');
        const creditGL = document.getElementById('CreditGLAccountID');
        let glData = [];

        async function loadAccounts() {
            try {
                const resp = await fetch("{{ route('glpostingmap.list') }}", { headers: { 'Accept': 'application/json' } });
                if (!resp.ok) throw new Error('HTTP error ' + resp.status);
                glData = await resp.json();
                populateAccounts();
            } catch (err) {
                console.error("Failed to load GL accounts:", err);
            }
        }

        function populateAccounts() {
            debitGL.innerHTML = '<option selected disabled value="">-- Select Debit Account --</option>';
            creditGL.innerHTML = '<option selected disabled value="">-- Select Credit Account --</option>';

            glData.forEach(item => {
                const option = `<option value="${item.Id}">${item.GLName}</option>`;
                debitGL.innerHTML += option;
                creditGL.innerHTML += option;
            });
        }

        function updateOptions(source, target) {
            const selected = source.value;
            [...target.options].forEach(option => {
                option.disabled = option.value && option.value === selected;
            });
        }

        debitGL.addEventListener('change', () => updateOptions(debitGL, creditGL));
        creditGL.addEventListener('change', () => updateOptions(creditGL, debitGL));

        loadAccounts();
    </script>
@endsection
