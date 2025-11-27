@extends('layouts.app')
@section('title', 'Policies Issuance')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0">

        <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-journal-check me-2"></i> Policies Ready for Issuance
            </h5>
        </div>

        <div class="card-body">
            <p class="text-muted small mb-4">
                <i class="bi bi-chat-dots-fill me-2 text-primary"></i>
                Below is the list of all policies that are pending issuance.
            </p>

            <table class="table table-hover table-bordered align-middle" id="issuance">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 5%">#</th>
                        <th>Policy #</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Insurer</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-center" style="width: 10%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($policies as $policy)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <span class="fw-semibold text-primary">
                                    {{ $policy->PolicyNumber ?? '—' }}
                                </span>
                            </td>
                            <td>{{ $policy->customer->thirdParty->ThirdPartyName ?? '-' }}</td>
                            <td>{{ $policy->product->Name ?? '-' }}</td>
                            <td>{{ $policy->insurer->Name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $policy->Status->badgeColor() ?? 'secondary' }}">
                                    {{ $policy->Status->label() ?? '-' }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($policy->CreatedAt)->format('d M Y') ?? '-' }}</td>
                            <td class="text-center">
                                <form action="{{ route('bancassurance.policies.storeIssuance', $policy->Id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <button type="submit" 
                                            class="btn btn-sm btn-success px-3"
                                            onclick="this.disabled=true; this.innerText='Issuing...'; this.form.submit();">
                                        <i class="bi bi-file-earmark-check me-1"></i> Issue
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
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
        });
    </script>
@endsection
@endsection
