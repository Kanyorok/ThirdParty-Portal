@extends('layouts.app')

@section('title', 'Supplier Evaluation Form')

@section('content')
  <div class="container py-4">
    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if (session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Please correct the following errors:</strong>
        <ul class="mb-0 mt-2">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <form method="POST" action="{{ route('evaluations.store') }}" id="evaluationForm" novalidate>
      @csrf
      @php
        // Ensure $rfqs is ordered newest-first for the select dropdown.
        // Support both LengthAwarePaginator and Collection/array inputs.
        $rfqsCollection = $rfqs instanceof \Illuminate\Contracts\Support\Arrayable ? collect($rfqs) : (isset($rfqs) ? $rfqs : collect());

        if (method_exists($rfqsCollection, 'sortByDesc')) {
            // Prefer CreatedOn if available, otherwise sort by Id desc
            if ($rfqsCollection->first() && isset($rfqsCollection->first()->CreatedOn)) {
                $rfqsSorted = $rfqsCollection->sortByDesc('CreatedOn');
            } else {
                $rfqsSorted = $rfqsCollection->sortByDesc('Id');
            }
        } else {
            $rfqsSorted = $rfqsCollection;
        }
      @endphp
      <!-- RFQ Section -->
      <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">RFQ No <span class="text-danger">*</span></label>
        <div class="col-sm-4">
          <select class="form-select" id="rfq-select" name="RFQId" required>
            <option value="">Select DropDown Or Search</option>
            @foreach ($rfqsSorted as $rfq)
              @php
                $plan = optional($rfq->requisition)->procurementPlan;
                $planLabel = $plan ? trim(($plan->Title ?? '') . ' - ' . ($plan->ReferenceNumber ?? '')) : '';
                $label = $rfq->RFQNumber . ($planLabel ? ' - ' . $planLabel : '');
              @endphp
              <option value="{{ $rfq->Id }}" data-comments="{{ $rfq->Comments }}">{{ $label }}</option>
            @endforeach
          </select>
          <div class="invalid-feedback">Please select an RFQ number.</div>
        </div>
        <label class="col-sm-2 col-form-label">RFQ Comments</label>
        <div class="col-sm-4">
          <input type="text" class="form-control" id="rfq-total-comments" name="RFQComments" placeholder="" readonly />
        </div>
      </div>

      <!-- Committee Info -->
      <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">Committee Member <span class="text-danger">*</span></label>
        <div class="col-sm-4">
          <input type="text" class="form-control" name="CommitteeMember" id="committee-member" readonly required />
          <div class="invalid-feedback">Committee member information is required.</div>
        </div>
        <label hidden class="col-sm-2 col-form-label">UserID <span class="text-danger">*</span></label>
        <div class="col-sm-4">
          <input type="hidden" class="form-control" name="UserID" id="user-id" readonly required />
          <div class="invalid-feedback">User ID is required.</div>
        </div>
      </div>

      <!-- Supplier Table -->
      <!-- Criteria alert (shown when no criteria/sections exist) -->
      <div id="criteria-alert" class="mb-3" style="display:none;"></div>

      <div class="table-responsive mb-4">
        <table class="table table-bordered" id="supplier-table">
          <thead class="table-light">
            <tr>
              <th>Suppliers</th>
              <th>Total Quoted</th>
              <th>Delivery Time</th>
              <th>Status</th>
              <th>Weighted Score</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="6" class="text-center">Select an RFQ to view supplier details</td>
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
          I confirm that this scoring is done independently and fairly. <span class="text-danger">*</span>
        </label>
        <div class="invalid-feedback">Please confirm the evaluation terms.</div>
      </div>

      <!-- Action Buttons -->
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Submit</button>
        <a href="{{ route('evaluations.index') }}" class="btn btn-danger">Cancel</a>
      </div>
    </form>
  </div>
@endsection

@section('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const rfqSelect = document.getElementById('rfq-select');
      const rfqComments = document.getElementById('rfq-total-comments');
      const supplierTableBody = document.querySelector('#supplier-table tbody');
      const evaluationFormsContainer = document.getElementById('evaluation-forms-container');
      const committeeMemberInput = document.querySelector('[name="CommitteeMember"]');
      const userIdInput = document.querySelector('[name="UserID"]');
      const form = document.getElementById('evaluationForm');
      const confirmCheckbox = document.getElementById('confirmCheck');
      const submitBtn = document.getElementById('submitBtn');
      // Holds per-supplier mapping of section weights and criteria ids for computing weighted totals
      const sectionMap = {};

      // Toggle submit button based on checkbox
      confirmCheckbox.addEventListener('change', function() {
        submitBtn.disabled = !this.checked;
      });

      // Custom validation for form submission
      form.addEventListener('submit', function(event) {
        let isValid = true;
        let errorMessages = [];

        // Check if RFQ is selected
        const rfqValue = rfqSelect.value;
        if (!rfqValue) {
          isValid = false;
          errorMessages.push('Please select an RFQ number.');
          rfqSelect.classList.add('is-invalid');
        } else {
          rfqSelect.classList.remove('is-invalid');
        }

        // Check if user is assigned to committee
        const committeeMember = committeeMemberInput.value.trim();
        const userId = userIdInput.value.trim();

        if (!committeeMember || committeeMember === 'You are not assigned to the committee' || committeeMember === 'Error' || !userId) {
          isValid = false;
          errorMessages.push('Committee member information is required. You may not be assigned to the evaluation committee for this RFQ.');
          committeeMemberInput.classList.add('is-invalid');
        } else {
          committeeMemberInput.classList.remove('is-invalid');
        }

        // Check if Confirmation checkbox is checked
        const confirmCheckbox = document.getElementById('confirmCheck');
        if (!confirmCheckbox.checked) {
          isValid = false;
          errorMessages.push('Please confirm that the scoring is done independently and fairly.');
          confirmCheckbox.classList.add('is-invalid');
        } else {
          confirmCheckbox.classList.remove('is-invalid');
        }

        // Check if all score inputs have values (only if RFQ is selected and has responses)
        const scoreInputs = evaluationFormsContainer.querySelectorAll(
          'input[name^="Evaluations"][name$="[Score]"]');
        let hasScoreErrors = false;
        scoreInputs.forEach(input => {
          if (!input.value || input.value < 1 || input.value > 10) {
            isValid = false;
            hasScoreErrors = true;
            input.classList.add('is-invalid');
          } else {
            input.classList.remove('is-invalid');
          }
        });
        if (hasScoreErrors) {
          errorMessages.push('Please ensure all scores are between 1 and 10.');
        }

        if (!isValid) {
          event.preventDefault();
          // Show error alert at top of form
          let alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert" id="validation-error-alert">';
          alertHtml += '<strong>Please correct the following errors:</strong><ul class="mb-0 mt-2">';
          errorMessages.forEach(msg => {
            alertHtml += '<li>' + msg + '</li>';
          });
          alertHtml += '</ul><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
          
          // Remove existing validation alert if any
          const existingAlert = document.getElementById('validation-error-alert');
          if (existingAlert) existingAlert.remove();
          
          // Insert at top of form
          form.insertAdjacentHTML('afterbegin', alertHtml);
          
          // Scroll to top to show errors
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      });

      rfqSelect.addEventListener('change', function() {
        const rfqId = this.value;

        if (!rfqId) {
          committeeMemberInput.value = '';
          userIdInput.value = '';
          return;
        }

        document.addEventListener('click', function(e) {
          if (e.target && e.target.classList.contains('view-quote-btn')) {
            const raw = e.target.getAttribute('data-response');
            const response = JSON.parse(raw.replace(/'/g, "'"));
            showQuote(response);
          }
        });

        fetch(`/procurement/rfq-committee-member/${rfqId}`)
          .then(res => res.json())
          .then(data => {
            if (!data.error) {
              committeeMemberInput.value = data.CommitteeMember;
              userIdInput.value = data.UserID;
            } else {
              committeeMemberInput.value = 'You are not assigned to the committee';
              userIdInput.value = '';
              console.warn(data.error);
            }
          })
          .catch(err => {
            console.error('Error fetching committee member info:', err);
            committeeMemberInput.value = 'Error';
            userIdInput.value = 'Error';
          });

        // Fetch RFQ responses and criteria
        fetch(`/procurement/rfq-responses/${rfqId}`)
          .then(response => response.json())
          .then(data => {
            const { responses, criteria, sectionWeights } = data;

            // Check if criteria/sections exist for the selected RFQ
            const hasCriteria = criteria && (Array.isArray(criteria) ? criteria.length > 0 : Object.keys(criteria).length > 0);
            const criteriaAlert = document.getElementById('criteria-alert');
            if (!hasCriteria) {
              // Show red alert with link to Quotation Criteria Setup
              criteriaAlert.style.display = 'block';
              criteriaAlert.innerHTML = `
                <div class="alert alert-danger" role="alert">
                  <strong>No evaluation criteria configured for this RFQ.</strong>
                  Please set up quotation criteria first in Procurement Settings.
                  <a class="btn btn-sm btn-outline-light btn-danger ms-3" href="{{ route('rfqcriteriasetup.evaluations') }}">Go to Quotation Criteria Setup</a>
                </div>
              `;
              supplierTableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Evaluation criteria missing. Please configure quotation criteria before proceeding.</td></tr>';
              evaluationFormsContainer.innerHTML = '';
              return;
            } else {
              criteriaAlert.style.display = 'none';
              criteriaAlert.innerHTML = '';
            }

            if (responses.length > 0) {
              supplierTableBody.innerHTML = '';
              evaluationFormsContainer.innerHTML = '';

              responses.forEach((response, index) => {
                const status = response.TotalPayable ? 'Approved' : 'No Reply';
                const viewQuoteButton = response.TotalPayable ?
                  `<button
                                                type="button"
                                                class="btn btn-sm btn-link view-quote-btn"
                                                data-response='${JSON.stringify(response).replace(/'/g, '"')}'>
                                            View Quote
                                        </button>` :
                  `<button type="button" class="btn btn-sm btn-link disabled">View Quote</button>`;

                const action = viewQuoteButton;

                supplierTableBody.innerHTML += `
                                <tr>
                                    <td>${
                                      response.supplier?.thirdParty?.ThirdPartyName
                                      || response.supplier?.thirdParty?.TradingName
                                      || response.supplier?.third_party?.ThirdPartyName
                                      || response.supplier?.third_party?.TradingName
                                      || response.SupplierName
                                      || 'Unknown'
                                    }</td>
                                    <td>${response.TotalPayable ? `Kes. ${response.TotalPayable}` : '-'}</td>
                                    <td>${response.DurationDays ? `${response.DurationDays} Days` : '-'}</td>
                                    <td>${status}</td>
                                    <td><strong class="supplier-total" data-supplier-id="${response.SupplierId}">0.00%</strong></td>
                                    <td>${action}</td>
                                </tr>
                            `;

                let formHtml = `
                                <div class="card mb-4">
                                    <div class="card-header">
                                        Supplier ${
                                          response.supplier?.thirdParty?.ThirdPartyName
                                          || response.supplier?.thirdParty?.TradingName
                                          || response.supplier?.third_party?.ThirdPartyName
                                          || response.supplier?.third_party?.TradingName
                                          || response.SupplierName
                                          || `#${index + 1}`
                                        }
                                        <input type="hidden" name="Evaluations[${response.SupplierId}][SupplierId]" value="${response.SupplierId}">
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Evaluation Criteria</th>
                                                    <th>Maximum Score</th>
                                                    <th>Score (1-10) <span class="text-danger">*</span></th>
                                                    <th>Comments</th>
                                                </tr>
                                            </thead>
                                            <tbody>`;

                // Ensure section map exists for this supplier
                if (!sectionMap[response.SupplierId]) sectionMap[response.SupplierId] = {};

                for (const sectionId in criteria) {
                  const rawGroup = criteria[sectionId];
                  const sectionGroup = Array.isArray(rawGroup) ? rawGroup : Object.values(rawGroup);
                  const firstItem = sectionGroup[0] || {};
                  const section = firstItem?.section;
                  const sectionName = section?.SectionName || 'Unnamed Section';
                  // Prefer sectionWeights map for accuracy; fallback to attached relation
                  const sectionWeightVal = (sectionWeights && sectionWeights[sectionId] != null)
                    ? sectionWeights[sectionId]
                    : (firstItem?.weighted_section?.Weight ?? firstItem?.weightedSection?.Weight ?? 'N/A');
                  const sectionWeight = isNaN(parseFloat(sectionWeightVal)) ? 'N/A' : parseFloat(sectionWeightVal);

                  // Initialize section mapping for computing totals later
                  sectionMap[response.SupplierId][sectionId] = {
                    weight: typeof sectionWeight === 'number' ? sectionWeight : 0,
                    criteriaIds: []
                  };

                  formHtml += `<tr class="table-secondary">
                                        <td colspan="4" class="fw-bold">
                                            ${sectionName} <span class="text-muted">(Section Weight: ${sectionWeight}%)</span>
                                        </td>
                                    </tr>`;

                  sectionGroup.forEach(criterion => {
                    const critId = criterion.id;
                    const name = criterion.criteria?.CriteriaName || 'Unnamed';
                    const maxScore = parseFloat(criterion.MaxScore).toFixed(2);
                    sectionMap[response.SupplierId][sectionId].criteriaIds.push(critId);

                    formHtml += `<tr>
                                        <td>${name}</td>
                                        <td>10</td>
                                        <td><input type="number" name="Evaluations[${response.SupplierId}][${critId}][Score]" class="form-control score-input" min="0" max="10" step="0.01" required data-supplier-id="${response.SupplierId}" data-section-id="${sectionId}" data-criteria-id="${critId}"></td>
                                        <td><input type="text" name="Evaluations[${response.SupplierId}][${critId}][Comments]" class="form-control"></td>
                                    </tr>`;
                  });
                }

                formHtml += `</tbody>
                              <tfoot>
                                <tr class="bg-light">
                                  <td colspan="4" class="text-end">
                                    <span class="me-3">Total Score: <strong class="supplier-raw-score" data-supplier-id="${response.SupplierId}">0/0</strong></span>
                                    Total Weighted Score: <strong class="supplier-total" data-supplier-id="${response.SupplierId}">0.00</strong>%
                                  </td>
                                </tr>
                              </tfoot>
                            </table>
                          </div>
                        </div>`;
                evaluationFormsContainer.innerHTML += formHtml;

                // Compute supplier total weighted score
                const computeSupplierTotal = (supplierId) => {
                  const mapping = sectionMap[supplierId] || {};
                  let totalWeighted = 0;
                  let totalRawScore = 0;
                  let totalMaxScore = 0;

                  Object.keys(mapping).forEach(secId => {
                    const { weight, criteriaIds } = mapping[secId];
                    if (!criteriaIds.length) return;
                    let sectionSum = 0;
                    criteriaIds.forEach(cId => {
                      const input = document.querySelector(`input.score-input[name="Evaluations[${supplierId}][${cId}][Score]"]`);
                      const val = parseFloat(input?.value);
                      if (!isNaN(val)) {
                          sectionSum += val;
                      }
                    });
                    
                    const maxTotal = criteriaIds.length * 10;
                    totalRawScore += sectionSum;
                    totalMaxScore += maxTotal;

                    if (maxTotal > 0) {
                      totalWeighted += (sectionSum / maxTotal) * weight;
                    }
                  });
                  
                  const totalEl = document.querySelector(`.supplier-total[data-supplier-id="${supplierId}"]`);
                  if (totalEl) totalEl.textContent = `${totalWeighted.toFixed(2)}%`;
                  
                  const rawEl = document.querySelector(`.supplier-raw-score[data-supplier-id="${supplierId}"]`);
                  if (rawEl) rawEl.textContent = `${totalRawScore}/${totalMaxScore}`;
                };

                // Bind events for this supplier inputs
                document.querySelectorAll(`input.score-input[data-supplier-id="${response.SupplierId}"]`).forEach(inp => {
                  inp.addEventListener('input', () => computeSupplierTotal(response.SupplierId));
                });

                // Initial compute
                computeSupplierTotal(response.SupplierId);
              });
            } else {
              supplierTableBody.innerHTML =
                '<tr><td colspan="5" class="text-center">No supplier details found for the selected RFQ</td></tr>';
            }
          })
          .catch(error => {
            console.error('Error fetching RFQ responses:', error);
            supplierTableBody.innerHTML =
              '<tr><td colspan="5" class="text-center">Failed to load supplier details. Please try again.</td></tr>';
          });
      });
    });

    // RFQ Comments Update
    document.addEventListener('DOMContentLoaded', function() {
      const rfqSelect = document.getElementById('rfq-select');
      const commentsInput = document.getElementById('rfq-total-comments');

      rfqSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const comments = selectedOption.getAttribute('data-comments') || '';
        commentsInput.value = comments;
      });
    });

    // Quote Modal Functionality
    function showQuote(response) {
      const modalTitle = document.getElementById('quoteModalLabel');
      const modalBody = document.getElementById('quoteModalBody');

      const supplierDisplayName =
        response.supplier?.thirdParty?.ThirdPartyName
        || response.supplier?.thirdParty?.TradingName
        || response.supplier?.third_party?.ThirdPartyName
        || response.supplier?.third_party?.TradingName
        || response.SupplierName
        || 'Supplier';
      modalTitle.textContent = `Quote Details: ${supplierDisplayName}`;

      if (!response.items || response.items.length === 0) {
        modalBody.innerHTML = '<p>No quote details available.</p>';
      } else {
        let html = `
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>UOM</th>
                            <th>Quantity</th>
                            <th>Quoted Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                `;

        response.items.forEach(item => {
          html += `
                    <tr>
                        <td>${item.ItemName || 'N/A'}</td>
                        <td>${item.uom?.Name ?? 'N/A'}</td>
                        <td>${item.Quantity || 'N/A'}</td>
                        <td>${item.QuotedPrice ? parseFloat(item.QuotedPrice).toFixed(2) : 'N/A'}</td>
                        <td>${item.TotalPayable ? parseFloat(item.TotalPayable).toFixed(2) : 'N/A'}</td>
                    </tr>
                `;
        });

        html += `</tbody></table>`;
        modalBody.innerHTML = html;
      }

      const modal = new bootstrap.Modal(document.getElementById('quoteModal'));
      modal.show();
    }
  </script>

  <!-- Quote View Modal -->
  <div class="modal fade" id="quoteModal" tabindex="-1" aria-labelledby="quoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="quoteModalLabel">Supplier Quote</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="quoteModalBody">
          <!-- Supplier quote details will be injected here -->
        </div>
      </div>
    </div>
  </div>
@endsection
