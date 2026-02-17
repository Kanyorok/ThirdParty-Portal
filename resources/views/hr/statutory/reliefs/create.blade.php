@extends('layouts.app')

@section('title', 'New Tax Relief')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create Tax Relief</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.statutory.reliefs.index') }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('hr.statutory.reliefs.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code *</label>
                        <input type="text" name="Code" class="form-control" value="{{ old('Code') }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Relief Type *</label>
                        <select name="ReliefType" id="ReliefType" class="form-select" required>
                            <option value="Fixed" @selected(old('ReliefType', 'Fixed') === 'Fixed')>Fixed</option>
                            <option value="Percentage" @selected(old('ReliefType') === 'Percentage')>Percentage of Deduction</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Apply Stage *</label>
                        <select name="ApplyStage" class="form-select" required>
                            <option value="PostTax" @selected(old('ApplyStage', 'PostTax') === 'PostTax')>Post-tax (reduce PAYE)</option>
                            <option value="PreTax" @selected(old('ApplyStage') === 'PreTax')>Pre-tax (reduce taxable income)</option>
                        </select>
                    </div>
                    <div class="col-md-4" id="fixedAmountGroup">
                        <label class="form-label">Amount *</label>
                        <input type="number" step="0.01" name="Amount" class="form-control" value="{{ old('Amount', 0) }}">
                    </div>
                    <div class="col-md-4" id="deductionGroup">
                        <label class="form-label">Deduction *</label>
                        <select name="DeductionID" class="form-select">
                            <option value="">Select deduction</option>
                            @foreach($deductions as $deduction)
                                <option value="{{ $deduction->Id }}" @selected(old('DeductionID') == $deduction->Id)>
                                    {{ $deduction->Name }} ({{ $deduction->Code }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Used when relief type is Percentage.</div>
                    </div>
                    <div class="col-md-4" id="rateGroup">
                        <label class="form-label">Relief Rate (%) *</label>
                        <input type="number" step="0.01" name="ReliefRate" class="form-control" value="{{ old('ReliefRate') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective From *</label>
                        <input type="date" name="EffectiveFrom" class="form-control" value="{{ old('EffectiveFrom') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective To</label>
                        <input type="date" name="EffectiveTo" class="form-control" value="{{ old('EffectiveTo') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <input type="text" name="Description" class="form-control" value="{{ old('Description') }}">
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Save Relief</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('ReliefType');
        const fixedGroup = document.getElementById('fixedAmountGroup');
        const deductionGroup = document.getElementById('deductionGroup');
        const rateGroup = document.getElementById('rateGroup');

        const toggleGroup = (group, enabled) => {
            if (!group) return;
            group.style.display = enabled ? '' : 'none';
            group.querySelectorAll('input, select').forEach((el) => {
                el.disabled = !enabled;
            });
        };

        const syncGroups = () => {
            const isFixed = (typeSelect.value || 'Fixed') === 'Fixed';
            toggleGroup(fixedGroup, isFixed);
            toggleGroup(deductionGroup, !isFixed);
            toggleGroup(rateGroup, !isFixed);
        };

        typeSelect.addEventListener('change', syncGroups);
        syncGroups();
    });
</script>
@endpush
