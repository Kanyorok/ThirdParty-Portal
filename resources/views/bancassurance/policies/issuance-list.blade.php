@extends('layouts.app')
@section('title', 'Policies Issuance')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0 rounded-4">

        <div class="card-header bg-primary text-white py-2 px-3">
            <h5 class="mb-0">
                <i class="bi bi-journal-check me-2"></i> Policies Ready for Issuance
            </h5>
        </div>

        <div class="card-body">

            <p class="text-muted small mb-4">
                <i class="bi bi-info-circle-fill me-1 text-primary"></i>
                Below is the list of policies approved and awaiting issuance.
            </p>

            <table class="table table-hover table-bordered align-middle" id="issuance">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:5%">#</th>
                        <th>Policy #</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Insurer</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-center" style="width:12%">Action</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($policies as $policy)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>

                        <td class="fw-semibold text-primary">
                            {{ $policy->PolicyNumber ?? '—' }}
                        </td>

                        <td>{{ $policy->customer->thirdParty->ThirdPartyName ?? '-' }}</td>
                        <td>{{ $policy->product->Name ?? '-' }}</td>
                        <td>{{ $policy->insurer->Name ?? '-' }}</td>

                        <td>
                            <span class="badge bg-{{ $policy->Status->badgeColor() ?? 'secondary' }}">
                                {{ $policy->Status->label() ?? '-' }}
                            </span>
                        </td>

                        <td>
                            {{ optional($policy->CreatedOn)->format('d M Y') ?? '-' }}
                        </td>

                        <td class="text-center">

                            {{-- Issuance Form --}}
                            <form id="issue-form-{{ $policy->Id }}"
                                  action="{{ route('bancassurance.policies.storeIssuance', $policy->Id) }}"
                                  method="POST">
                                @csrf
                            </form>

                            <button type="button"
                                    class="btn btn-sm btn-success btn-issue"
                                    data-form="issue-form-{{ $policy->Id }}"
                                    data-policy="{{ $policy->PolicyNumber }}">
                                <i class="bi bi-file-earmark-check me-1"></i> Issue
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No policies awaiting issuance.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection

@section('scripts')

{{-- jQuery --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

{{-- DataTables --}}
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {

    // Initialize DataTable
    $('#issuance').DataTable({
        pageLength: 10,
        ordering: true,
        searching: true,
        lengthChange: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search policies..."
        }
    });

    // Issue confirmation popup
    $(document).on('click', '.btn-issue', function () {

        const button   = $(this);
        const formId   = button.data('form');
        const policyNo = button.data('policy');

        Swal.fire({
            title: 'Confirm Policy Issuance',
            html: `
                <p>Are you sure you want to issue this policy?</p>
                <strong>Policy No:</strong> ${policyNo}
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Issue Policy',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                button.prop('disabled', true)
                      .html('<i class="bi bi-hourglass-split me-1"></i> Issuing...');
                document.getElementById(formId).submit();
            }
        });
    });

});
</script>
@endsection
