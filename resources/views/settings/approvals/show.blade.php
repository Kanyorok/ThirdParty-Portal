@extends('layouts.app')
@section('title', 'Approval Workflow Details')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">View Approval Workflow</h4>
        <a href="{{ route('settings.workflows.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <dl class="row">
        <dt class="col-sm-3">Name</dt>
        <dd class="col-sm-9">{{ $approval->Name }}</dd>

        <dt class="col-sm-3">Description</dt>
        <dd class="col-sm-9">{{ $approval->Description }}</dd>



        <dt class="col-sm-3">Has Final Stage</dt>
        <dd class="col-sm-9">
            <span class="badge {{ !empty($approval->FinalStage) ? 'bg-success' : 'bg-secondary' }}">
                {{ !empty($approval->FinalStage) ? 'Yes' : 'No' }}
            </span>
        </dd>



        <dt class="col-sm-3">Created On</dt>
        <dd class="col-sm-9">{{ \Carbon\Carbon::parse($approval->CreatedOn)->format('d-m-Y H:i') }}</dd>
    </dl>

    <hr>

    {{-- Add Stage Form --}}
    <div class="card shadow p-4 rounded-4 mt-4" id="stageFormCard">
        <h4 class="mb-4">➕ Add Approval Stage</h4>

        @if(!empty($approval->FinalStage))
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
                    {{--
                        <select name="WorkFlowLimitId" id="WorkflowLimitID" class="form-control select2">
                            <option value="">Select WorkFlow Limit</option>
                            @foreach($workflowLimits as $limit)
                                <option value="{{ $limit->Id }}">{{ $limit->Source }}</option>
                    @endforeach
                    </select>
                    --}}
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

    {{-- Approvers Modal --}}
    <div class="modal fade" id="approversModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approvers for Stage</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul id="approversList" class="list-group">
                        <li class="list-group-item text-center">Loading...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Added Stages Table --}}
    <div class="mt-5">
        <h5 class="mb-3">🧾 Added Stages</h5>
        <div class="table-responsive">
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
                        <td>{{ $stage->type_name->Name ?? $stage->type_name->TypeID ?? '-' }}</td>
                        {{-- uses ?-> to safely access properties --}}
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($stage->permission?->roles ?? [] as $role)
                                    <span class="badge bg-info text-dark">{{ $role->name }}</span>
                                @empty
                                    -
                                @endforelse
                            </div>
                        </td>
                        <td>{{ $stage->EscalationLimit ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $isFinalStage ? 'bg-success' : 'bg-secondary' }}">
                                {{ $isFinalStage ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex">
                                <button type="button" class="btn btn-sm btn-info view-approvers me-1" data-id="{{ $stage->Id }}" title="View Approvers">👀</button>
                                <form class="deleteStageForm" data-id="{{ $stage->Id }}" data-is-final="{{ $isFinalStage ? '1' : '0' }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                                </form>
                            </div>
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
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Safer boolean export
    let workflowHasFinalStage = @json(!empty($approval->FinalStage));
    const workflowId = {{ $approval->Id }};
    const STORAGE_KEY = 'workflow-stage-form-' + workflowId;

        workflowId: workflowId,
        hasFinalStage: workflowHasFinalStage,
        finalStageName: @json($approval->FinalStage ?? null)
    });

    // Function to reload workflow state from server
    function reloadWorkflowState() {
        
        return $.ajax({
            url: '/settings/workflows/' + workflowId + '/state',
            method: 'GET',
            cache: false,
            headers: {
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            },
            success: function(res) {
                if (res.status === 'success') {
                    workflowHasFinalStage = res.workflow.IsFinalStage;
                    
                        hasFinalStage: workflowHasFinalStage,
                        stageCount: res.stages.length
                    });
                    
                    // Update form visibility
                    updateFormVisibility(workflowHasFinalStage);
                    
                    // Rebuild stages table
                    rebuildStagesTable(res.stages);
                    
                    return res;
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to reload workflow state:', error);
            }
        });
    }

    // Function to rebuild stages table
    function rebuildStagesTable(stages) {
        const tbody = $('#stagesTable');
        tbody.empty();
        
        if (stages.length === 0) {
            tbody.append(
                '<tr id="noStages">' +
                '<td colspan="7" class="text-center text-muted">No stages added yet.</td>' +
                '</tr>'
            );
            return;
        }
        
        stages.forEach(function(stage, index) {
            const rowClass = stage.IsFinalStage ? 'final-stage-row' : '';
            const finalBadge = stage.IsFinalStage ? '<span class="badge bg-success ms-2">FINAL</span>' : '';
            const finalBadgeCell = stage.IsFinalStage ? 
                '<span class="badge bg-success">Yes</span>' : 
                '<span class="badge bg-secondary">No</span>';
            
            const typeText = stage.type ? (stage.type.Name || stage.type.TypeID || '-') : '-';
            const escalationText = stage.EscalationLimit || '-';
            let roleDisplay = '-';
            if (stage.role_name && stage.role_name !== '-') {
                roleDisplay = '<div class="d-flex flex-wrap gap-1">';
                stage.role_name.split(',').forEach(function(r) {
                    if(r.trim() !== '') {
                        roleDisplay += '<span class="badge bg-info text-dark">' + r.trim() + '</span>';
                    }
                });
                roleDisplay += '</div>';
            }

            tbody.append(
                '<tr id="stage-' + stage.Id + '" class="' + rowClass + '">' +
                '<td>' + (index + 1) + '</td>' +
                '<td>' + stage.StageName + ' ' + finalBadge + '</td>' +
                '<td>' + typeText + '</td>' +
                '<td>' + roleDisplay + '</td>' +
                '<td>' + escalationText + '</td>' +
                '<td>' + finalBadgeCell + '</td>' +
                '<td>' +
                '<div class="d-flex">' +
                '<button type="button" class="btn btn-sm btn-info view-approvers me-1" ' +
                'data-id="' + stage.Id + '" title="View Approvers">👀</button>' +
                '<form class="deleteStageForm" data-id="' + stage.Id + '" ' +
                'data-is-final="' + (stage.IsFinalStage ? '1' : '0') + '">' +
                '@csrf @method("DELETE")' +
                '<button type="submit" class="btn btn-sm btn-danger">🗑️</button>' +
                '</form>' +
                '</div>' +
                '</td>' +
                '</tr>'
            );
        });
        
    }

    // Enhanced save form state
    function saveFormState() {
        try {
            var permData = $('#PermissionId').select2('data');
            var permissionText = (permData && permData.length > 0 && permData[0].text) ? permData[0].text : '';

            var limitData = $('#WorkflowLimitID').select2('data');
            var workFlowLimitText = (limitData && limitData.length > 0 && limitData[0].text) ? limitData[0].text : '';

            const formData = {
                StageName: $('input[name="StageName"]').val(),
                EscalationLimit: $('input[name="EscalationLimit"]').val(),
                WorkFlowTypeId: $('#TypeID').val(),
                WorkFlowLimitId: $('#WorkflowLimitID').val(),
                Count: $('input[name="Count"]').val(),
                PermissionId: $('#PermissionId').val(),
                IsFinalStage: $('#IsFinalStage').is(':checked'),
                PermissionText: permissionText,
                WorkFlowLimitText: workFlowLimitText,
                lastUpdated: Date.now()
            };
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(formData));
        } catch (error) {
            console.error('Error saving form state:', error);
        }
    }

    function loadFormState() {
        try {
            const saved = sessionStorage.getItem(STORAGE_KEY);
            if (saved) {
                const formData = JSON.parse(saved);

                $('input[name="StageName"]').val(formData.StageName || '');
                $('input[name="EscalationLimit"]').val(formData.EscalationLimit || '');

                if (formData.WorkFlowTypeId) {
                    $('#TypeID').val(formData.WorkFlowTypeId).trigger('change');
                }

                setTimeout(function() {
                    if (formData.WorkFlowLimitId) {
                        $('#WorkflowLimitID').val(formData.WorkFlowLimitId).trigger('change');
                    }
                    if (formData.Count) {
                        $('input[name="Count"]').val(formData.Count);
                    }
                    if (formData.PermissionId) {
                        $('#PermissionId').val(formData.PermissionId).trigger('change');
                    }
                    $('#IsFinalStage').prop('checked', !!formData.IsFinalStage);
                }, 300);
            }
        } catch (error) {
            console.error('Error loading form state:', error);
        }
    }

    function clearFormState() {
        try {
            sessionStorage.removeItem(STORAGE_KEY);
        } catch (error) {
            console.error('Error clearing form state:', error);
        }
    }

    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: '🔍 Type to search...',
        allowClear: true
    });

    setTimeout(loadFormState, 100);

    let saveTimeout;
    function debouncedSave() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(saveFormState, 500);
    }

    $('#stageForm').on('input change keyup', '.form-control, .form-select, .form-check-input', debouncedSave);
    $('#stageForm').on('select2:select select2:unselect', debouncedSave);

    function toggleFields() {
        const selected = $('#TypeID').find(':selected').data('code');
        $('#limitGroup').toggle(selected === 'AMT');
        $('#countGroup').toggle(selected === 'CNT');
        debouncedSave();
    }

    $('#TypeID').on('change', toggleFields);
    setTimeout(toggleFields, 200);

    function renumberStages() {
        $('#stagesTable tr:not(#noStages)').each(function(i) {
            $(this).find('td:first').text(i + 1);
        });
    }

    function updateFormVisibility(hasFinalStage) {
        workflowHasFinalStage = hasFinalStage;
        
        if (hasFinalStage) {
            $('#stageForm').hide();
            if ($('#stageFormCard .alert-warning').length === 0) {
                $('#stageFormCard').prepend(
                    '<div class="alert alert-warning">' +
                    '<strong>⚠️ Notice:</strong> This workflow already has a final stage. ' +
                    'You cannot add more stages until you remove the final stage designation.' +
                    '</div>'
                );
            }
        } else {
            $('#stageForm').show();
            $('#stageFormCard .alert-warning').remove();
        }
    }

    // Initial form visibility check
    updateFormVisibility(workflowHasFinalStage);

    // Form submission
    $('#stageForm').on('submit', function(e) {
        e.preventDefault();

        if (workflowHasFinalStage) {
            alert('This workflow already has a final stage. Cannot add more stages.');
            return;
        }

        const isFinalStage = $('#IsFinalStage').is(':checked');

        if (isFinalStage) {
            if (!confirm('Are you sure you want to mark this as the FINAL stage? ' +
                'You will not be able to add more stages after this.')) {
                return;
            }
        }

        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm"></span> Saving...'
        );

        $.ajax({
            url: "{{ route('settings.workflow_stages.store') }}",
            method: "POST",
            data: $(this).serialize(),
            cache: false,
            success: function(res) {
                submitBtn.prop('disabled', false).html(originalText);

                if (res.status === 'success') {
                    alert(res.message);
                    
                    // Reset form
                    $('#stageForm')[0].reset();
                    $('#PermissionId').val(null).trigger('change');
                    $('#WorkflowLimitID').val(null).trigger('change');
                    clearFormState();
                    
                    // Reload entire workflow state
                    // reloadWorkflowState().then(function() {
                    // });
                    window.location.reload();
                } else {
                    alert(res.message || 'Failed to create stage');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);
                var errorMsg = 'Error creating stage';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert(errorMsg);
                console.error('Stage creation error:', xhr);
            }
        });
    });

    // Delete stage
    $(document).on('submit', '.deleteStageForm', function(e) {
        e.preventDefault();

        const isFinal = $(this).data('is-final') == '1';
        let confirmMsg = 'Are you sure you want to delete this stage?';

        if (isFinal) {
            confirmMsg = 'This is the FINAL stage. Deleting it will allow you to add more stages. Are you sure?';
        }

        if (!confirm(confirmMsg)) return;

        const deleteBtn = $(this).find('button');
        const originalText = deleteBtn.html();
        deleteBtn.prop('disabled', true).html('...');

        let id = $(this).data('id');
        const token = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: '/settings/workflow-stages/' + id,
            method: 'POST',
            data: $(this).serialize() + '&_method=DELETE&_token=' + encodeURIComponent(token),
            cache: false,
            success: function(res) {
                deleteBtn.prop('disabled', false).html(originalText);

                if (res.status === 'success') {
                    alert(res.message);
                    
                    // Reload entire workflow state
                    // reloadWorkflowState().then(function() {
                    // });
                    window.location.reload();
                } else {
                    alert(res.message || 'Failed to delete stage');
                }
            },
            error: function(err) {
                deleteBtn.prop('disabled', false).html(originalText);
                console.error('Delete error:', err);
                alert('Error deleting stage');
            }
        });
    });

    // Save form state periodically
    $(window).on('beforeunload', saveFormState);
    $(document).on('click', 'a', saveFormState);
    setInterval(saveFormState, 30000);

    // View approvers
    $(document).on('click', '.view-approvers', function() {
        const stageId = $(this).data('id');
        const modal = new bootstrap.Modal(document.getElementById('approversModal'));
        const list = $('#approversList');

        list.html('<li class="list-group-item text-center">Loading...</li>');
        modal.show();

        $.get('/settings/workflow/stage/' + stageId + '/approvers', function(res) {
            list.empty();
            if (res.users && res.users.length > 0) {
                $.each(res.users, function(i, user) {
                    list.append(
                        '<li class="list-group-item">' +
                        user.Name + ' <small class="text-muted">(' + user.Email + ')</small>' +
                        '</li>'
                    );
                });
            } else {
                list.append('<li class="list-group-item text-center text-muted">No users found.</li>');
            }
        }).fail(function() {
            list.html('<li class="list-group-item text-center text-danger">Failed to load approvers.</li>');
        });
    });

});
</script>
@endpush