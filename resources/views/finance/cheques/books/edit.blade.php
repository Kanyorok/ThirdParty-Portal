@extends('layouts.app')

@section('title', 'Edit Cheque Book')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                {{-- Header hidden as per create view style preference, or kept minimal --}}
                <div></div> 
                {{-- <a href="{{ route('finance.chequebooks.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a> --}}
            </div>

            <div class="card shadow-sm rounded-3">
                <div class="card-header bg-light py-2 px-3">
                    <h6 class="mb-0 text-muted">
                        <i class="fas fa-edit text-info me-2"></i> Edit Cheque Book #{{ $row->ChequeBookID }}
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

                    <form method="POST" action="{{ route('finance.chequebooks.update', $row->ChequeBookID) }}" id="edit-book-form">
                        @csrf @method('PUT')
                        <div class="row g-3">
                            {{-- Bank Account --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Bank Account <span class="text-danger">*</span></label>
                                <select name="BankAccountID" class="form-select select2" required>
                                    @foreach($bankAccounts as $ba)
                                        <option value="{{ $ba->AccountID }}" @selected(old('BankAccountID',$row->BankAccountID)==$ba->AccountID)>
                                            {{ optional($ba->bank)->BankName }} — {{ $ba->AccountNumber }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Active Status --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-toggle-on"></i></span>
                                    <select name="IsActive" class="form-select">
                                        @foreach($chequeBookStatuses as $status)
                                            <option value="{{ $status->Value }}" @selected(old('IsActive',$row->IsActive)==$status->Value)>
                                                {{ $status->Description }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Book Name --}}
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Book Name / Description</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-tag"></i></span>
                                    <input name="BookName" class="form-control" value="{{ old('BookName',$row->BookName) }}"
                                           placeholder="e.g., Main Ops Book 2025-Q3">
                                </div>
                            </div>

                            <div class="col-12"><hr class="text-muted"></div>

                            {{-- Prefix/Suffix --}}
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Prefix</label>
                                <input name="Prefix" class="form-control" value="{{ old('Prefix',$row->Prefix) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Suffix</label>
                                <input name="Suffix" class="form-control" value="{{ old('Suffix',$row->Suffix) }}">
                            </div>

                            {{-- Range (Editable) --}}
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-2">
                                        <label class="form-label small text-muted mb-1">Leaf Range & Next Leaf</label>
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <label class="small text-muted">Start</label>
                                                <input type="number" name="StartNumber" class="form-control form-control-sm"
                                                       value="{{ old('StartNumber',$row->StartNumber) }}" required>
                                            </div>
                                            <div class="col-4">
                                                <label class="small text-muted">End</label>
                                                <input type="number" name="EndNumber" class="form-control form-control-sm"
                                                       value="{{ old('EndNumber',$row->EndNumber) }}" required>
                                            </div>
                                            <div class="col-4">
                                                <label class="small text-muted">Next</label>
                                                <input type="number" name="NextLeafNumber" class="form-control form-control-sm border-primary"
                                                       value="{{ old('NextLeafNumber',$row->NextLeafNumber) }}" required>
                                            </div>
                                        </div>
                                        <div class="mt-1 small text-muted">
                                            <i class="fas fa-info-circle me-1"></i> Ensure "Next" is within Start/End.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('finance.chequebooks.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="btn-submit">
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span class="btn-text"><i class="fas fa-save me-1"></i> Update Cheque Book</span>
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
            // Initialize Select2
            $('.select2').select2({
                width: '100%'
            });

            // Button loader
            document.getElementById('edit-book-form').addEventListener('submit', function() {
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
