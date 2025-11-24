@extends('layouts.app')
@section('title', 'Workflow Limit Setup')

@section('styles')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    .select2-container--bootstrap4 .select2-selection--single {
      border: 1px solid #ced4da;
      border-radius: 0.375rem;
      height: calc(2.375rem + 2px);
      padding: 0.375rem 0.75rem;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
      line-height: 1.5;
    }

    .amount-row {
      margin-bottom: 10px;
    }

    .amount-row:last-child {
      margin-bottom: 0;
    }

    #amountLimitsContainer {
      max-height: 400px;
      overflow-y: auto;
    }

    .tier-badge {
      display: inline-block;
      min-width: 60px;
      text-align: center;
    }
  </style>
@endsection

@section('content')
  {{-- SUCCESS/ERROR MESSAGES --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  @if (session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif
  
  <div class="card shadow p-4 rounded-4 mb-4">
    <h4 class="mb-4">➕ Setup Workflow Limit</h4>
    <form method="POST" action="{{ route('settings.workflow_limits.store') }}" id="workflowLimitForm">
      @csrf
      <div class="row mb-3">
        <div class="col-md-6">
          <label for="WorkFlowStageId" class="form-label">Workflow Stage <span class="text-danger">*</span></label>
          <select name="WorkFlowStageId" id="WorkFlowStageId" class="form-control select2" required>
            <option value="">-- Select Workflow Stage --</option>
            @foreach ($workflowStages as $stage)
              @if (!in_array($stage->Id, $existingWorkflowStageIds))
                <option value="{{ $stage->Id }}" 
                        data-workflow="{{ $stage->workflow_name }}"
                        data-source="{{ $stage->workflow_source }}"
                        {{ old('WorkFlowStageId') == $stage->Id ? 'selected' : '' }}>
                  {{ $stage->StageName }} ({{ $stage->workflow_name }} - {{ $stage->workflow_source }})
                </option>
              @endif
            @endforeach
          </select>
          <small class="form-text text-muted">Only AMT workflow type stages without existing limits are shown</small>
          @error('WorkFlowStageId')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label">Existing Limits for Selected Stage</label>
          <div id="existingLimitsDisplay" class="border rounded p-3 bg-light" style="min-height: 60px;">
            <small class="text-muted">Select a workflow stage to see existing limits...</small>
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="Permission" class="form-label">Permission Name <span class="text-danger">*</span></label>
          <select name="Permission" id="PermissionSelect" class="form-control select2" required>
            <option value="">Select Permission</option>
            @foreach ($permissions as $permission)
              <option value="{{ $permission->id }}" {{ old('Permission') == $permission->id ? 'selected' : '' }}>
                {{ $permission->name }}
              </option>
            @endforeach
          </select>
          @error('Permission')
            <div class="text-danger">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label">Amount Limits (Tiers) <span class="text-danger">*</span></label>
          <small class="form-text text-muted d-block mb-2">
            Add one or more amount limits. Each will create a separate approval tier.
          </small>
          
          <div id="amountLimitsContainer">
            <div class="amount-row d-flex align-items-center">
              <span class="tier-badge badge bg-primary me-2">Tier 1</span>
              <input type="number" name="AmountLimit[]" class="form-control me-2" 
                     min="0.01" step="0.01" placeholder="Enter amount" required>
              <button type="button" class="btn btn-sm btn-danger remove-amount" style="display: none;">
                ✖
              </button>
            </div>
          </div>

          <button type="button" id="addAmountBtn" class="btn btn-sm btn-success mt-2">
            ➕ Add Another Tier
          </button>

          @error('AmountLimit')
            <div class="text-danger mt-2">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="alert alert-info">
        <strong>📌 Note:</strong> 
        <ul class="mb-0 mt-2">
          <li>The selected permission will be created and linked to each workflow limit tier</li>
          <li>Tiers are assigned based on amount values (lowest amount = Tier 1)</li>
          <li>Permission format: <code>workflow_limit_[stage]_tier[X]_upto_[amount]</code></li>
          <li>After creation, assign users to these permissions to grant approval authority</li>
        </ul>
      </div>

      <button type="submit" class="btn btn-primary">💾 Save Workflow Limits</button>
    </form>
  </div>

  @if ($limits->count())
    <div class="card shadow p-4 rounded-4">
      <h5 class="mb-3">📋 Existing Workflow Limits</h5>

      <table class="table table-bordered table-hover">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Workflow Stage</th>
            <th>Workflow</th>
            <th>Source</th>
            <th>Max Amount</th>
            <th>Permission Name</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($limits as $limit)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $limit->workflow_stage->StageName ?? 'N/A' }}</td>
              <td>{{ $limit->workflow_stage->workflow->Name ?? 'N/A' }}</td>
              <td>{{ $limit->workflow_stage->workflow->Source ?? 'N/A' }}</td>
              <td><strong>{{ number_format($limit->MaxAmount, 2) }}</strong></td>
              <td>
                <code>{{ $limit->permission->name ?? 'N/A' }}</code>
              </td>
              <td>
                <button class="btn btn-sm btn-danger delete-limit-btn" 
                        data-limit-id="{{ $limit->Id }}"
                        data-stage-name="{{ $limit->workflow_stage->StageName ?? 'this stage' }}"
                        data-permission="{{ $limit->permission->name ?? 'N/A' }}">
                  🗑️ Remove
                </button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="card shadow p-4 rounded-4">
      <div class="alert alert-info">
        No workflow limits configured yet.
      </div>
    </div>
  @endif
@endsection

@section('scripts')
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function() {
      let tierCounter = 1;
      const existingLimitsData = @json($limits->groupBy('WorkFlowStageId'));

      // Initialize Workflow Stage Select2
      $('#WorkFlowStageId').select2({
        theme: 'bootstrap4',
        placeholder: '🔍 Type to search workflow stage...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 0
      });

      // Initialize Permission Select2
      $('#PermissionSelect').select2({
        theme: 'bootstrap4',
        placeholder: '🔍 Type to search permission...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 1
      });

      // Update tier badges
      function updateTierBadges() {
        $('#amountLimitsContainer .amount-row').each(function(index) {
          $(this).find('.tier-badge').text('Tier ' + (index + 1));
        });
        
        // Show/hide remove buttons
        const rowCount = $('#amountLimitsContainer .amount-row').length;
        if (rowCount > 1) {
          $('.remove-amount').show();
        } else {
          $('.remove-amount').hide();
        }
      }

      // Add new amount input
      $('#addAmountBtn').click(function() {
        tierCounter++;
        const newRow = `
          <div class="amount-row d-flex align-items-center">
            <span class="tier-badge badge bg-primary me-2">Tier ${tierCounter}</span>
            <input type="number" name="AmountLimit[]" class="form-control me-2" 
                   min="0.01" step="0.01" placeholder="Enter amount" required>
            <button type="button" class="btn btn-sm btn-danger remove-amount">
              ✖
            </button>
          </div>
        `;
        $('#amountLimitsContainer').append(newRow);
        updateTierBadges();
      });

      // Remove amount input
      $(document).on('click', '.remove-amount', function() {
        $(this).closest('.amount-row').remove();
        updateTierBadges();
      });

      // Show existing limits when stage is selected
      $('#WorkFlowStageId').on('change', function() {
        const stageId = $(this).val();
        const displayDiv = $('#existingLimitsDisplay');
        
        if (!stageId) {
          displayDiv.html('<small class="text-muted">Select a workflow stage to see existing limits...</small>');
          return;
        }

        // Find limits for this stage
        const stageLimits = existingLimitsData[stageId] || [];
        
        if (stageLimits.length === 0) {
          displayDiv.html('<small class="text-success">✓ No existing limits for this stage</small>');
        } else {
          let html = '<small class="text-warning d-block mb-2">⚠️ Existing limits for this stage:</small><ul class="mb-0 small">';
          stageLimits.forEach(function(limit, index) {
            html += `<li><strong>Tier ${index + 1}:</strong> ${parseFloat(limit.MaxAmount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</li>`;
          });
          html += '</ul>';
          displayDiv.html(html);
        }
      });

      // Form validation
      $('#workflowLimitForm').on('submit', function(e) {
        const amounts = [];
        let hasDuplicates = false;
        
        $('input[name="AmountLimit[]"]').each(function() {
          const val = parseFloat($(this).val());
          if (amounts.includes(val)) {
            hasDuplicates = true;
          }
          amounts.push(val);
        });

        if (hasDuplicates) {
          e.preventDefault();
          alert('⚠️ Duplicate amounts detected! Each tier must have a unique amount value.');
          return false;
        }
      });

      // Initialize
      updateTierBadges();

      // AJAX Delete Functionality
      $(document).on('click', '.delete-limit-btn', function() {
        const btn = $(this);
        const limitId = btn.data('limit-id');
        const stageName = btn.data('stage-name');
        const permission = btn.data('permission');
        const row = btn.closest('tr');

        if (!confirm(`Are you sure you want to delete the limit for ${stageName}?\n\nPermission: ${permission}\n\nUsers with this permission will no longer be able to approve at this amount level.`)) {
          return;
        }

        // Disable button and show loading state
        btn.prop('disabled', true).html('⏳ Deleting...');

        $.ajax({
          url: '{{ route("settings.workflow_limits.destroy", ":id") }}'.replace(':id', limitId),
          type: 'DELETE',
          data: {
            _token: '{{ csrf_token() }}'
          },
          success: function(response) {
            // Fade out and remove the row
            row.fadeOut(400, function() {
              $(this).remove();
              
              // Update row numbers
              updateRowNumbers();
              
              // Check if table is empty
              checkIfTableEmpty();
            });

            // Show success message
            showAlert('success', response.message || 'Workflow limit deleted successfully.');
          },
          error: function(xhr) {
            // Re-enable button
            btn.prop('disabled', false).html('🗑️ Remove');
            
            let errorMessage = 'Failed to delete workflow limit.';
            
            if (xhr.responseJSON && xhr.responseJSON.error) {
              errorMessage = xhr.responseJSON.error;
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
              errorMessage = xhr.responseJSON.message;
            }
            
            showAlert('danger', errorMessage);
          }
        });
      });

      // Update row numbers after deletion
      function updateRowNumbers() {
        $('.table tbody tr').each(function(index) {
          $(this).find('td:first').text(index + 1);
        });
      }

      // Check if table is empty and show message
      function checkIfTableEmpty() {
        if ($('.table tbody tr').length === 0) {
          const emptyMessage = `
            <tr>
              <td colspan="7" class="text-center py-4">
                <div class="alert alert-info mb-0">
                  No workflow limits configured yet.
                </div>
              </td>
            </tr>
          `;
          $('.table tbody').html(emptyMessage);
        }
      }

      // Show alert messages
      function showAlert(type, message) {
        const alertHtml = `
          <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        `; 
        
        // Remove existing alerts
        $('.alert').not('.alert-info:has(strong)').remove();
        
        // Add new alert at the top
        $('h4.mb-4').first().before(alertHtml);
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
          $('.alert-dismissible').fadeOut(400, function() {
            $(this).remove();
          });
        }, 5000);
        
        // Scroll to top to show the alert
        $('html, body').animate({ scrollTop: 0 }, 300);
      }
    });
  </script>
@endsection