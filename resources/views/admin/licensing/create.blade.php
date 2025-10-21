@extends('layouts.app')

@section('title', 'Upload License')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-upload me-2"></i>
                        Upload New License
                    </h5>
                    <a href="{{ route('admin.licensing.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    <!-- Instance Information -->
                    <div class="alert alert-info">
                        <h6 class="mb-2">
                            <i class="fas fa-server me-2"></i>
                            Instance Information for License Request
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Database GUID:</strong><br>
                                <code class="small">{{ $instance->DbGuid }}</code>
                            </div>
                            <div class="col-md-6">
                                <strong>Host Fingerprint:</strong><br>
                                <code class="small text-truncate d-block">{{ Str::limit($instance->HostFingerprint, 30) }}</code>
                            </div>
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('admin.licensing.instance.download') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i> Download Full Instance Info
                            </a>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <form method="POST" action="{{ route('admin.licensing.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="license_file" class="form-label">License File</label>
                            <input type="file" 
                                   class="form-control @error('license_file') is-invalid @enderror" 
                                   id="license_file" 
                                   name="license_file" 
                                   accept=".json,.txt"
                                   required>
                            <div class="form-text">
                                Upload the license file provided by your vendor (.json or .txt format)
                            </div>
                            @error('license_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="public_key_id" class="form-label">Public Key ID</label>
                            <input type="text" 
                                   class="form-control @error('public_key_id') is-invalid @enderror" 
                                   id="public_key_id" 
                                   name="public_key_id" 
                                   value="{{ old('public_key_id', 'vendor-2025') }}"
                                   placeholder="e.g., vendor-2025"
                                   required>
                            <div class="form-text">
                                Enter the public key identifier provided by your vendor
                            </div>
                            @error('public_key_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- License Preview (filled by JavaScript) -->
                        <div class="card mb-4" id="licensePreview" style="display: none;">
                            <div class="card-header">
                                <h6 class="mb-0">License Preview</h6>
                            </div>
                            <div class="card-body">
                                <div id="licenseContent"></div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.licensing.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i> Upload License
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Instructions Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        License Upload Instructions
                    </h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li class="mb-2">
                            <strong>Request a License:</strong> Contact your vendor with the instance information shown above
                        </li>
                        <li class="mb-2">
                            <strong>Receive License File:</strong> You'll receive a JSON or text file containing your license
                        </li>
                        <li class="mb-2">
                            <strong>Upload License:</strong> Use the form above to upload the license file
                        </li>
                        <li class="mb-2">
                            <strong>Verification:</strong> The system will automatically verify the license signature
                        </li>
                    </ol>

                    <div class="alert alert-warning mt-3">
                        <h6 class="mb-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Security Notes
                        </h6>
                        <ul class="mb-0 small">
                            <li>Only upload license files provided by your authorized vendor</li>
                            <li>License files are cryptographically signed and cannot be modified</li>
                            <li>Each license is bound to this specific instance</li>
                            <li>Uploading a new license will deactivate the previous one</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('license_file');
    const previewCard = document.getElementById('licensePreview');
    const previewContent = document.getElementById('licenseContent');

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) {
            previewCard.style.display = 'none';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const content = e.target.result;
                let licenseData;
                
                try {
                    licenseData = JSON.parse(content);
                } catch (err) {
                    throw new Error('Invalid JSON format');
                }

                if (!licenseData.payload) {
                    throw new Error('Missing payload in license file');
                }

                let payload;
                if (typeof licenseData.payload === 'string') {
                    payload = JSON.parse(licenseData.payload);
                } else {
                    payload = licenseData.payload;
                }

                // Display license information
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>License Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>License ID:</strong></td><td>${payload.license_id || 'N/A'}</td></tr>
                                <tr><td><strong>Tenant:</strong></td><td>${payload.tenant_name || 'N/A'}</td></tr>
                                <tr><td><strong>Edition:</strong></td><td>${payload.edition || 'N/A'}</td></tr>
                                <tr><td><strong>Max Users:</strong></td><td>${payload.max_users || 'N/A'}</td></tr>
                                <tr><td><strong>Expires:</strong></td><td>${payload.expires_at || 'N/A'}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Licensed Modules</h6>
                            <div class="mb-3">
                `;
                
                if (payload.modules && payload.modules.length > 0) {
                    payload.modules.forEach(module => {
                        html += `<span class="badge bg-primary me-1 mb-1">${module}</span>`;
                    });
                } else {
                    html += '<span class="text-muted">No modules specified</span>';
                }

                html += `</div>`;

                if (payload.features) {
                    html += `<h6>Features</h6><div>`;
                    Object.entries(payload.features).forEach(([feature, enabled]) => {
                        if (enabled) {
                            html += `<span class="badge bg-success me-1 mb-1">${feature}</span>`;
                        }
                    });
                    html += `</div>`;
                }

                html += `</div></div>`;

                previewContent.innerHTML = html;
                previewCard.style.display = 'block';

            } catch (error) {
                previewContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Error parsing license file:</strong> ${error.message}
                    </div>
                `;
                previewCard.style.display = 'block';
            }
        };
        
        reader.readAsText(file);
    });
});
</script>
@endpush
