@extends('layouts.app')
@section('title', 'Edit GL Posting Map')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4">✏️ Edit GL Posting Map</h4>

        <form method="POST" action="{{ route('glpostingmap.update', $mapping->Id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Module</label>
                <select name="ModuleID" class="form-select" required>
                    @foreach ($modules as $module)
                        <option
                            value="{{ $module->ModuleID }}" {{ $mapping->ModuleID == $module->ModuleID ? 'selected' : '' }}>
                            {{ $module->Name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Transaction Type</label>
                <select name="TransactionType" class="form-select" required>
                    @foreach ($transactionTypes as $type)
                        <option
                            value="{{ $type->Id }}" {{ $mapping->TransactionTypeID == $type->Id ? 'selected' : '' }}>
                            {{ $type->Name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Debit GL Account</label>
                <select name="DebitGLAccountID" class="form-select" required>
                    @foreach ($glaccounts as $account)
                        <option
                            value="{{ $account->Id }}" {{ $mapping->DebitGLAccountID == $account->Id ? 'selected' : '' }}>
                            {{ $account->GLName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Credit GL Account</label>
                <select name="CreditGLAccountID" class="form-select" required>
                    @foreach ($glaccounts as $account)
                        <option
                            value="{{ $account->Id }}" {{ $mapping->CreditGLAccountID == $account->Id ? 'selected' : '' }}>
                            {{ $account->GLName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" name="IsActive" class="form-check-input" id="IsActive"
                           {{ $mapping->IsActive ? 'checked' : '' }}>
                    <label class="form-check-label" for="IsActive">
                        Active
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-success">
                <i class="fas fa-save me-1"></i> Update Mapping
            </button>
            <a href="{{ route('glpostingmap.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
        </form>
    </div>
@endsection
