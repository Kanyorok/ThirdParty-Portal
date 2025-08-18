@extends('layouts.app')
@section('title', '')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Tender/RFQ Committees</h4>
        <a href="{{ route('tendercommittee.create') }}" class="btn btn-sm btn-success" data-bs-toggle="modal"
           data-bs-target="#addCommitteeModal">
            + Appoint New Committee</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Committee Type</th>
                <th>Reference</th>
                <th>Description</th>
                <th>Members</th>
                <th>Appointment Date</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($committees as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-uppercase">{{ $item['type'] }}</td>
                    <td>{{ $item['ref'] }}</td>
                    <td>
                        @if ($item['type'] === 'tender')
                            {{ \App\Models\Procurement\Tender::find($item['refId'])->Title ?? 'N/A' }}
                        @elseif ($item['type'] === 'rfq')
                            {{ \App\Models\Procurement\RFQ::find($item['refId'])->RFQNumber ?? 'N/A' }}
                        @endif
                    </td>

                    <td>{{ $item['members_count'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($item['appointment_date'])->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('tendercommittee.show', ['id' => $item['refId'], 'type' => $item['type']]) }}"
                           class="btn btn-sm btn-outline-info">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">No Committees Found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>



<!-- Add New Committee Modal -->
<div class="modal fade" id="addCommitteeModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Appoint Committee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="committeeForm" action="{{ route('tendercommittee.store') }}" method="POST">
                @csrf
                @method('POST')

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="committeeType" class="form-label">Committee Type</label>
                            <select id="committeeType" class="form-select" name="committeeType" required>
                                <option value="">-- Select Type --</option>
                                <option value="tender">Tender</option>
                                <option value="rfq">RFQ</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="referenceId" class="form-label">Reference</label>
                            <select id="referenceId" class="form-select" name="referenceId" required>
                                <option value="">-- Select Reference --</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="appointmentDate" class="form-label">Appointment Date</label>
                            <input type="date" class="form-control" name="appointmentDate" id="appointmentDate">
                        </div>
                        @error('appointmentDate')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="committeeMembers" class="form-label">Select Committee Members</label>
                        <select class="form-select" id="committeeMembers" multiple required name="committeeMembers[]">
                            <!-- Populate from system user list -->
                            @foreach ($employees as $item)
                                <option value="{{$item['Id']}}">{{$item->FirstName}} {{$item->LastName}}.
                                    – {{$item->JobTitle}}</option>
                            @endforeach
                        </select>
                        @error('committeeMembers')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Hold CTRL/CMD to select multiple users.</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button
                        type="submit"
                        class="btn btn-success"
                        onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();"
                    >
                        Appoint Committee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const committeeTypeSelect = document.getElementById("committeeType");
        const referenceSelect = document.getElementById("referenceId");
        const form = document.getElementById("committeeForm");

        committeeTypeSelect.addEventListener("change", function () {
            const selectedType = this.value;

            // Change form action based on type
            if (selectedType === 'rfq') {
                form.action = "{{ route('rfqcommittee.store') }}";
            } else {
                form.action = "{{ route('tendercommittee.store') }}";
            }

            // Load references
            referenceSelect.innerHTML = '<option value="">Loading...</option>';
            fetch(`/procurement/committee-references/${selectedType}`)
                .then(response => response.json())
                .then(data => {
                    referenceSelect.innerHTML = '<option value="">-- Select Reference --</option>';
                    data.forEach(item => {
                        const ref = item.RefNo ?? item.RFQNumber ?? 'N/A';
                        referenceSelect.innerHTML += `<option value="${item.Id}">${ref} ${item.Title ? '| ' + item.Title : ''}</option>`;
                    });
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                    referenceSelect.innerHTML = '<option value="">Error loading options</option>';
                });
        });
    });
</script>


@endsection
