@extends('layouts.app')
@section('title', 'Bulk Upload Property Rate & Pricing')

@section('content')

<style>
    .bulk-upload-section {
        max-width: 900px;
        margin: 0 auto;
    }

    .upload-info-box {
        background: #f8f9fa;
        border-left: 4px solid #0d6efd;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-radius: 4px;
    }

    .upload-info-box h5 {
        color: #0d6efd;
        margin-bottom: 0.75rem;
        font-weight: 600;
    }

    .upload-info-box li {
        font-size: 0.9rem;
        margin-bottom: 0.4rem;
    }

    .form-section {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 2rem;
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
    }

    .file-upload-wrapper {
        border: 2px dashed #0d6efd;
        border-radius: 4px;
        padding: 2rem;
        text-align: center;
        background: #f0f7ff;
        cursor: pointer;
        transition: 0.3s ease;
    }

    .file-upload-wrapper:hover {
        background: #e6f2ff;
    }

    .file-upload-wrapper.has-file {
        border-color: #198754;
        background: #f0fff4;
    }

    .file-upload-wrapper input {
        display: none;
    }

    .upload-icon {
        font-size: 2.5rem;
        color: #0d6efd;
        margin-bottom: 0.75rem;
    }

    .file-name {
        color: #198754;
        font-weight: 600;
        margin-top: .75rem;
    }

    .template-section {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 1rem;
        margin-top: 1.5rem;
        border-radius: 4px;
    }

    .error-summary {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .error-summary h6 {
        color: #721c24;
        font-weight: 600;
        margin-bottom: .75rem;
    }

    .error-item {
        color: #721c24;
        font-size: .9rem;
    }
</style>

<div class="bulk-upload-section py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-file-upload"></i> Bulk Upload Property Rate & Pricing
        </h2>
        <a href="{{ route('propertyrateandpricing.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    {{-- Errors --}}
    @if ($errors->any())
        <div class="error-summary">
            <h6>Upload Errors</h6>
            @foreach ($errors->all() as $error)
                <div class="error-item">{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Info Boxes --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="upload-info-box">
                <h5><i class="fas fa-list"></i> Required Columns</h5>
                <ul>
                    <li><strong>PropertyId</strong> e.g 1</li>
                    <li><strong>BlockId</strong> e.g 1</li>
                    <li><strong>FloorId</strong> e.g 2</li>
                    <li><strong>UnitId</strong> e.g 1</li>
                    <li><strong>Rent</strong> e.g 50000</li>
                    <li><strong>ParkingFee</strong> e.g 2000</li>
                    <li><strong>ServiceCharge</strong> e.g 1000</li>
                    <li><strong>OtherCharges</strong> e.g 0</li>
                    <li><strong>DepositAmount</strong> e.g 25000</li>
                    <li><strong>CurrencyId</strong> e.g 1</li>
                    <li><strong>TaxId</strong> e.g 1</li>
                </ul>
            </div>
        </div>

        <div class="col-md-6">
            <div class="upload-info-box">
                <h5><i class="fas fa-check-double"></i> Best Practices</h5>
                <ul>
                    <li>Use CSV or Excel files</li>
                    <li>Maximum size: 10MB</li>
                    <li>Headers must be on first row</li>
                    <li>Numeric IDs process faster</li>
                    <li>Ensure references exist in the system</li>
                    <li>Download the template for accuracy</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Upload Form --}}
    <div class="form-section">
        <form action="{{ route('propertyrateandpricing.bulk-store') }}" method="POST" enctype="multipart/form-data" id="bulkUploadForm">
            @csrf

            <div class="mb-4">
                <label class="form-label fw-bold">Upload File</label>
                <div class="file-upload-wrapper" id="fileUploadArea">
                    <div class="upload-icon">
                        <i class="fas fa-file-excel"></i>
                    </div>
                    <p class="mb-0">
                        <strong>Click to upload</strong> or drag and drop<br>
                        <small class="text-muted">CSV or Excel (max 10MB)</small>
                    </p>
                    <div class="file-name" id="fileName"></div>
                    <input type="file" id="fileInput" name="file" accept=".csv,.xlsx,.xls" required>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-cloud-upload-alt"></i> Upload & Process
                </button>

                <a href="{{ route('propertyrateandpricing.bulk-template') }}" class="btn btn-outline-info">
                    <i class="fas fa-download"></i> Download Template
                </a>

                <a href="{{ route('propertyrateandpricing.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>

        <div class="template-section">
            <strong><i class="fas fa-lightbulb"></i> Tip:</strong>
            Always use the template to avoid validation errors.
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('fileInput');
    const uploadArea = document.getElementById('fileUploadArea');
    const fileName = document.getElementById('fileName');

    uploadArea.addEventListener('click', () => fileInput.click());

    uploadArea.addEventListener('dragover', e => {
        e.preventDefault();
        uploadArea.style.borderColor = '#0b5ed7';
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.style.borderColor = '#0d6efd';
    });

    uploadArea.addEventListener('drop', e => {
        e.preventDefault();
        fileInput.files = e.dataTransfer.files;
        updateFileName();
    });

    fileInput.addEventListener('change', updateFileName);

    function updateFileName() {
        if (fileInput.files.length) {
            fileName.textContent = '✓ ' + fileInput.files[0].name;
            uploadArea.classList.add('has-file');
        } else {
            fileName.textContent = '';
            uploadArea.classList.remove('has-file');
        }
    }
});
</script>

@endsection
