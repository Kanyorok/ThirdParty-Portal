@extends('layouts.app')
@section('title', 'Tender Invitation Response')
@section('content')
<div class="container mt-4">
    <form action="{{ route('tenderresponse.storeResponse') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!input type="hidden" name="TenderId" value="1"> <!-- Replace with dynamic ID -->
        <!input type="hidden" name="SupplierId" value="1"> <!-- Replace with dynamic ID -->

        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tenderSelect" class="form-label">Tender Reference</label>
                    <select class="form-select @error('TenderId') is-invalid @enderror" id="tenderSelect" name="TenderId" required>
                        <option selected disabled>-- Select Tender --</option>
                        @foreach ($tenders as $tender)
                            <option value="{{ $tender->Id }}">{{ $tender->TenderNo }}-{{ $tender->Title }}</option>
                        @endforeach
                    </select>
                    @error('TenderId')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="supplierSelect" class="form-label">Supplier Name</label>
                    <select class="form-select @error('SupplierId') is-invalid @enderror" id="supplierSelect" name="SupplierId" required>
                        <option selected disabled>-- Select Supplier --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->Id }}">{{ $supplier->SupplierName }}</option>
                        @endforeach
                    </select>
                    @error('supplier_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Your Response:</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="ResponseStatus" id="accept" value="Accepted" required>
                <label class="form-check-label" for="accept">Accept</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="ResponseStatus" id="decline" value="Declined">
                <label class="form-check-label" for="decline">Decline</label>
            </div>
        </div>

        <div class="mb-3">
            <label for="DeclineReason" class="form-label">Remarks (Optional)</label>
            <textarea class="form-control" name="DeclineReason" id="DeclineReason" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label for="ConfirmationAttachment" class="form-label">Upload Confirmation (Optional)</label>
            <input type="file" class="form-control" name="ConfirmationAttachment" id="ConfirmationAttachment">
        </div>

        <button type="submit" class="btn btn-primary">Submit Response</button>
    </form>
</div>
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2
        $('#supplierSelect').select2({
            placeholder: '-- Select Supplier --',
            allowClear: true,
            width: '100%' // Ensure it takes full width
        });

        // Use jQuery for event binding as Select2 uses it
        $('#tenderSelect').on('change', function() {
            const tenderId = $(this).val();
            const supplierSelect = $('#supplierSelect');

            // Clear existing options
             supplierSelect.empty().append('<option selected disabled>Loading...</option>');
             supplierSelect.trigger('change');

            fetch(`{{ url('procurement/tenderresponse/invited-suppliers') }}/${tenderId}`)
                .then(response => response.json())
                .then(data => {
                    supplierSelect.empty();
                    supplierSelect.append('<option selected disabled>-- Select Supplier --</option>');
                    
                    if (data.length === 0) {
                        supplierSelect.append('<option disabled>No invited suppliers found</option>');
                    } else {
                        data.forEach(supplier => {
                            // Create new option: new Option(text, value, defaultSelected, selected)
                            const option = new Option(supplier.SupplierName, supplier.Id, false, false);
                            supplierSelect.append(option);
                        });
                    }
                    supplierSelect.trigger('change');
                })
                .catch(error => {
                    console.error('Error:', error);
                    supplierSelect.empty().append('<option selected disabled>Error fetching suppliers</option>');
                });
        });
    });
</script>
@endsection
@endsection
