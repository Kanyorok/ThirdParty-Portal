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
                    <td>{{ $committees->firstItem() + $index }}</td>
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
                        <a href="{{ route('tendercommittee.manual.show', ['id' => $item['refId'], 'type' => $item['type']]) }}"
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
                            <select id="referenceId" class="form-select select2-reference" name="referenceId" required>
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="appointmentDate" class="form-label">Appointment Date</label>
                            <input type="date" class="form-control" name="appointmentDate" id="appointmentDate" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required>
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
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        /* Remove text decoration from Select2 dropdown options */
        .select2-container--default .select2-results__option {
            text-decoration: none !important;
        }
        
        /* Specifically target the Reference dropdown */
        .select2-reference + .select2-container .select2-results__option {
            text-decoration: none !important;
        }
        
        /* Remove text decoration from selected items */
        .select2-container--default .select2-selection__rendered {
            text-decoration: none !important;
        }
        
        /* Ensure no underline on hover */
        .select2-container--default .select2-results__option:hover {
            text-decoration: none !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
    // Wait for jQuery and DOM to be ready
    $(document).ready(function() {
        const committeeTypeSelect = document.getElementById("committeeType");
        const referenceSelect = document.getElementById("referenceId");
        const form = document.getElementById("committeeForm");

        // Initialize Select2 for Reference dropdown
        function initializeSelect2() {
            if ($('.select2-reference').length && typeof $.fn.select2 !== 'undefined') {
                // Destroy existing instance if any
                if ($('.select2-reference').hasClass('select2-hidden-accessible')) {
                    $('.select2-reference').select2('destroy');
                }
                
                $('.select2-reference').select2({
                    placeholder: '-- Select Reference --',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#addCommitteeModal')
                });
            }
        }

        // Initialize Select2 when modal is shown
        $('#addCommitteeModal').on('shown.bs.modal', function () {
            initializeSelect2();
        });

        // Initialize on page load
        initializeSelect2();

        // Initialize appointment date field similar to Raise Needs
        const appt = document.getElementById('appointmentDate');
        if (appt) {
            const pad = (n) => String(n).padStart(2, '0');
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
                disableMobile: true
            });

            appt.addEventListener('change', () => {
                if (appt.value && appt.value < appt.min) {
                    appt.setCustomValidity('Date cannot be earlier than today.');
                    appt.reportValidity();
                    appt.value = appt.min;
                    appt.setCustomValidity('');
                }
            });
        }

        if (committeeTypeSelect) {
            committeeTypeSelect.addEventListener("change", function () {
                const selectedType = this.value;

                // Change form action based on type
                if (selectedType === 'rfq') {
                    form.action = "{{ route('rfqcommittee.store') }}";
                } else {
                    form.action = "{{ route('tendercommittee.store') }}";
                }

                // Destroy existing Select2 before loading new data
                if ($('.select2-reference').hasClass('select2-hidden-accessible')) {
                    $('.select2-reference').select2('destroy');
                }

                // Load references
                referenceSelect.innerHTML = '<option value="">Loading...</option>';
                fetch(`/procurement/committee-references/${selectedType}`)
                    .then(response => response.json())
                    .then(data => {
                        referenceSelect.innerHTML = '<option value=""></option>';
                        data.forEach(item => {
                            const ref = item.RefNo ?? item.RFQNumber ?? 'N/A';
                            referenceSelect.innerHTML += `<option value="${item.Id}">${ref} ${item.Title ? '| ' + item.Title : ''}</option>`;
                        });
                        
                        // Re-initialize Select2 after loading new options
                        setTimeout(initializeSelect2, 100);
                    })
                    .catch(error => {
                        console.error("Error fetching data:", error);
                        referenceSelect.innerHTML = '<option value="">Error loading options</option>';
                        
                        // Re-initialize Select2 even on error
                        setTimeout(initializeSelect2, 100);
                    });
            });
        }
    });
    </script>
@endpush


@endsection
