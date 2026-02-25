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

    <div class="d-flex justify-content-end mb-2">
        <div class="input-group" style="max-width: 340px;">
            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
            <input
                type="text"
                id="committeeSearch"
                class="form-control"
                placeholder="Search by reference or description…"
                autocomplete="off"
            >
            <button type="button" class="btn btn-outline-secondary" id="committeeSearchClear" title="Clear search">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle" id="committeesTable">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Committee Type</th>
                <th>Reference</th>
                <th>Description</th>
                <th>Members</th>
                <th>Appointment Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($committees as $index => $item)
                <tr>
                    <td>{{ $committees->firstItem() + $index }}</td>
                    <td class="text-uppercase">{{ $item['type'] }}</td>
                    <td>{{ $item['ref'] }}</td>
                    <td>{{ $item['description'] ?? 'N/A' }}</td>
                    <td>{{ $item['members_count'] }}</td>
                    <td>{{ $item['appointment_date'] ? \Carbon\Carbon::parse($item['appointment_date'])->format('d/m/Y') : 'N/A' }}</td>
                    <td>
                        @if (!empty($item['is_active']))
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('tendercommittee.manual.show', ['id' => $item['refId'], 'type' => $item['type']]) }}"
                           class="btn btn-sm btn-outline-info">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4">No Committees Found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="small text-muted">
            @if($committees->total() > 0)
                Showing {{ $committees->firstItem() }} to {{ $committees->lastItem() }} of {{ $committees->total() }} entries
            @else
                No entries
            @endif
        </div>
        <div>
            {{ $committees->withQueryString()->links() }}
        </div>
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

            <form id="committeeForm" action="{{ route('tendercommittee.store') }}" method="POST" novalidate>
                @csrf
                @method('POST')

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label for="committeeType" class="form-label">
                                Committee Type <span class="text-danger">*</span>
                            </label>
                            <select id="committeeType" class="form-select" name="committeeType">
                                <option value="">-- Select Type --</option>
                                <option value="tender">Tender</option>
                                <option value="rfq">RFQ</option>
                            </select>
                            <div class="invalid-feedback" id="committeeType-error"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="referenceId" class="form-label">
                                Reference <span class="text-danger">*</span>
                            </label>
                            <select id="referenceId" class="form-select" name="referenceId">
                                <option value="" selected>-- Select Reference --</option>
                            </select>
                            <div class="text-danger small mt-1" id="referenceId-error" style="display:none;"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="appointmentDate" class="form-label">
                                Appointment Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" name="appointmentDate" id="appointmentDate"
                                   min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                            <div class="invalid-feedback" id="appointmentDate-error"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="committeeMembers" class="form-label">
                            Select Committee Members <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="committeeMembers" name="committeeMembers[]" multiple>
                           @foreach ($employees as $item)
                            <option value="{{ $item->Id }}">
                                {{ optional($item->employee)->full_name ?? $item->Name }}
                                -
                                {{ $item->branchRoles->first()?->role?->name ?? 'N/A' }}
                            </option>
                        @endforeach
                        </select>
                        <div class="invalid-feedback" id="committeeMembers-error"></div>
                        <small class="form-text text-muted">Hold CTRL/CMD to select multiple users.</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="appointCommitteeBtn" class="btn btn-success">
                        Appoint Committee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        #referenceId {
            width: 100%;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    $(document).ready(function () {
        const committeeTypeSelect = document.getElementById('committeeType');
        const referenceSelect     = document.getElementById('referenceId');
        const form                = document.getElementById('committeeForm');
        const submitBtn           = document.getElementById('appointCommitteeBtn');

        //  helpers

        function setFieldError(inputEl, errorDivId, message) {
            const errDiv = document.getElementById(errorDivId);
            if (!errDiv) return;
            if (message) {
                inputEl.classList.add('is-invalid');
                errDiv.textContent = message;
                if (errDiv.classList.contains('text-danger')) errDiv.style.display = 'block';
            } else {
                inputEl.classList.remove('is-invalid');
                errDiv.textContent = '';
                if (errDiv.classList.contains('text-danger')) errDiv.style.display = 'none';
            }
        }

        function clearAllErrors() {
            [
                ['committeeType',    'committeeType-error'],
                ['referenceId',      'referenceId-error'],
                ['appointmentDate',  'appointmentDate-error'],
                ['committeeMembers', 'committeeMembers-error'],
            ].forEach(([id, errId]) => {
                const el = document.getElementById(id);
                if (el) setFieldError(el, errId, null);
            });
        }

        function highlightSelect2Error(hasError) {
            referenceSelect.classList.toggle('is-invalid', !!hasError);
        }

        // reset modal on close 

        $('#addCommitteeModal').on('hidden.bs.modal', function () {
            form.reset();
            clearAllErrors();
            referenceSelect.innerHTML = '<option value="" selected>-- Select Reference --</option>';
            const appt = document.getElementById('appointmentDate');
            if (appt) {
                const pad = n => String(n).padStart(2, '0');
                const n = new Date();
                appt.value = `${n.getFullYear()}-${pad(n.getMonth()+1)}-${pad(n.getDate())}`;
            }
            submitBtn.disabled = false;
            submitBtn.textContent = 'Appoint Committee';
            form.action = "{{ route('tendercommittee.store') }}";
        });

       


        // Appointment date (flatpickr)

        const appt = document.getElementById('appointmentDate');
        if (appt) {
            const pad = n => String(n).padStart(2, '0');
            const now = new Date();
            const todayStr = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}`;
            appt.min = todayStr;
            if (!appt.value || appt.value < todayStr) appt.value = todayStr;

            flatpickr(appt, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                allowInput: true,
                minDate: 'today',
                defaultDate: new Date(),
                disableMobile: true,
                onChange(_, dateStr) {
                    if (dateStr) setFieldError(appt, 'appointmentDate-error', null);
                },
            });
        }

        //commitee/type load referecnes 

        if (committeeTypeSelect) {
            committeeTypeSelect.addEventListener('change', function () {
                const type = this.value;
                setFieldError(committeeTypeSelect, 'committeeType-error', null);

                form.action = type === 'rfq'
                    ? "{{ route('rfqcommittee.store') }}"
                    : "{{ route('tendercommittee.store') }}";

                setFieldError(referenceSelect, 'referenceId-error', null);
                highlightSelect2Error(false);
                referenceSelect.innerHTML = '<option value="" selected>Loading references...</option>';

                if (!type) {
                    referenceSelect.innerHTML = '<option value="" selected>-- Select Reference --</option>';
                    return;
                }

                fetch(`/procurement/committee-references/${type}`)
                    .then(r => r.json())
                    .then(data => {
                        referenceSelect.innerHTML = '<option value="" selected>-- Select Reference --</option>';
                        data.forEach(item => {
                            const ref = item.RefNo ?? item.RFQNumber ?? 'N/A';
                            referenceSelect.innerHTML += `<option value="${item.Id}">${ref}${item.Title ? ' | ' + item.Title : ''}</option>`;
                        });
                    })
                    .catch(err => {
                        console.error('Error fetching references:', err);
                        referenceSelect.innerHTML = '<option value="" selected>Error loading options</option>';
                    });
            });
        }

        

        referenceSelect?.addEventListener('change', function () {
            if (this.value) {
                setFieldError(referenceSelect, 'referenceId-error', null);
                highlightSelect2Error(false);
            }
        });

        document.getElementById('committeeMembers')?.addEventListener('change', function () {
            if (this.selectedOptions.length > 0) {
                setFieldError(this, 'committeeMembers-error', null);
            }
        });

        

        submitBtn.addEventListener('click', function () {
            clearAllErrors();
            let valid = true;

            if (!committeeTypeSelect.value) {
                setFieldError(committeeTypeSelect, 'committeeType-error', 'The Committee Type field is required.');
                valid = false;
            }

            if (!referenceSelect.value) {
                setFieldError(referenceSelect, 'referenceId-error', 'The Reference field is required.');
                highlightSelect2Error(true);
                valid = false;
            }

            const apptEl = document.getElementById('appointmentDate');
            if (!apptEl || !apptEl.value) {
                setFieldError(apptEl, 'appointmentDate-error', 'The Appointment Date field is required.');
                valid = false;
            }

            const membersEl = document.getElementById('committeeMembers');
            if (!membersEl || membersEl.selectedOptions.length === 0) {
                setFieldError(membersEl, 'committeeMembers-error', 'The Committee Members field is required. Please select at least one member.');
                valid = false;
            }

            if (!valid) return;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
            form.submit();
        });

       

        const searchInput  = document.getElementById('committeeSearch');
        const searchClear  = document.getElementById('committeeSearchClear');
        const tableBody    = document.querySelector('#committeesTable tbody');
        const infoText     = document.querySelector('.small.text-muted');

        function filterTable() {
            const term = searchInput.value.trim().toLowerCase();
            const rows = tableBody.querySelectorAll('tr[data-searchable]');
            let visible = 0;

            rows.forEach(row => {
                const ref  = (row.dataset.ref  || '').toLowerCase();
                const desc = (row.dataset.desc || '').toLowerCase();
                const match = !term || ref.includes(term) || desc.includes(term);
                row.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            // Show / hide "no results" placeholder
            let noResultsRow = tableBody.querySelector('.no-search-results');
            if (visible === 0 && term) {
                if (!noResultsRow) {
                    noResultsRow = document.createElement('tr');
                    noResultsRow.className = 'no-search-results';
                    noResultsRow.innerHTML = '<td colspan="8" class="text-center py-4 text-muted">No committees match your search.</td>';
                    tableBody.appendChild(noResultsRow);
                }
                noResultsRow.style.display = '';
            } else if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }

            // Update entry-count text
            if (infoText) {
                if (term) {
                    infoText.textContent = `Showing ${visible} result${visible !== 1 ? 's' : ''} for "${searchInput.value.trim()}"`;
                } else {
                    // Restore original server-rendered text
                    infoText.textContent = infoText.dataset.original || infoText.textContent;
                }
            }
        }

        // Store original info text for restoration
        if (infoText) infoText.dataset.original = infoText.textContent;

        // Stamp each data row with searchable attributes
        tableBody.querySelectorAll('tr:not(.no-search-results)').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 4) {
                row.dataset.searchable = '1';
                row.dataset.ref  = cells[2].textContent.trim();
                row.dataset.desc = cells[3].textContent.trim();
            }
        });

        if (searchInput) {
            searchInput.addEventListener('input', filterTable);
        }

        if (searchClear) {
            searchClear.addEventListener('click', function () {
                searchInput.value = '';
                filterTable();
                searchInput.focus();
            });
        }
    });
    </script>
@endpush


@endsection
