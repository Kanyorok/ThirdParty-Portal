@extends('layouts.app')

@section('title', 'New Cheque Book')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <!-- <h4>New Cheque Book</h4> -->
                <!-- <a href="{{ route('finance.chequebooks.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a> -->
            </div>

            <div class="card shadow-sm rounded-3">
                <div class="card-header bg-light py-2 px-3">
                    <h6 class="mb-0 text-muted">
                        <i class="fas fa-plus-circle text-info me-2"></i> Register New Book
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong><i class="fas fa-exclamation-triangle me-1"></i> Please correct the errors below:</strong>
                            <ul class="mb-0 mt-1">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('finance.chequebooks.store') }}" id="create-book-form">
                        @csrf
                        <div class="row g-3">
                            {{-- Bank Account --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Bank Account <span class="text-danger">*</span></label>
                                <select name="BankAccountID" id="BankAccountID" class="form-select select2" required>
                                    <option value="">-- Select Account --</option>
                                    @foreach($bankAccounts as $ba)
                                        <option value="{{ $ba->AccountID }}" @selected(old('BankAccountID', $firstId)==$ba->AccountID)>
                                            {{ optional($ba->bank)->BankName }} — {{ $ba->AccountNumber }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Book Size --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Book Size <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-list-ol"></i></span>
                                    <select name="BookSize" id="BookSize" class="form-select" required>
                                        @foreach($chequeBookSizes as $size)
                                            <option value="{{ $size->Value }}" @selected(old('BookSize',50)==$size->Value)>{{ $size->Description }} leaves</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Book Name --}}
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Book Name / Description</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-tag"></i></span>
                                    <input name="BookName" class="form-control" value="{{ old('BookName') }}"
                                           placeholder="e.g., Main Ops Book 2025-Q3">
                                </div>
                            </div>

                            <!-- <div class="col-12"><hr class="text-muted"></div> -->

                            {{-- Prefix/Suffix --}}
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Prefix</label>
                                <input name="Prefix" id="Prefix" class="form-control" value="{{ old('Prefix') }}" placeholder="e.g. A">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Suffix</label>
                                <input name="Suffix" id="Suffix" class="form-control" value="{{ old('Suffix') }}" placeholder="e.g. -25">
                            </div>

                            {{-- Range Preview (Read-only) --}}
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-2">
                                        <label class="form-label small text-muted mb-1">Proposed Range (Auto-Calculated)</label>
                                        <div class="d-flex gap-2 align-items-center">
                                            <input name="StartNumber" id="StartNumber" class="form-control form-control-sm bg-white" value="{{ old('StartNumber') }}" readonly title="Start Number">
                                            <i class="fas fa-arrow-right text-muted"></i>
                                            <input name="EndNumber" id="EndNumber" class="form-control form-control-sm bg-white" value="{{ old('EndNumber') }}" readonly title="End Number">
                                        </div>
                                        <div class="mt-2 small text-muted">
                                            Next Leaf: <strong id="NextLeafDisplay" class="text-primary">-</strong>
                                            <input type="hidden" id="NextLeafNumber" name="NextLeafNumber">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('finance.chequebooks.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="btn-submit">
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span class="btn-text"><i class="fas fa-save me-1"></i> Create Cheque Book</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bankSel = $('#BankAccountID'); // Use jQuery for Select2
            const sizeSel = document.getElementById('BookSize');
            const startInp = document.getElementById('StartNumber');
            const endInp = document.getElementById('EndNumber');
            const nextInp = document.getElementById('NextLeafNumber');
            const nextDisp = document.getElementById('NextLeafDisplay');

            // Initialize Select2
            bankSel.select2({
                width: '100%'
            });

            // Button loader
            document.getElementById('create-book-form').addEventListener('submit', function() {
                const btn = document.getElementById('btn-submit');
                const spinner = btn.querySelector('.spinner-border');
                const text = btn.querySelector('.btn-text');

                btn.disabled = true;
                spinner.classList.remove('d-none');
                text.textContent = 'Processing...';
            });

            async function refreshRange() {
                const bankId = bankSel.val(); // Get value from Select2
                const size = sizeSel.value || 50;
                if (!bankId) {
                    startInp.value = '';
                    endInp.value = '';
                    nextInp.value = '';
                    nextDisp.textContent = '-';
                    return;
                }
                try {
                    // Use placeholder replacement to handle JS variable in route
                    const url = `{{ route('finance.chequebooks.next-range', ['bankAccountId' => 'BANK_ID_PLACEHOLDER']) }}?size=${encodeURIComponent(size)}`.replace('BANK_ID_PLACEHOLDER', bankId);
                    
                    const rsp = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    
                    if (!rsp.ok) throw new Error('Network response was not ok');
                    
                    const data = await rsp.json();
                    startInp.value = data.start;
                    endInp.value = data.end;
                    nextInp.value = data.next_leaf;
                    nextDisp.textContent = data.next_leaf;
                } catch (e) {
                    console.error('Error fetching range:', e);
                    startInp.value = 'Error';
                    endInp.value = 'Error';
                }
            }

            // Listen to Select2 change event
            bankSel.on('change', refreshRange);
            sizeSel.addEventListener('change', refreshRange);

            // Initial preview if form preselected a bank
            if (bankSel.val()) refreshRange();
        });
    </script>
@endsection
