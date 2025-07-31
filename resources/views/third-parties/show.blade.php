@extends('layouts.app')

@section('title', 'View Third Party - ' . ($party->ThirdPartyName ?? 'N/A'))

@section('styles')
<style>
    /* Modern, Clean Palette */
    :root {
        --primary: #4F46E5;
        /* Indigo 600 */
        --primary-hover: #4338CA;
        /* Indigo 700 */
        --accent: #10B981;
        /* Emerald 500 */
        --text-dark: #1F2937;
        /* Gray 900 */
        --text-medium: #4B5563;
        /* Gray 700 */
        --text-light: #6B7280;
        /* Gray 500 */
        --bg-light: #F9FAFB;
        /* Gray 50 */
        --bg-card: #FFFFFF;
        --border-light: #E5E7EB;
        /* Gray 200 */
        --border-medium: #D1D5DB;
        /* Gray 300 */
        --success-bg: #D1FAE5;
        /* Green 100 */
        --success-text: #065F46;
        /* Green 800 */
        --warning-bg: #FEF3C7;
        /* Amber 100 */
        --warning-text: #92400E;
        /* Amber 800 */
        --danger-bg: #FEE2E2;
        /* Red 100 */
        --danger-text: #991B1B;
        /* Red 800 */
        --info-bg: #DBEAFE;
        /* Blue 100 */
        --info-text: #1E40AF;
        /* Blue 800 */
    }

    body {
        background-color: var(--bg-light);
        color: var(--text-dark);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .container-fluid-custom {
        width: 100%;
        padding-right: 2rem;
        padding-left: 2rem;
        margin-right: auto;
        margin-left: auto;
    }

    @media (min-width: 1600px) {
        .container-fluid-custom {
            max-width: 1500px;
        }
    }

    .card {
        border-radius: 1rem;
        border: 1px solid var(--border-light);
        background-color: var(--bg-card);
        overflow: hidden;
    }

    .card-header {
        background-color: var(--bg-card);
        border-bottom: 1px solid var(--border-light);
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-body {
        padding: 2rem;
    }

    .card-footer {
        border-top: 1px solid var(--border-light);
        border-bottom-left-radius: 1rem;
        border-bottom-right-radius: 1rem;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
    }

    .detail-item {
        margin-bottom: 1.5rem;
    }

    .detail-item dt {
        font-size: 0.95rem;
        color: var(--text-medium);
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .detail-item dd {
        font-size: 1.05rem;
        font-weight: 500;
        color: var(--text-dark);
        margin-bottom: 0;
        word-break: break-word;
    }

    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 1.25rem;
        font-weight: 600;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-primary {
        background-color: var(--primary);
        border: 1px solid var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background-color: var(--primary-hover);
        border-color: var(--primary-hover);
    }

    .btn-danger {
        background-color: var(--danger-text);
        border: 1px solid var(--danger-text);
        color: white;
    }

    .btn-danger:hover {
        background-color: #B91C1C;
        /* Darker red */
        border-color: #B91C1C;
    }

    .btn-outline-secondary {
        border: 1px solid var(--border-medium);
        color: var(--text-medium);
        background-color: var(--bg-card);
    }

    .btn-outline-secondary:hover {
        background-color: var(--bg-light);
        color: var(--text-dark);
        border-color: var(--text-light);
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.75em;
        border-radius: 0.375rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .badge-prequalified-yes {
        background-color: var(--accent);
        color: white;
    }

    .badge-prequalified-no {
        background-color: var(--danger-bg);
        color: var(--danger-text);
    }

    .text-primary {
        color: var(--primary) !important;
    }

    .text-danger {
        color: var(--danger-text) !important;
    }

    .text-muted {
        color: var(--text-light) !important;
    }

    @media (max-width: 768px) {

        .card-header,
        .card-body,
        .card-footer {
            padding: 1.25rem;
        }

        .card-footer {
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn {
            width: 100%;
        }

        .container-fluid-custom {
            padding-right: 1rem;
            padding-left: 1rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid-custom py-5">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 fw-bold" style="color: var(--text-dark);">
                Third Party Profile: {{ $party->ThirdPartyName ?? 'N/A' }}
            </h5>
            <a href="{{ route('web.parties.index') }}" class="btn btn-sm btn-outline-secondary" aria-label="Go back to list">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="card-body">
            <div class="row">
                @php
                $details = [
                'Company Name' => $party->ThirdPartyName,
                'Trading Name' => $party->TradingName,
                'Business Type' => $party->BusinessType?->label(),
                'Registration Number' => $party->RegistrationNumber,
                'Tax PIN' => $party->TaxPIN,
                'VAT Number' => $party->VATNumber,
                'Country' => $party->Country,
                'Physical Address' => $party->PhysicalAddress,
                'Email' => $party->Email,
                'Phone' => $party->Phone,
                'Website' => $party->Website,
                'Approval Status' => $party->ApprovalStatus?->label(),
                'Status' => $party->Status?->value,
                'Third Party Type' => $party->ThirdPartyType?->label(),
                'Is Prequalified' => $party->IsPrequalified,
                'Created On' => $party->CreatedOn?->format('Y-m-d H:i:s'),
                'Modified On' => $party->ModifiedOn?->format('Y-m-d H:i:s'),
                ];
                @endphp

                @foreach ($details as $label => $value)
                <div class="col-md-6 detail-item">
                    <dl class="mb-0">
                        <dt>{{ $label }}</dt>
                        <dd>
                            @if ($label === 'Website' && $value)
                            <a href="{{ $value }}" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline">{{ $value }}</a>
                            @elseif ($label === 'Is Prequalified')
                            <span class="badge {{ $value ? 'badge-prequalified-yes' : 'badge-prequalified-no' }}">
                                {{ $value ? 'Yes' : 'No' }}
                            </span>
                            @else
                            {{ $value ?: 'N/A' }}
                            @endif
                        </dd>
                    </dl>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('web.parties.edit', ['party' => $party->Id]) }}" class="btn btn-primary" title="Edit this third party">
                <i class="fas fa-edit"></i> Review
            </a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal" title="Delete this third party">
                <i class="fas fa-trash-alt"></i> Delete
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('web.parties.destroy', ['party' => $party->Id]) }}" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" title="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong>{{ $party->ThirdPartyName ?? 'this third party' }}</strong>? This action is <span class="text-danger fw-bold">irreversible</span>.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Yes, Delete
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@endsection