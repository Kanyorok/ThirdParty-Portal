@extends('layouts.app')
@section('title', 'Approval Workflow Details')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">👁️ View Approval Workflow</h4>

    <dl class="row">
        <dt class="col-sm-3">Name</dt>
        <dd class="col-sm-9">{{ $approval->Name }}</dd>

        <dt class="col-sm-3">Description</dt>
        <dd class="col-sm-9">{{ $approval->Description }}</dd>

        <dt class="col-sm-3">Document Type</dt>
        <dd class="col-sm-9">{{ class_basename($approval->Source) }}</dd>

        <dt class="col-sm-3">Created By</dt>
        <dd class="col-sm-9">{{ optional($approval->createdByUser)->Name ?? 'N/A' }}</dd>

        <dt class="col-sm-3">Created On</dt>
        <dd class="col-sm-9">{{ \Carbon\Carbon::parse($approval->CreatedOn)->format('d-m-Y H:i') }}</dd>
    </dl>
    <div class="card" x-data="approvalWorkflow()">
    <h4 class="mb-4">🛠️ Setup Approval Workflow Stages</h4>

    <!-- Workflow Stages Form -->
    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="form-label">Stage Name</label>
                <input type="text" class="form-control" x-model="stage.name">
            </div>

            <div>
                <label class="form-label">Workflow Type</label>
                <select class="form-control" x-model="stage.type">
                    <option value="">-- Select --</option>
                    <option value="COUNT">COUNT</option>
                    <option value="ALL">ALL</option>
                    <option value="AMOUNT">AMOUNT</option>
                </select>
            </div>

            <div class="flex items-center mt-4 md:mt-8">
                <label class="me-2">Final Stage?</label>
                <input type="checkbox" x-model="stage.isFinal">
            </div>
        </div>

        <!-- Conditional Fields for AMOUNT -->
        <template x-if="stage.type === 'AMOUNT'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Approver Role</label>
                    <select class="form-control" x-model="stage.role">
                        <option value="">-- Select Role --</option>
                        <option value="Manager">Manager</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Finance">Finance</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Cut-off Amount</label>
                    <input type="number" class="form-control" x-model="stage.cutoff">
                </div>
            </div>
        </template>

        <button class="btn btn-primary mt-2" @click="addStage()">➕ Add Stage</button>
    </div>

    <!-- Stage Table -->
    <div class="mt-6">
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
            <tbody>
                <template x-for="(item, index) in stages" :key="index">
                    <tr>
                        <td x-text="index + 1"></td>
                        <td x-text="item.name"></td>
                        <td x-text="item.type"></td>
                        <td x-text="item.role ?? '-'"></td>
                        <td x-text="item.cutoff ? '$' + item.cutoff : '-'"></td>
                        <td><span x-text="item.isFinal ? 'Yes' : 'No'"></span></td>
                        <td>
                            <button class="btn btn-sm btn-danger" @click="removeStage(index)">🗑️</button>
                        </td>
                    </tr>
                </template>
                <tr x-show="stages.length === 0">
                    <td colspan="7" class="text-center">No stages added yet.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Alpine.js Logic -->
<script>
function approvalWorkflow() {
    return {
        stage: {
            name: '',
            type: '',
            role: '',
            cutoff: '',
            isFinal: false
        },
        stages: [],
        addStage() {
            if (!this.stage.name || !this.stage.type) {
                alert('Stage name and type are required.');
                return;
            }

            if (this.stage.type === 'AMOUNT' && (!this.stage.role || !this.stage.cutoff)) {
                alert('Role and cutoff are required for AMOUNT type.');
                return;
            }

            this.stages.push({ ...this.stage });
            this.resetStage();
        },
        removeStage(index) {
            this.stages.splice(index, 1);
        },
        resetStage() {
            this.stage = {
                name: '',
                type: '',
                role: '',
                cutoff: '',
                isFinal: false
            };
        }
    }
}
</script>

    <a href="{{ route('settings.approval_stages') }}" class="btn btn-secondary mt-3">← Back to List</a>
</div>
@endsection
