@extends('layouts.app')
@section('title', 'RFQ Sections Setup')
@section('content')

  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addRfqSectionModal">
        + Assign Sections to RFQ
      </a>
    </div>

    <!-- Table Placeholder -->
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>RFQ No</th>
            <th>Section Count</th>
            <th>Criteria Items</th>
            <th>Total Weight</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($rfqs as $index => $rfq)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $rfq->RFQNumber }}</td>
              <td>{{ $rfq->sections_count ?? 0 }}</td>
              <td>
                <a href="{{ route('rfqcriterias.show', $rfq->Id) }}"
                  class="btn btn-sm btn-outline-{{ $rfq->criteria_count > 0 ? 'primary' : 'danger' }}">
                  {{ $rfq->criteria_count ?? 0 }}
                  <i class="fa fa-eye"
                    style="font-size: 18px; color: {{ $rfq->criteria_count > 0 ? 'rgb(63, 63, 252)' : 'red' }}"></i>

                  @if (($rfq->criteria_count ?? 0) == 0)
                    <span class="badge bg-danger ms-1" data-bs-toggle="tooltip" title="No criteria assigned">!</span>
                  @endif
                </a>
              </td>
              <td>{{ $rfq->sections->sum('Weight') ?? 0 }}%</td>
              <td>
                <a href="{{ route('rfqcriterias.show', $rfq->Id) }}" class="btn btn-sm btn-outline-primary">Setup
                  Criteria</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add RFQ Section Modal -->
  <div class="modal fade" id="addRfqSectionModal" tabindex="-1" aria-labelledby="addRfqSectionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content rounded-3 shadow">
        <div class="modal-header">
          <h5 class="modal-title">Assign Sections to RFQ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('rfqcriteriasetup.evaluations.save') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-bold">Select RFQ</label>
              <select name="rfq_id" class="form-select" required>
                <option disabled selected>-- Select RFQ --</option>
                @foreach ($rfqList as $rfq)
                  <option value="{{ $rfq->Id }}">{{ $rfq->RFQNumber }}</option>
                @endforeach
              </select>
            </div>

            <table class="table table-bordered">
              <thead class="table-light">
                <tr>
                  <th>Include</th>
                  <th>Section</th>
                  <th>Weight (%)</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($sections as $section)
                  <tr>
                    <td>
                      <input type="checkbox" name="sections[]" value="{{ $section->Id }}">
                    </td>
                    <td>{{ $section->SectionName }}</td>
                    <td>
                      <input type="number" class="form-control" name="weights[{{ $section->Id }}]" step="0.01"
                        value="0.00">
                    </td>
                  </tr>
                @endforeach
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2" class="text-end fw-bold">Total</td>
                  <td><strong id="totalWeight">0.00</strong>%</td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Assign</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- JS to validate 100% total weight -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const modal = document.getElementById('addRfqSectionModal');
      const form = modal.querySelector('form');
      const totalWeightDisplay = document.getElementById('totalWeight');

      function updateTotal() {
        let total = 0;
        form.querySelectorAll('tbody tr').forEach(row => {
          const checkbox = row.querySelector('input[type="checkbox"]');
          const weightInput = row.querySelector('input[type="number"]');
          if (checkbox.checked) {
            weightInput.disabled = false;
            total += parseFloat(weightInput.value) || 0;
          } else {
            weightInput.disabled = true;
          }
        });
        totalWeightDisplay.textContent = total.toFixed(2);
        return total;
      }

      form.querySelectorAll('input[type="checkbox"], input[type="number"]').forEach(input => {
        input.addEventListener('change', updateTotal);
      });

      form.addEventListener('submit', function(e) {
        const total = updateTotal();
        if (total.toFixed(2) !== '100.00') {
          e.preventDefault();
          alert(`Total weight must be exactly 100.00%. Current total: ${total.toFixed(2)}%`);
        }
      });

      updateTotal();
    });
  </script>

@endsection
