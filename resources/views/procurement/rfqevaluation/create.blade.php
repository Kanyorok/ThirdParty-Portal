@extends('layouts.app')
@section('title', 'Supplier Evaluation Form')
@section('content')
<div class="container py-4">

    <!-- Header -->
    <div class="mb-4">
        <h3>Supplier Evaluation Form</h3>
    </div>

    <!-- Committee Info -->
    <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">Committee Member</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="John Doe" readonly />
        </div>
        <label class="col-sm-2 col-form-label">UserID</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="auto-populated" readonly />
        </div>
    </div>

    <!-- RFQ Section -->
    <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">RFQ No</label>
        <div class="col-sm-4">
            <select class="form-select" id="rfq-select">
                <option value="">Select DropDown Or Search</option>
                @foreach($rfqs as $rfq)
                    <option value="{{ $rfq->Id }}" data-comments="{{ $rfq->Comments }}">{{ $rfq->RFQNumber }}</option>
                @endforeach
            </select>
        </div>
        <label class="col-sm-2 col-form-label">RFQ Comments</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" id="rfq-total-comments" placeholder="Load from DB" readonly />
        </div>
    </div>

    <!-- Supplier Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered" id="supplier-table">
            <thead class="table-light">
                <tr>
                    <th>Suppliers</th>
                    <th>Total Quoted</th>
                    <th>Delivery Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="text-center">Select an RFQ to view supplier details</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Evaluation Forms -->
    @for ($i = 1; $i <= 3; $i++)
    <div class="card mb-4">
        <div class="card-header">
            Supplier {{ $i }}
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Evaluation Criteria</th>
                        <th>Weight (%)</th>
                        <th>Score (1-10)</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Technical Quality</td>
                        <td>40%</td>
                        <td><input type="number" class="form-control" min="1" max="10"></td>
                        <td><input type="text" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Pricing</td>
                        <td>30%</td>
                        <td><input type="number" class="form-control" min="1" max="10"></td>
                        <td><input type="text" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Delivery Time</td>
                        <td>20%</td>
                        <td><input type="number" class="form-control" min="1" max="10"></td>
                        <td><input type="text" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Past Experience</td>
                        <td>10%</td>
                        <td><input type="number" class="form-control" min="1" max="10"></td>
                        <td><input type="text" class="form-control"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endfor

    <!-- Confirmation Checkbox -->
    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" value="" id="confirmCheck">
        <label class="form-check-label" for="confirmCheck">
            ✅ I confirm that this scoring is done independently and fairly.
        </label>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="button" class="btn btn-secondary">Save</button>
        <button type="button" class="btn btn-danger">Cancel</button>
    </div>

</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rfqSelect = document.getElementById('rfq-select');
        const rfqComments = document.getElementById('rfq-total-comments');
        const supplierTableBody = document.querySelector('#supplier-table tbody');

        // Update RFQ Comments when an RFQ is selected
        rfqSelect.addEventListener('change', function () {
            const selectedOption = rfqSelect.options[rfqSelect.selectedIndex];
            const comments = selectedOption.getAttribute('data-comments') || '';

            // Update the RFQ Comments input field
            rfqComments.value = comments;

            // Fetch supplier details for the selected RFQ
            const rfqId = this.value;

            // Clear the table body
            supplierTableBody.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';

            if (!rfqId) {
                supplierTableBody.innerHTML = '<tr><td colspan="5" class="text-center">Select an RFQ to view supplier details</td></tr>';
                return;
            }

            // Fetch RFQ responses for the selected RFQ
            fetch(`/rfq-responses/${rfqId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        supplierTableBody.innerHTML = ''; // Clear the loading message

                        data.forEach(response => {
                            const status = response.TotalPayable ? 'Submitted' : 'No Reply';
                            const action = response.TotalPayable
                                ? `<a href="#" class="btn btn-sm btn-link">View Quote</a>`
                                : `<a href="#" class="btn btn-sm btn-link disabled">View Quote</a>`;

                            supplierTableBody.innerHTML += `
                                <tr>
                                    <td>${response.SupplierName ? response.SupplierName : 'Unknown'}</td>
                                    <td>${response.TotalPayable ? `Kes. ${response.TotalPayable}` : '-'}</td>
                                    <td>${response.DurationDays ? `${response.DurationDays} Days` : '-'}</td>
                                    <td>${status}</td>
                                    <td>${action}</td>
                                </tr>
                            `;
                        });
                    } else {
                        supplierTableBody.innerHTML = '<tr><td colspan="5" class="text-center">No supplier details found for the selected RFQ</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching RFQ responses:', error);
                    supplierTableBody.innerHTML = '<tr><td colspan="5" class="text-center">Failed to load supplier details. Please try again.</td></tr>';
                });
        });
    });
</script>