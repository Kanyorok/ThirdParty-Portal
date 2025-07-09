@extends('layouts.app')

@section('title', 'Supplier Evaluation Form')

@section('content')
<div class="container py-4">
    <form method="POST" action="{{ route('evaluations.store') }}">
        @csrf

        <!-- Header -->
        <div class="mb-4">
            <h3>Supplier Evaluation Form</h3>
        </div>

        <!-- Committee Info -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">Committee Member</label>
            <div class="col-sm-4">
                <input type="text" class="form-control" name="CommitteeMember" placeholder="John Doe"/>
            </div>
            <label class="col-sm-2 col-form-label">UserID</label>
            <div class="col-sm-4">
                <input type="text" class="form-control" name="UserID" placeholder="Enter User ID"/>
            </div>
        </div>

        <!-- RFQ Section -->
        <div class="mb-3 row">
            <label class="col-sm-2 col-form-label">RFQ No</label>
            <div class="col-sm-4">
                <select class="form-select" id="rfq-select" name="RFQId" required>
                    <option value="">Select DropDown Or Search</option>
                    @foreach($rfqs as $rfq)
                        <option value="{{ $rfq->Id }}" data-comments="{{ $rfq->Comments }}">{{ $rfq->RFQNumber }}</option>
                    @endforeach
                </select>
            </div>
            <label class="col-sm-2 col-form-label">RFQ Comments</label>
            <div class="col-sm-4">
                <input type="text" class="form-control" id="rfq-total-comments" name="RFQComments" placeholder="Load from DB" readonly />
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
        <div id="evaluation-forms-container">
            <!-- Evaluation forms will be dynamically added here -->
        </div>

        <!-- Confirmation Checkbox -->
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" value="1" id="confirmCheck" name="Confirmation" required>
            <label class="form-check-label" for="confirmCheck">
                ✅ I confirm that this scoring is done independently and fairly.
            </label>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Submit</button>
            <button type="submit" class="btn btn-secondary">Save</button>
            <a href="{{ route('evaluations.index') }}" class="btn btn-danger">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rfqSelect = document.getElementById('rfq-select');
            const rfqComments = document.getElementById('rfq-total-comments');
            const supplierTableBody = document.querySelector('#supplier-table tbody');
            const evaluationFormsContainer = document.getElementById('evaluation-forms-container');

            rfqSelect.addEventListener('change', function () {
                const selectedOption = rfqSelect.options[rfqSelect.selectedIndex];
                rfqComments.value = selectedOption.getAttribute('data-comments') || '';

                const rfqId = this.value;
                supplierTableBody.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';
                evaluationFormsContainer.innerHTML = '';

                if (!rfqId) {
                    supplierTableBody.innerHTML = '<tr><td colspan="5" class="text-center">Select an RFQ to view supplier details</td></tr>';
                    return;
                }

                fetch(`/procurement/rfq-responses/${rfqId}`)
                    .then(response => response.json())
                    .then(data => {
                        const { responses, criteria } = data;

                        if (responses.length > 0) {
                            supplierTableBody.innerHTML = '';
                            evaluationFormsContainer.innerHTML = '';

                            responses.forEach((response, index) => {
                                const status = response.TotalPayable ? 'Submitted' : 'No Reply';
                                const action = response.TotalPayable
                                    ? `<a href="#" class="btn btn-sm btn-link">View Quote</a>`
                                    : `<a href="#" class="btn btn-sm btn-link disabled">View Quote</a>`;

                                supplierTableBody.innerHTML += `
                            <tr>
                                <td>${response.SupplierName || 'Unknown'}</td>
                                <td>${response.TotalPayable ? `Kes. ${response.TotalPayable}` : '-'}</td>
                                <td>${response.DurationDays ? `${response.DurationDays} Days` : '-'}</td>
                                <td>${status}</td>
                                <td>${action}</td>
                            </tr>
                        `;

                                let formHtml = `
                            <div class="card mb-4">
                                <div class="card-header">
                                    Supplier ${response.SupplierName || `#${index + 1}`}
                                    <input type="hidden" name="Evaluations[${response.SupplierId}][SupplierId]" value="${response.SupplierId}">
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
                                        <tbody>`;

                                // Loop through grouped criteria by section
                                for (const sectionId in criteria) {
                                    const sectionGroup = criteria[sectionId];
                                    const sectionName = sectionGroup[0]?.section?.SectionName || 'Unnamed Section';

                                    formHtml += `<tr class="table-secondary">
                                            <td colspan="4" class="fw-bold">${sectionName}</td>
                                         </tr>`;

                                    sectionGroup.forEach(criterion => {
                                        const critId = criterion.CriteriaID;
                                        const name = criterion.criteria?.CriteriaName || 'Unnamed';
                                        const maxScore = parseFloat(criterion.MaxScore).toFixed(2);

                                        formHtml += `<tr>
                                    <td>${name}</td>
                                    <td>${maxScore}%</td>
                                    <td><input type="number" name="Evaluations[${response.SupplierId}][${critId}][Score]" class="form-control" min="1" max="10" required></td>
                                    <td><input type="text" name="Evaluations[${response.SupplierId}][${critId}][Comments]" class="form-control"></td>
                                </tr>`;
                                    });
                                }

                                formHtml += `</tbody></table></div></div>`;
                                evaluationFormsContainer.innerHTML += formHtml;
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
@endsection
