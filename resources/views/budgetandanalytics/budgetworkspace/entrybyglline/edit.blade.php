@extends('layouts.app')
@section('title', 'Edit Budget Line Entry')
@section('content')

    <div class="card mt-4">
        {{--        <div class="card-header bg-dark text-white">✏️ Edit Budget Line Entry</div>--}}
        <div class="card-body">
            <form action="{{ route('entrybyglline.update', $entry->Id) }}" method="POST">
                @csrf
                @method('PUT')

                <p class="text-muted">
                    Use this form to edit the selected budget line entry. The Budget cannot be changed.
                </p>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Budget</label>
                        <input type="text" class="form-control" value="{{ $entry->budget->Name }}" disabled>
                        <input type="hidden" name="BudgetID" value="{{ $entry->BudgetID }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Branch</label>
                        <select class="form-select" name="BranchID" required>
                            <option selected disabled>-- Select Branch --</option>
                            @foreach ($branches as $item)
                                <option
                                    value="{{ $item->Id }}" {{$entry->BudgetID == $item->Id ? 'selected' : ''}}>{{ $item->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Budget Line</label>
                    <select name="BudgetLineID" class="form-select" required>
                        <option disabled selected>-- Select Budget Line --</option>
                        @foreach ($budgetLines as $item)
                            <option
                                value="{{ $item->Id }}" {{$entry->BudgetLineID == $item->Id ? 'selected' : ''}}>{{ $item->LineName }}</option>
                        @endforeach
                    </select>
                    @error('BudgetLineID') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <!-- Manual Entry -->
                <div class="manual-entry">
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" min="0.00" id="totalAmount" name="Amount" class="form-control"
                               value="{{ $entry->Amount }}" required>
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label">Remarks (optional)</label>
                    <textarea class="form-control" name="Comments" rows="2">{{ $entry->Comments }}</textarea>
                </div>

                <!-- Monthly Allocation Fields -->
                <div id="monthlyAllocationSection">
                    <h6>Monthly Allocation(s)</h6>
                    <div class="row">
                        @php $months = range(1, 12); @endphp
                        @foreach($months as $key)
                            <div class="col-md-3 mb-2">
                                <label>Month {{ $key }}</label>
                                <input type="number"
                                       value="{{ old('monthly_allocations.'.$key, $entry->allocations->where('Month', str_pad($key, 2, '0', STR_PAD_LEFT))->first()->Allocation ?? 0) }}"
                                       min="0" name="monthly_allocations[{{ $key }}]" class="form-control"
                                       placeholder="0.00" step="0.01" required>
                            </div>
                        @endforeach
                    </div>
                    @error('monthly_allocations') <small class="text-danger">{{ $message }}</small> @enderror
                    <div class="mt-3 mb-3">
                        <strong>Total Allocation:</strong> <span id="totalAllocation" class=" text-danger">0.00</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-success"
                        onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit(); }">
                    💾 Update Budget Line
                </button>
            </form>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inputs = document.querySelectorAll('input[name^="monthly_allocations"]');
            const totalDisplay = document.getElementById('totalAllocation');

            function calculateTotal() {
                let total = 0;
                inputs.forEach(input => {
                    const value = parseFloat(input.value) || 0;
                    total += value;
                });
                totalDisplay.textContent = total.toFixed(2);
            }

            // Attach input event listener to all monthly input fields
            inputs.forEach(input => {
                input.addEventListener('input', calculateTotal);
            });

            // Initial calculation in case values are prefilled
            calculateTotal();
        });
    </script>

@endsection
