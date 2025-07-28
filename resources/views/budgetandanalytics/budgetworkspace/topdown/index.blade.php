@extends('layouts.app')
@section('title', 'General Ledger Entries')
@section('content')

    <div class="container mt-4">
        {{-- <h4 class="mb-3">📊 Budget Data Entry</h4> --}}

        <form method="POST" action="{{ route('topdownallocation.display') }}">
            @csrf
            @method('POST')
            <!-- Budget selection -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Select Budget</label>
                    <select class="form-select" name="BudgetID" required>
                        <option value="" selected disabled>-- Select Budget --</option>
                        @foreach ($budgets as $item)
                            <option value="{{ $item->Id }}">{{ $item->Name }} - {{ $item->From.' '.$item->To }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Select Branch</label>
                    <select class="form-select" name="BranchID" required>
                        <option value="" selected disabled>-- Select Branch --</option>
                        <option value="all" name="all">All</option>
                        @foreach ($branches as $item)
                            <option value="{{ $item->Id }}">{{ $item->Name }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- <div class="col-md-4">
                    <label class="form-label">Select Format</label>
                    <select class="form-select" id="format" name="format" required>
                        <option value="m" selected>Monthly</option>
                        <option value="q">Quarterly</option>
                    </select>
                </div> --}}
                <div class="col-md-12 d-flex align-items-end mt-5">
                    <button type="submit" class="btn btn-primary w-100"
                            onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Loading Please wait...'; this.form.submit(); }">
                        💾 Submit
                    </button>
                </div>
            </div>

            <!-- MONTHLY FORMAT -->
            {{-- <div id="monthly_form" class="table-responsive mb-4">
                <div style="overflow-x: auto;">
                    <table class="table table-bordered table-striped table-sm" style="min-width: 1600px;">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Account ID</th>
                                <th>Budget Line</th>
                                @for ($m = 1; $m <= 12; $m++)
                                    <th>Month {{ $m }}</th>
                                @endfor
                                <th>Budget 2025</th>
                                <th>Actuals Dec 2024</th>
                                <th>% Change</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($glsMaster as $item)
                                <tr>
                                    <td>{{ $item->AccountID_CBS }}</td>
                                    <td>{{ $item->Description }}</td>

                                    @for ($m = 1; $m <= 12; $m++)
                                        <td>
                                            <input type="number"
                                                name="monthly_allocations[{{ $item->AccountID_CBS }}][{{ $m }}]"
                                                class="form-control"
                                                placeholder="e.g. 100000000"
                                                inputmode="numeric"
                                                style="min-width: 120px;" />
                                        </td>
                                    @endfor

                                    <td>
                                        <input class="form-control total-input" style="min-width: 120px;" value="435000020" readonly />
                                    </td>
                                    <td>
                                        <input class="form-control" style="min-width: 120px;" value="4000000" readonly />
                                    </td>
                                    <td>
                                        <input class="form-control" style="min-width: 110px;" value="108.75%" readonly />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div> --}}

            {{-- <button type="submit" class="btn btn-success" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit(); }">
            💾 Save Budget
            </button> --}}
        </form>
    </div>


    <script>
        // document.addEventListener('DOMContentLoaded', function () {
        //     const table = document.querySelector('#monthly_form table');

        //     table.querySelectorAll('tbody tr').forEach(row => {
        //         const monthlyInputs = row.querySelectorAll('input[name^="monthly_allocations"]');
        //         const totalInput = row.querySelector('.total-input');

        //         function calculateRowTotal() {
        //             let total = 0;
        //             monthlyInputs.forEach(input => {
        //                 const val = parseFloat(input.value) || 0;
        //                 total += val;
        //             });
        //             totalInput.value = total.toFixed(2);
        //         }

        //         monthlyInputs.forEach(input => {
        //             input.addEventListener('input', calculateRowTotal);
        //         });

        //         // Optional: calculate on load if you have default values
        //         calculateRowTotal();
        //     });
        // });
    </script>

@endsection
