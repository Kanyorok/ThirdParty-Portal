@extends('layouts.app')
@section('title', 'Process Payment')

@section('content')
    <div class="card shadow  rounded-4">
        <div class="card-header bg-light py-1 px-3">
            <h6 class="mb-0 text-muted"><i class="fas fa-credit-card"></i> Submit Payment</h6>
        </div>
        <div class="card-body">
            <form>
        <div class="mb-3">
            <label class="form-label" for="voucherRef">Voucher</label>
            <select class="form-control" name="VoucherRef" id="voucherRef" onchange="fillInvoice()">
                <option disabled selected value="">--Select Voucher--</option>
                @foreach( $vouchers as $item)
                    <option value="{{ $item->Id}}"
                            data-amount="{{$item->invoice->InvoiceAmount}}"
                            data-invoice="{{ $item->invoice->InvoiceNumber}}"
                            data-paid="{{ $item->TotAmnt}}">
                        {{ $item->VoucherNo}}({{$item->TotAmnt}})
                    </option>
                @endforeach
                {{-- <option>VCH-2025-0001 (KES 100,000.00)</option> --}}
            </select>
        </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Invoice</label>
                        <input class="form-control" type="text" id="InvoiceRef" name="InvoiceRef"
                               placeholder="Based on the Selected voucher" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="invoice amnt">Invoice Amount</label>
                        <input class="form-control" type="text" id="InvoiceDesc" name="InvoiceAmount"
                               placeholder="e.g. 10,000 of 89,000 paid" readonly>
                    </div>
        </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Payment Date</label>
                        <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
                    <div class="col-md-6">
                        <label>Bank Account</label>
                        <select class="form-control">
                            <option>KCB Main Account</option>
                            <option>Co-operative Bank of Kenya</option>
                            <option>Equity Bank</option>
                </select>
            </div>
        </div>

                <div class="row mb-3">
                    <div>
                        <label>Narration</label>
                        <textarea type="text" class="form-control" placeholder="First installment payment"></textarea>
            </div>
                </div>

                <div class="text-end">
                    <button class="btn btn-success">Submit Payment</button>
        </div>
            </form>
        </div>
</div>

    <script>
        function fillInvoice() {
            const select = document.getElementById('voucherRef');
            const selectedOption = select.options[select.selectedIndex];

            const invoiceNo = selectedOption.getAttribute('data-invoice');
            const amount = selectedOption.getAttribute('data-amount');
            const paid = selectedOption.getAttribute('data-paid');

            document.getElementById('InvoiceRef').value = invoiceNo ?? '';
            document.getElementById('InvoiceDesc').value = `${paid.toLocaleString()} of ${amount.toLocaleString()} paid`;
        }
    </script>

@endsection
