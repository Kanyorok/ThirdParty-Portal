@extends('layouts.app')
@section('title', 'Edit Clarification Response')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Clarification Response</h2>
        <a href="{{ route('tenderclarification.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Clarification Details</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('tenderclarification.update') }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="clarification_id" value="{{ $clarification->ClarificationID ?? '' }}">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="tenderInfo" class="form-label">Tender</label>
                        <input type="text" class="form-control" id="tenderInfo" 
                               value="{{ $clarification->tenderID->TenderNo ?? 'N/A' }} - {{ $clarification->tenderID->Title ?? 'No Title' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="supplierName" class="form-label">From Supplier</label>
                        <input type="text" class="form-control" id="supplierName" 
                               value="{{ $clarification->supplier_name ?? 'Unknown Supplier' }}" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="questionText" class="form-label">Question</label>
                    <div class="form-control-plaintext border p-2 bg-light" style="min-height: 80px;">
                        {{ $clarification->Question ?? 'No question available' }}
                    </div>
                    <small class="text-muted">Asked on: {{ $clarification->QuestionDate ? $clarification->QuestionDate->format('F d, Y \a\t h:i A') : 'Unknown date' }}</small>
                </div>

                <div class="mb-3">
                    <label for="responseText" class="form-label">Response <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('answer') is-invalid @enderror" 
                              id="responseText" 
                              name="answer" 
                              rows="5" 
                              placeholder="Enter or update the official response..." 
                              required>{{ old('answer', $clarification->Answer ?? '') }}</textarea>
                    @error('answer')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Minimum 10 characters, maximum 2000 characters.</small>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="is_published_to_all" 
                           id="publishToAll" 
                           value="1" 
                           {{ old('is_published_to_all', $clarification->ISPUBLISHEDTOALL ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="publishToAll">
                        <strong>Publish this clarification for all bidders</strong>
                    </label>
                    <div class="form-text">
                        If checked, this clarification and response will be visible to all suppliers participating in this tender.
                    </div>
                </div>

                @if($clarification->AnswerDate)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        This clarification was previously answered on {{ $clarification->AnswerDate->format('F d, Y \a\t h:i A') }}.
                        You are now editing the existing response.
                    </div>
                @endif

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Response
                    </button>
                    <a href="{{ route('tenderclarification.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
