@extends('layouts.app')
@section('title', 'Approval Workflow Details')

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
.final-stage-row {
    background-color: #d4edda !important;
}
</style>
@endsection

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">View Approval Workflow</h4>

    <dl class="row">
        <dt class="col-sm-3">Name</dt>
        <dd class="col-sm-9">{{ $approval->Name }}</dd>

        <dt class="col-sm-3">Description</dt>
        <dd class="col-sm-9">{{ $approval->Description }}</dd>

        <dt class="col-sm-3">Document Type</dt>
        <dd class="col-sm-9">{{ class_basename($approval->Source) }}</dd>

        <dt class="col-sm-3">Has Final Stage</dt>
        <dd class="col-sm-9">
            <span class="badge {{ $approval->IsFinalStage ? 'bg-success' : 'bg-secondary' }}">
                {{ $approval->IsFinalStage ? 'Yes' : 'No' }}
            </span>
        </dd>

        <dt class="col-sm-3">Created By</dt>
        <dd class="col-sm-9">{{ optional($approval->createdByUser)->Name ?? 'N/A' }}</dd>

        <dt class="col-sm-3">Created On</dt>
        <dd class="col-sm-9">{{ \Carbon\Carbon::parse($approval->CreatedOn)->format('d-m-Y H:i') }}</dd>
    </dl>

    <hr>

    {{-- Add Stage Form --}}
    <div class="card shadow p-4 rounded-4 mt-4" id="stageFormCard">
        <h4 class="mb-4">➕ Add Approval Stage</h4>

        @if($approval->IsFinalStage)
        <div class="alert alert-warning">
            <strong>⚠️ Notice:</strong> This workflow already has a final stage. You cannot add more stages until you remove the final stage designation.
        </div>
        @else
        <form id="stageForm">
            @csrf
            <input type="hidden" name="WorkFlowId" value="{{ $approval->Id }}">

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Stage Name</label>
                    <input type="text" name="StageName" class="form-control" required placeholder="e.g. Committee Stage">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Escalation Limit (days)</label>
                    <input type="number" name="EscalationLimit" class="form-control" required min="1">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Approval Type</label>
                    <select name="WorkFlowTypeId" id="TypeID" class="form-select" required>
                        <option value="">-- Select Type --</option>
                        @foreach($approvalTypes as $type)
                        <option value="{{ $type->Id }}" data-code="{{ $type->TypeID }}">{{ $type->TypeID }} - {{ $type->Name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- AMT Limit --}}
            <div class="row mb-3" id="limitGroup" style="display: none;">
                <div class="col-md-4">
                    <label class="form-label">Approval Limit (AMOUNT only)</label>
                    <select name="WorkFlowLimitId" id="WorkflowLimitID" class="form-control select2">
                        <option value="">Select WorkFlow Limit</option>
                        @foreach($workflowLimits as $limit)
                        <option value="{{ $limit->Id }}">{{ $limit->Source }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Count --}}
            <div class="row mb-3" id="countGroup" style="display: none;">
                <div class="col-md-4">
                    <label class="form-label">Approver Count (COUNT only)</label>
                    <input type="number" name="Count" class="form-control" min="1">
                </div>
            </div>

            {{-- Permission --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Permission Name</label>
                    <select name="PermissionId" id="PermissionId" class="form-control select2" required>
                        <option value="">Select Permission</option>
                        @foreach($permissions as $permission)
                        <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end pt-2">
                    <div class="form-check">
                        <input type="hidden" name="IsFinalStage" value="0">
                        <input type="checkbox" class="form-check-input" name="IsFinalStage" value="1" id="IsFinalStage">
                        <label class="form-check-label" for="IsFinalStage">
                            <strong>Mark as Final Stage</strong> (No more stages can be added after this)
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">💾 Save Stage</button>
            </div>
        </form>
        @endif
    </div>

    {{-- Added Stages Table --}}
    <div class="mt-5">
        <h5 class="mb-3">🧾 Added Stages</h5>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Role</th>
                    <th>Cut-off</th>
                    <th>Final</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="stagesTable">
                @forelse ($stages as $index => $stage)
                @php
                    // Check if this is the last stage (final stage)
                    $isLastStage = $index === count($stages) - 1;
                    $isFinalStage = $approval->IsFinalStage && $isLastStage;
                @endphp
                <tr id="stage-{{ $stage->Id }}" class="{{ $isFinalStage ? 'final-stage-row' : '' }}">
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $stage->StageName }}
                        @if($isFinalStage)
                        <span class="badge bg-success ms-2">FINAL</span>
                        @endif
                    </td>
                    <td>{{ $stage->type_name->TypeID ?? '-' }}</td>
                    <td>{{ $stage->role_name ?? '-' }}</td>
                    <td>{{ $stage->MaxAmount ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $isFinalStage ? 'bg-success' : 'bg-secondary' }}">
                            {{ $isFinalStage ? 'Yes' : 'No' }}
                        </span>
                    </td>
                    <td>
                        <form class="deleteStageForm" data-id="{{ $stage->Id }}" data-is-final="{{ $isFinalStage ? '1' : '0' }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr id="noStages">
                    <td colspan="7" class="text-center text-muted">No stages added yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    let workflowHasFinalStage = {{ $approval->IsFinalStage ? 'true' : 'false' }};

    // Toggle AMT/CNT fields
    function toggleFields() {
        const selected = $('#TypeID').find(':selected').data('code');
        $('#limitGroup').toggle(selected === 'AMT');
        $('#countGroup').toggle(selected === 'CNT');
    }
    $('#TypeID').on('change', toggleFields);
    toggleFields();

    // Enable select2 for static selects
    $('.select2').select2({ theme: 'bootstrap4', placeholder: '🔍 Type to search...', allowClear: true });

    // Renumber table rows
    function renumberStages() {
        $('#stagesTable tr:not(#noStages)').each(function(i){
            $(this).find('td:first').text(i+1);
        });
    }

    // Update form visibility based on final stage status
    function updateFormVisibility(hasFinalStage) {
        workflowHasFinalStage = hasFinalStage;
        if (hasFinalStage) {
            $('#stageForm').hide();
            if ($('#stageFormCard .alert-warning').length === 0) {
                $('#stageFormCard').prepend(`
                    <div class="alert alert-warning">
                        <strong>⚠️ Notice:</strong> This workflow already has a final stage. You cannot add more stages until you remove the final stage designation.
                    </div>
                `);
            }
        } else {
            $('#stageForm').show();
            $('#stageFormCard .alert-warning').remove();
        }
    }

    // AJAX form submit
    $('#stageForm').on('submit', function(e){
        e.preventDefault();
        
        if (workflowHasFinalStage) {
            alert('This workflow already has a final stage. Cannot add more stages.');
            return;
        }

        const isFinalStage = $('#IsFinalStage').is(':checked');
        
        if (isFinalStage) {
            if (!confirm('Are you sure you want to mark this as the FINAL stage? You will not be able to add more stages after this.')) {
                return;
            }
        }

        $.ajax({
            url: "{{ route('settings.workflow_stages.store') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function(res){
                if(res.status === 'success'){
                    const stage = res.stage;
                    $('#noStages').remove();

                    const rowClass = stage.IsFinalStage ? 'final-stage-row' : '';
                    const finalBadge = stage.IsFinalStage ? '<span class="badge bg-success ms-2">FINAL</span>' : '';
                    const finalBadgeCell = stage.IsFinalStage ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>';

                    $('#stagesTable').append(`
                        <tr id="stage-${stage.Id}" class="${rowClass}">
                            <td></td>
                            <td>
                                ${stage.StageName}
                                ${finalBadge}
                            </td>
                            <td>${stage.type_name?.TypeID ?? '-'}</td>
                            <td>${stage.role_name ?? '-'}</td>
                            <td>${stage.MaxAmount ?? '-'}</td>
                            <td>${finalBadgeCell}</td>
                            <td>
                                <form class="deleteStageForm" data-id="${stage.Id}" data-is-final="${stage.IsFinalStage ? '1' : '0'}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    `);

                    renumberStages();
                    $('#stageForm')[0].reset();
                    $('#PermissionId').val(null).trigger('change');
                    
                    // If this was a final stage, hide the form
                    if (stage.IsFinalStage) {
                        updateFormVisibility(true);
                    }
                    
                    alert(res.message);
                } else {
                    alert(res.message || 'Failed to create stage');
                }
            },
            error: function(xhr){ 
                const errorMsg = xhr.responseJSON?.message || 'Error creating stage';
                alert(errorMsg);
            }
        });
    });

    // AJAX delete stage
    $(document).on('submit', '.deleteStageForm', function(e){
        e.preventDefault();
        
        const isFinal = $(this).data('is-final') == '1';
        let confirmMsg = 'Are you sure you want to delete this stage?';
        
        if (isFinal) {
            confirmMsg = 'This is the FINAL stage. Deleting it will allow you to add more stages. Are you sure?';
        }
        
        if(!confirm(confirmMsg)) return;

        let id = $(this).data('id');
        $.ajax({
            url: `/settings/workflow_stages/${id}`,
            method: 'POST',
            data: $(this).serialize(),
            success: function(res){
                if(res.status === 'success'){
                    $(`#stage-${id}`).remove();
                    renumberStages();

                    // If the deleted stage was final, show the form again
                    if (isFinal) {
                        updateFormVisibility(false);
                    }

                    if($('#stagesTable tr:not(#noStages)').length === 0){
                        $('#stagesTable').append('<tr id="noStages"><td colspan="7" class="text-center text-muted">No stages added yet.</td></tr>');
                    }
                    alert(res.message);
                } else {
                    alert(res.message || 'Failed to delete stage');
                }
            },
            error: function(){ alert('Error deleting stage'); }
        });
    });

});
</script>
@endsection