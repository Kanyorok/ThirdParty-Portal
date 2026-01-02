@extends('layouts.app')

@section('title', 'Issue New Cheque')



@section('content')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                {{-- Header hidden or minimal --}}
                <div></div>
                {{-- <a href="{{ route('finance.cheques.index') }}" class="btn btn-outline-secondary">Back</a> --}}
            </div>

            <div class="card shadow-sm rounded-3">
                <div class="card-header bg-light py-2 px-3">
                    <h6 class="mb-0 text-muted">
                        <i class="fas fa-pen-nib text-info me-2"></i> Issue New Cheque
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('finance.cheques.issued.store') }}" method="POST" id="form-issue-cheque">
                        @csrf

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <strong><i class="fas fa-exclamation-triangle me-1"></i> Please correct the errors below:</strong>
                                <ul class="mb-0 mt-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">
                            {{-- Cheque Book & Number --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Cheque Book <span class="text-danger">*</span></label>
                                <select name="ChequeBookID" id="chequeBookSelect" class="form-select select2" required>
                                    <option value="">-- Select Cheque Book --</option>
                                    @foreach($books as $book)
                                        <option value="{{ $book->ChequeBookID }}" {{ old('ChequeBookID') == $book->ChequeBookID ? 'selected' : '' }}>
                                            {{ $book->bankAccount->bank->BankName }} ({{ $book->bankAccount->AccountNumber }})
                                            — Leaves: {{ $book->LeavesIssued }}/{{ $book->LeavesTotal }}
                                            (Next: {{ $book->NextLeafNumber }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Cheque Number / Leaf 
                                    <small class="text-muted fw-normal">(Select book first)</small>
                                </label>
                                <select name="ChequeNumber" id="chequeNumberSelect" class="form-select" disabled>
                                    <option value="">-- Select Cheque Book First --</option>
                                </select>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle"></i> Available leaves will appear after selecting a book
                                </small>
                            </div>

                            {{-- Dates & PDC --}}
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Cheque Date</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" name="ChequeDate" class="form-control" value="{{ old('ChequeDate', now()->toDateString()) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Due Date</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-calendar-check"></i></span>
                                    <input type="date" name="DueDate" class="form-control" value="{{ old('DueDate', now()->toDateString()) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Is Post-Dated?</label>
                                <select name="IsPostDated" class="form-select">
                                    <option value="0" {{ old('IsPostDated', '0') == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('IsPostDated') == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>

                            {{-- Amount & Currency --}}
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Currency <span class="text-danger">*</span></label>
                                <select name="CurrencyID" class="form-select select2" required>
                                    @foreach($currencies as $curr)
                                        <option value="{{ $curr->Id }}" {{ old('CurrencyID') == $curr->Id ? 'selected' : '' }}>
                                            {{ $curr->Code }} - {{ $curr->Name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-money-bill-wave"></i></span>
                                    <input type="number" step="0.01" name="Amount" class="form-control" value="{{ old('Amount') }}" required>
                                </div>
                            </div>

                            {{-- Party Details --}}
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Party Type</label>
                                <select name="PartyType" class="form-select select2">
                                    <option value="">-- None --</option>
                                    @foreach($chequePartyTypes as $partyType)
                                        <option value="{{ $partyType->Value }}" {{ old('PartyType') == $partyType->Value ? 'selected' : '' }}>
                                            {{ $partyType->Description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Party Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                    <input type="text" name="PartyName" class="form-control" value="{{ old('PartyName') }}" placeholder="Payee Name">
                                </div>
                            </div>

                            {{-- Reference & Narration --}}
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Reference</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-bookmark"></i></span>
                                    <input type="text" name="Reference" class="form-control" value="{{ old('Reference') }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Narration</label>
                                <textarea name="Narration" class="form-control" rows="2" placeholder="Enter narration here...">{{ old('Narration') }}</textarea>
                            </div>

                            <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                                <a href="{{ route('finance.cheques.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="btn-submit">
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    <span class="btn-text"><i class="fas fa-save me-1"></i> Issue Cheque</span>
                                </button>
                            </div>
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
            // Initialize Select2
            $('.select2').select2({
                width: '100%'
            });

            const chequeBookSelect = document.getElementById('chequeBookSelect');
            const chequeNumberSelect = document.getElementById('chequeNumberSelect');

            // Load leaves when a cheque book is selected
            $('#chequeBookSelect').on('change', function() {
                const bookId = this.value;
                
                // Reset cheque number dropdown
                chequeNumberSelect.innerHTML = '<option value="">-- Loading leaves... --</option>';
                chequeNumberSelect.disabled = true;

                if (!bookId) {
                    chequeNumberSelect.innerHTML = '<option value="">-- Select Cheque Book First --</option>';
                    return;
                }

                // Fetch available leaves via AJAX
                fetch(`/finance/chequebooks/${bookId}/leaves`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.leaves.length > 0) {
                            // Populate dropdown with available leaves
                            chequeNumberSelect.innerHTML = '<option value="">-- Select Leaf / Cheque Number --</option>';
                            
                            const nextLeafNumber = data.book.next_leaf;
                            let nextLeafFound = false;
                            
                            data.leaves.forEach(leaf => {
                                const option = document.createElement('option');
                                option.value = leaf.ChequeNumber;
                                option.textContent = `Leaf #${leaf.LeafNumber} — Cheque: ${leaf.ChequeNumber}`;
                                
                                // Auto-select the next leaf based on NextLeafNumber
                                if (leaf.LeafNumber == nextLeafNumber && !nextLeafFound) {
                                    option.selected = true;
                                    nextLeafFound = true;
                                }
                                
                                chequeNumberSelect.appendChild(option);
                            });
                            
                            // If next leaf wasn't found, select the first available leaf
                            if (!nextLeafFound && data.leaves.length > 0) {
                                chequeNumberSelect.options[1].selected = true; // Skip the placeholder
                            }
                            
                            chequeNumberSelect.disabled = false;
                        } else {
                            chequeNumberSelect.innerHTML = '<option value="">No available leaves in this book</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching leaves:', error);
                        chequeNumberSelect.innerHTML = '<option value="">Error loading leaves. Please try again.</option>';
                    });
            });

            document.getElementById('form-issue-cheque').addEventListener('submit', function() {
                const btn = document.getElementById('btn-submit');
                const spinner = btn.querySelector('.spinner-border');
                const text = btn.querySelector('.btn-text');

                btn.disabled = true;
                spinner.classList.remove('d-none');
                text.textContent = 'Processing...';
            });
        });
    </script>
@endsection
