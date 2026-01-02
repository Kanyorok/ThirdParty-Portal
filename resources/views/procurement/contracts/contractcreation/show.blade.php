@extends('layouts.app')
@section('title', 'Contract Details')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">📄 Contract Details</h4>
                        <p class="text-muted mb-0">
                            Contract Reference: <strong>{{ $contract->ContractRef ?? 'PENDING' }}</strong>
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Contracts
                        </a>

                        @if($contract->ContractStatus === 'Under Review')
                            <a href="{{ route('contracts.approvalQueue') }}" class="btn btn-info me-2">
                                <i class="fas fa-clock"></i> Go to Approval Queue
                            </a>
                        @endif

                        @if($contract->ContractStatus === 'Draft Created')
                            <a href="{{ route('contracts.edit', ['id' => $contract->Id, 'type' => $type ?? 'tender']) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit Contract
                            </a>
                        @elseif(in_array($contract->ContractStatus, ['Approved', 'Executed']) && $contract->ContractStatus !== 'Terminated')
                            <button class="btn btn-warning" onclick="addAddendum()">
                                <i class="fas fa-plus-circle"></i> Add Addendum
                            </button>
                        @endif
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Contract Status Card -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-file-contract fa-3x {{ strpos($contract->contract_status_badge['class'], 'success') !== false ? 'text-success' : (strpos($contract->contract_status_badge['class'], 'warning') !== false ? 'text-warning' : 'text-primary') }} mb-3"></i>
                                <h5>Contract Status</h5>
                                <span class="badge {{ $contract->contract_status_badge['class'] }} fs-6">
                                    {{ $contract->contract_status_badge['text'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-calendar-alt fa-3x text-info mb-3"></i>
                                <h5>Contract Duration</h5>
                                @if($contract->ContractStartDate && $contract->ContractEndDate)
                                    <div class="text-muted">
                                        {{ $contract->ContractStartDate->format('d/m/Y') }} -
                                        {{ $contract->ContractEndDate->format('d/m/Y') }}
                                    </div>
                                    <small class="text-success">
                                        ({{ $contract->ContractStartDate->diffInDays($contract->ContractEndDate) }}
                                        days)
                                    </small>
                                @else
                                    <span class="text-muted">Duration not set</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-money-bill-wave fa-3x text-success mb-3"></i>
                                <h5>Contract Value</h5>
                                @if($contract->ContractValue)
                                    <strong class="text-success fs-5">
                                        {{ number_format($contract->ContractValue, 2) }}
                                    </strong>
                                    <div
                                        class="text-muted">{{ is_object($contract->tender->Currency) ? $contract->tender->Currency->Code : ($contract->tender->Currency ?? 'KES') }}</div>
                                @else
                                    <span class="text-muted">Value not set</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Award Information -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">🏆 Award Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Tender Reference:</strong></td>
                                        <td>{{ $contract->tender->TenderNo ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tender Title:</strong></td>
                                        <td>{{ $contract->tender->Title ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Award Date:</strong></td>
                                        <td>{{ $contract->AwardDate ? $contract->AwardDate->format('d/m/Y') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Award Status:</strong></td>
                                        <td>
                                            <span class="badge {{ $contract->status_badge['class'] }}">
                                                {{ $contract->status_badge['text'] }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Winning Supplier:</strong></td>
                                        <td>{{ 
                                            $contract->winningSupplier->supplierMaster->party->TradingName 
                                            ?? $contract->winningSupplier->thirdParty->TradingName 
                                            ?? $contract->winningSupplier->thirdParty->Name 
                                            ?? $contract->winningSupplier->SupplierName 
                                            ?? 'N/A' 
                                        }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Contact Person:</strong></td>
                                        <td>{{ $contract->winningSupplier->thirdParty->ContactPerson ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>{{ $contract->winningSupplier->thirdParty->Email ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td>{{ $contract->winningSupplier->thirdParty->PhoneNumber ?? ($contract->winningSupplier->thirdParty->Mobile ?? 'N/A') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contract Documents -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">📎 Contract Documents</h5>
                    </div>
                    <div class="card-body">
                        @if($contract->ContractStatus !== 'Terminated')
                            <form id="uploadForm" class="mb-3">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="file" id="contractDocument" name="contract_document"
                                               class="form-control" accept=".pdf,.doc,.docx" required>
                                        <small class="text-muted">Accepted formats: PDF, DOC, DOCX (Max: 10MB)</small>

                                        <!-- Progress Bar -->
                                        <div id="uploadProgress" class="progress mt-2"
                                             style="display: none; height: 20px;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                 role="progressbar"
                                                 style="width: 0%" aria-valuenow="0" aria-valuemin="0"
                                                 aria-valuemax="100">
                                                <span id="progressText">0%</span>
                                            </div>
                                        </div>

                                        <!-- Upload Status -->
                                        <div id="uploadStatus" class="mt-2"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" id="uploadBtn" class="btn btn-success w-100">
                                            <i class="fas fa-upload"></i> Upload Document
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif

                        <!-- Display uploaded documents -->
                        <div class="table-responsive">
                            <table class="table table-sm" id="documentsTable">
                                <thead class="table-light">
                                <tr>
                                    <th>Document Type</th>
                                    <th>File Name</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody id="documentsTableBody">
                                @php
                                    $documents = json_decode($contract->SpecialConditions ?? '[]', true);
                                    $documents = is_array($documents) ? $documents : [];
                                @endphp

                                @forelse($documents as $doc)
                                    @if(isset($doc['original_name']))
                                        <tr>
                                            <td>{{ $doc['type'] ?? 'Contract Document' }}</td>
                                            <td>{{ $doc['original_name'] }}</td>
                                            <td>{{ isset($doc['upload_date']) ? \Carbon\Carbon::parse($doc['upload_date'])->format('d/m/Y H:i') : 'N/A' }}</td>
                                            <td>
                                                <a href="{{ Storage::url($doc['file_path']) }}" target="_blank"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr id="noDocsRow">
                                        <td colspan="4" class="text-center text-muted">No documents uploaded yet</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Contract Details -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">📋 Contract Terms & Conditions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary">💳 Payment Terms</h6>
                                <div class="bg-light p-3 rounded">
                                    {{ $contract->PaymentTerms ?: 'Payment terms not specified' }}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-primary">🚚 Delivery Terms</h6>
                                <div class="bg-light p-3 rounded">
                                    {{ $contract->DeliveryTerms ?: 'Delivery terms not specified' }}
                                </div>
                            </div>
                        </div>

                        @if($contract->SpecialConditions)
                            <div class="mt-3">
                                <h6 class="text-primary">⚖️ Special Conditions</h6>
                                <div class="bg-light p-3 rounded">
                                    @php
                                        $conditions = json_decode($contract->SpecialConditions, true);
                                    @endphp

                                    @if(json_last_error() === JSON_ERROR_NONE && is_array($conditions))
                                        @foreach($conditions as $item)
                                            @if(isset($item['type']) && $item['type'] === 'Original Special Conditions')
                                                <div class="mb-2">
                                                    {{ $item['content'] }}
                                                </div>
                                            @endif
                                        @endforeach
                                        
                                        {{-- If no original conditions found in JSON, but array exists (e.g. only files), show nothing or message --}}
                                        @if(collect($conditions)->where('type', 'Original Special Conditions')->isEmpty())
                                            <span class="text-muted">No text conditions specified.</span>
                                        @endif
                                    @else
                                        {{-- Legacy: Display as raw string --}}
                                        {{ $contract->SpecialConditions }}
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Contract Actions -->
                @if($contract->hasContract())
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">⚡ Contract Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if($contract->ContractStatus === 'Draft Created')
                                    <div class="col-md-3">
                                        <button class="btn btn-success w-100" onclick="submitForReview()">
                                            <i class="fas fa-paper-plane"></i>
                                            Submit for Review
                                        </button>
                                    </div>
                                @endif

                                @if($contract->ContractStatus === 'Approved')
                                    <div class="col-md-3">
                                        <button class="btn btn-primary w-100" onclick="executeContract()">
                                            <i class="fas fa-handshake"></i>
                                            Execute Contract
                                        </button>
                                    </div>
                                @endif

                                @if($contract->ContractStatus === 'Executed')
                                    <div class="col-md-3">
                                        <a href="{{ route('contracts.lifecycle.execution', $contract->Id) }}"
                                           class="btn btn-info w-100">
                                            <i class="fas fa-chart-line"></i>
                                            Monitor Performance
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="{{ route('contracts.lifecycle.amend', $contract->Id) }}"
                                           class="btn btn-warning w-100">
                                            <i class="fas fa-edit"></i>
                                            Amend Contract
                                        </a>
                                    </div>
                                @endif

                                @if(in_array($contract->ContractStatus, ['Draft Created', 'Under Review', 'Approved', 'Executed']))
                                    <div class="col-md-3">
                                        <a href="{{ route('contracts.lifecycle.terminate', $contract->Id) }}"
                                           class="btn btn-danger w-100">
                                            <i class="fas fa-times-circle"></i>
                                            Terminate Contract
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Contract History/Timeline -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">📊 Contract Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Award Approved</h6>
                                    <p class="timeline-description">
                                        Tender awarded
                                        to {{ $contract->winningSupplier->thirdParty->TradingName ?? ($contract->winningSupplier->thirdParty->Name ?? 'N/A') }}
                                    </p>
                                    <small
                                        class="text-muted">{{ $contract->ApprovedOn ? $contract->ApprovedOn->format('M d, Y H:i') : 'N/A' }}</small>
                                </div>
                            </div>

                            @if($contract->hasContract())
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Contract Created</h6>
                                        <p class="timeline-description">
                                            Contract {{ $contract->ContractRef }} created
                                        </p>
                                        <small
                                            class="text-muted">{{ $contract->ModifiedOn ? $contract->ModifiedOn->format('M d, Y H:i') : 'N/A' }}</small>
                                    </div>
                                </div>
                            @endif

                            @if($contract->ContractApprovedOn)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Contract Approved</h6>
                                        <p class="timeline-description">
                                            Contract approved and ready for execution
                                        </p>
                                        <small
                                            class="text-muted">{{ $contract->ContractApprovedOn->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
            padding-left: 50px;
        }

        .timeline-marker {
            position: absolute;
            left: 14px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 3px #dee2e6;
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            position: relative;
        }

        .timeline-title {
            margin-bottom: 5px;
            font-weight: bold;
        }

        .timeline-description {
            margin-bottom: 5px;
        }
    </style>

    <script>
        // Document Upload with Progress Bar
        document.getElementById('uploadForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const fileInput = document.getElementById('contractDocument');
            const uploadBtn = document.getElementById('uploadBtn');
            const progressContainer = document.getElementById('uploadProgress');
            const progressBar = progressContainer.querySelector('.progress-bar');
            const progressText = document.getElementById('progressText');
            const uploadStatus = document.getElementById('uploadStatus');

            if (!fileInput.files[0]) {
                uploadStatus.innerHTML = '<div class="alert alert-danger">Please select a file to upload.</div>';
                return;
            }

            // Reset status
            uploadStatus.innerHTML = '';
            progressContainer.style.display = 'block';
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

            // Create FormData
            const formData = new FormData();
            formData.append('contract_document', fileInput.files[0]);
            formData.append('award_type', '{{ $type ?? "tender" }}');
            formData.append('_token', '{{ csrf_token() }}');

            // Create XMLHttpRequest for progress tracking
            const xhr = new XMLHttpRequest();

            // Upload progress
            xhr.upload.addEventListener('progress', function (e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                    progressText.textContent = Math.round(percentComplete) + '%';
                }
            });

            // Upload complete
            xhr.addEventListener('load', function () {
                console.log('Upload response status:', xhr.status); // Debug log
                console.log('Upload response:', xhr.responseText); // Debug log

                try {
                    const response = JSON.parse(xhr.responseText);
                    console.log('Parsed response:', response); // Debug log

                    if (response.success) {
                        // Success
                        progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                        progressBar.classList.add('bg-success');
                        uploadStatus.innerHTML = '<div class="alert alert-success">' + response.message + '</div>';

                        // Add document to table
                        console.log('Adding document to table:', response.document); // Debug log
                        addDocumentToTable(response.document);

                        // Reset form
                        fileInput.value = '';

                        // Hide progress after delay
                        setTimeout(() => {
                            progressContainer.style.display = 'none';
                            uploadStatus.innerHTML = '';
                        }, 3000);
                    } else {
                        // Server returned error in JSON format
                        progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                        progressBar.classList.add('bg-danger');
                        uploadStatus.innerHTML = '<div class="alert alert-danger">' + (response.message || 'Unknown server error') + '</div>';
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Raw response:', xhr.responseText);

                    // If we can't parse JSON, show the raw response or a generic error
                    let errorMessage = 'Invalid response from server.';
                    if (xhr.responseText && xhr.responseText.length < 200) {
                        errorMessage = xhr.responseText;
                    }

                    progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                    progressBar.classList.add('bg-danger');
                    uploadStatus.innerHTML = '<div class="alert alert-danger">' + errorMessage + '</div>';
                }

                // Reset button
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Document';
            });

            // Upload error
            xhr.addEventListener('error', function () {
                uploadStatus.innerHTML = '<div class="alert alert-danger">Upload failed. Please check your connection.</div>';
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Document';
            });

            // Send request
            xhr.open('POST', '{{ route("contracts.uploadDocument", $contract->Id) }}');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.send(formData);
        });

        // Add document to table
        function addDocumentToTable(doc) {
            console.log('addDocumentToTable called with:', doc); // Debug log

            const tableBody = document.getElementById('documentsTableBody');
            const noDocsRow = document.getElementById('noDocsRow');

            if (!tableBody) {
                console.error('Table body not found!');
                return;
            }

            // Remove "no documents" row if it exists
            if (noDocsRow) {
                console.log('Removing no docs row');
                noDocsRow.remove();
            }

            // Create new row
            const newRow = document.createElement('tr');
            const uploadDate = new Date(doc.upload_date).toLocaleDateString('en-GB', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });

            console.log('Creating row with date:', uploadDate); // Debug log

            newRow.innerHTML = `
                <td>${doc.type || 'Contract Document'}</td>
                <td>${doc.original_name}</td>
                <td>${uploadDate}</td>
                <td>
                    <a href="/storage/${doc.file_path}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download"></i> Download
                    </a>
                </td>
            `;

            tableBody.appendChild(newRow);
            console.log('Row added to table'); // Debug log
        }

        function submitForReview() {
            if (confirm('Submit this contract for review? Once submitted, you will not be able to make changes until it is reviewed.')) {
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("contracts.submitForReview", $contract->Id) }}';

                // Add award_type hidden input
                const typeInput = document.createElement('input');
                typeInput.type = 'hidden';
                typeInput.name = 'award_type';
                typeInput.value = '{{ $type ?? "tender" }}';
                form.appendChild(typeInput);

                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                // Add optional review notes (you could add a prompt for this)
                const reviewNotes = prompt('Add any review notes (optional):');
                if (reviewNotes !== null) {
                    const notesInput = document.createElement('input');
                    notesInput.type = 'hidden';
                    notesInput.name = 'review_notes';
                    notesInput.value = reviewNotes;
                    form.appendChild(notesInput);
                }

                document.body.appendChild(form);
                form.submit();
            }
        }

        function executeContract() {
            if (confirm('Execute this contract? This will mark the contract as active and binding.')) {
                // TODO: Implement contract execution
                alert('Feature coming soon: Contract execution workflow');
            }
        }

        function addAddendum() {
            // Create modal for addendum
            const modalHtml = `
                <div class="modal fade" id="addendumModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Contract Addendum</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('contracts.addAddendum', $contract->Id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="award_type" value="{{ $type ?? 'tender' }}">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Addendum Title <span class="text-danger">*</span></label>
                    <input type="text" name="addendum_title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="addendum_description" class="form-control" rows="4" required
                              placeholder="Describe the changes or additions to the contract..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Effective Date</label>
                    <input type="date" name="effective_date" class="form-control" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Upload Addendum Document (Optional)</label>
                                        <input type="file" name="addendum_document" class="form-control" accept=".pdf,.doc,.docx">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-plus-circle"></i> Add Addendum
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal if any
            const existingModal = document.getElementById('addendumModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add modal to DOM and show
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('addendumModal'));
            modal.show();
        }
    </script>
@endsection
